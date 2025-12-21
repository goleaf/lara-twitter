<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class CompositeKeyListRecords extends ListRecords
{
    abstract protected function getCompositeKeyColumns(): array;

    protected function getCompositeKeyDelimiter(): string
    {
        return ':';
    }

    public function getTableRecordKey(Model | array $record): string
    {
        if (is_array($record)) {
            return (string) ($record['key'] ?? '');
        }

        $values = [];

        foreach ($this->getCompositeKeyColumns() as $column) {
            $values[] = (string) $record->getAttribute($column);
        }

        return implode($this->getCompositeKeyDelimiter(), $values);
    }

    public function getSelectedTableRecordsQuery(bool $shouldFetchSelectedRecords = true, ?int $chunkSize = null): Builder
    {
        $table = $this->getTable();
        $maxSelectableRecords = $table->getMaxSelectableRecords();
        $query = $table->getQuery();
        $columns = $this->getCompositeKeyColumns();

        if ($this->isTrackingDeselectedTableRecords) {
            foreach ($this->deselectedTableRecords as $key) {
                $parts = $this->parseCompositeKey($key);

                if (! $parts) {
                    continue;
                }

                $query->whereNot(function (Builder $query) use ($columns, $parts): void {
                    foreach ($columns as $index => $column) {
                        $query->where($column, $parts[$index]);
                    }
                });
            }
        } else {
            if (empty($this->selectedTableRecords)) {
                return $query->whereRaw('0 = 1');
            }

            $query->where(function (Builder $query) use ($columns): void {
                foreach ($this->selectedTableRecords as $key) {
                    $parts = $this->parseCompositeKey($key);

                    if (! $parts) {
                        continue;
                    }

                    $query->orWhere(function (Builder $query) use ($columns, $parts): void {
                        foreach ($columns as $index => $column) {
                            $query->where($column, $parts[$index]);
                        }
                    });
                }
            });
        }

        if ($maxSelectableRecords) {
            $query->limit($maxSelectableRecords);
        }

        if (! $chunkSize) {
            $this->applySortingToTableQuery($query);
        }

        if ($shouldFetchSelectedRecords) {
            foreach ($table->getColumns() as $column) {
                $column->applyEagerLoading($query);
                $column->applyRelationshipAggregates($query);
            }
        }

        if ($table->shouldDeselectAllRecordsWhenFiltered()) {
            $this->filterTableQuery($query);
        }

        return $query;
    }

    public function getAllSelectableTableRecordKeys(): array
    {
        $table = $this->getTable();

        $records = $table->selectsCurrentPageOnly()
            ? $this->getTableRecords()
            : $this->getFilteredTableQuery()->get();

        return $records->reduce(
            function (array $carry, Model | array $record, string $key) use ($table): array {
                if ($table->checksIfRecordIsSelectable() && ! $table->isRecordSelectable($record)) {
                    return $carry;
                }

                $carry[] = $this->getTableRecordKey($record);

                return $carry;
            },
            initial: [],
        );
    }

    public function getGroupedSelectableTableRecordKeys(?string $group): array
    {
        $table = $this->getTable();
        $tableGrouping = $this->getTableGrouping();

        $records = $table->selectsCurrentPageOnly()
            ? $this->getTableRecords()
                ->filter(fn (Model | array $record) => $tableGrouping->getStringKey($record) === $group)
            : $table->getQuery()
                ->tap(fn (Builder $query) => $tableGrouping->scopeQueryByKey($query, $group))
                ->get();

        return $records->reduce(
            function (array $carry, Model | array $record, string $key) use ($table): array {
                if ($table->checksIfRecordIsSelectable() && ! $table->isRecordSelectable($record)) {
                    return $carry;
                }

                $carry[] = $this->getTableRecordKey($record);

                return $carry;
            },
            initial: [],
        );
    }

    protected function resolveTableRecord(?string $key): Model | array | null
    {
        if ($key === null) {
            return null;
        }

        $columns = $this->getCompositeKeyColumns();
        $parts = $this->parseCompositeKey($key);

        if (! $parts) {
            return null;
        }

        $query = $this->getFilteredTableQuery();

        foreach ($columns as $index => $column) {
            $query->where($column, $parts[$index]);
        }

        return $query->first();
    }

    /**
     * @return array<int, string>|null
     */
    protected function parseCompositeKey(string $key): ?array
    {
        $columns = $this->getCompositeKeyColumns();
        $parts = explode($this->getCompositeKeyDelimiter(), $key);

        if (count($columns) !== count($parts)) {
            return null;
        }

        return $parts;
    }
}

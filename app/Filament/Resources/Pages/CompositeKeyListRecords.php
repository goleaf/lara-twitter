<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
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

    protected function resolveTableRecord(?string $key): Model | array | null
    {
        if ($key === null) {
            return null;
        }

        $columns = $this->getCompositeKeyColumns();
        $parts = explode($this->getCompositeKeyDelimiter(), $key);

        if (count($columns) !== count($parts)) {
            return null;
        }

        $query = $this->getFilteredTableQuery();

        foreach ($columns as $index => $column) {
            $query->where($column, $parts[$index]);
        }

        return $query->first();
    }
}

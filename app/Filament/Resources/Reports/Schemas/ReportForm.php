<?php

namespace App\Filament\Resources\Reports\Schemas;

use App\Models\Report;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(array_combine(Report::statuses(), Report::statuses()))
                    ->required(),
                TextInput::make('reason')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? Report::reasonLabel($state) : null)
                    ->disabled(),
                Textarea::make('details')
                    ->disabled()
                    ->rows(4),
                Textarea::make('admin_notes')
                    ->rows(4),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Reports\Schemas;

use App\Models\Report;
use Filament\Schemas\Components\Section;
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
                Section::make('Case details')
                    ->schema([
                        TextInput::make('case_number')
                            ->label('Case')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('reporter_id')
                            ->label('Reporter')
                            ->relationship('reporter', 'username')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('reportable_type')
                            ->label('Target type')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?string $state): ?string => $state ? class_basename($state) : null),
                        TextInput::make('reportable_id')
                            ->label('Target ID')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('reason')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? Report::reasonLabel($state) : null)
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('details')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(4),
                    ])
                    ->columns(2),
                Section::make('Moderation')
                    ->schema([
                        Select::make('status')
                            ->options(array_combine(Report::statuses(), Report::statuses()))
                            ->required(),
                        Textarea::make('admin_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

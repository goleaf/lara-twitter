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
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation !== 'create'),
                        Select::make('reporter_id')
                            ->label('Reporter')
                            ->relationship('reporter', 'username')
                            ->searchable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation !== 'create')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                        Select::make('reportable_type')
                            ->label('Target type')
                            ->options([
                                'App\\Models\\Post' => 'Post',
                                'App\\Models\\Hashtag' => 'Hashtag',
                                'App\\Models\\Space' => 'Space',
                                'App\\Models\\Message' => 'Message',
                                'App\\Models\\User' => 'User',
                                'App\\Models\\UserList' => 'List',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation !== 'create')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                        TextInput::make('reportable_id')
                            ->label('Target ID')
                            ->numeric()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation !== 'create')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                        Select::make('reason')
                            ->options(Report::reasonOptions())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation !== 'create')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                        Textarea::make('details')
                            ->disabled(fn (string $operation): bool => $operation !== 'create')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create')
                            ->rows(4),
                    ])
                    ->columns(2),
                Section::make('Moderation')
                    ->schema([
                        Select::make('status')
                            ->options(array_combine(Report::statuses(), Report::statuses()))
                            ->default(Report::STATUS_OPEN)
                            ->required(),
                        Textarea::make('admin_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

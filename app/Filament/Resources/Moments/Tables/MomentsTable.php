<?php

namespace App\Filament\Resources\Moments\Tables;

use App\Models\Moment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MomentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image_path')
                    ->label('Cover')
                    ->disk('public')
                    ->toggleable(),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('owner.username')
                    ->label('Owner')
                    ->searchable(),
                IconColumn::make('is_public')
                    ->boolean()
                    ->label('Public'),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Posts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_public')
                    ->label('Public'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Moment $record): string => route('moments.show', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

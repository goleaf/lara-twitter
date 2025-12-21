<?php

namespace App\Filament\Resources\MessageAttachments;

use App\Filament\Resources\MessageAttachments\Pages\CreateMessageAttachment;
use App\Filament\Resources\MessageAttachments\Pages\EditMessageAttachment;
use App\Filament\Resources\MessageAttachments\Pages\ListMessageAttachments;
use App\Filament\Resources\MessageAttachments\Schemas\MessageAttachmentForm;
use App\Filament\Resources\MessageAttachments\Tables\MessageAttachmentsTable;
use App\Models\MessageAttachment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MessageAttachmentResource extends Resource
{
    protected static ?string $model = MessageAttachment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperClip;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 40;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with('message');
    }

    public static function form(Schema $schema): Schema
    {
        return MessageAttachmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessageAttachmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageAttachments::route('/'),
            'create' => CreateMessageAttachment::route('/create'),
            'edit' => EditMessageAttachment::route('/{record}/edit'),
        ];
    }
}

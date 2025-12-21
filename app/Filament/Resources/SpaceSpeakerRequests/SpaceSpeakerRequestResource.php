<?php

namespace App\Filament\Resources\SpaceSpeakerRequests;

use App\Filament\Resources\SpaceSpeakerRequests\Pages\CreateSpaceSpeakerRequest;
use App\Filament\Resources\SpaceSpeakerRequests\Pages\EditSpaceSpeakerRequest;
use App\Filament\Resources\SpaceSpeakerRequests\Pages\ListSpaceSpeakerRequests;
use App\Filament\Resources\SpaceSpeakerRequests\Schemas\SpaceSpeakerRequestForm;
use App\Filament\Resources\SpaceSpeakerRequests\Tables\SpaceSpeakerRequestsTable;
use App\Models\SpaceSpeakerRequest;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class SpaceSpeakerRequestResource extends Resource
{
    protected static ?string $model = SpaceSpeakerRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string|UnitEnum|null $navigationGroup = 'Spaces';

    protected static ?int $navigationSort = 30;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['space', 'user', 'decidedBy']);
    }

    public static function form(Schema $schema): Schema
    {
        return SpaceSpeakerRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpaceSpeakerRequestsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = self::pendingRequestsCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return self::pendingRequestsCount() > 0 ? 'warning' : 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpaceSpeakerRequests::route('/'),
            'create' => CreateSpaceSpeakerRequest::route('/create'),
            'edit' => EditSpaceSpeakerRequest::route('/{record}/edit'),
        ];
    }

    private static function pendingRequestsCount(): int
    {
        return Cache::remember('admin:nav:speaker-requests-pending', now()->addSeconds(90), function (): int {
            return SpaceSpeakerRequest::query()
                ->where('status', SpaceSpeakerRequest::STATUS_PENDING)
                ->count();
        });
    }
}

<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('username')
                    ->required()
                    ->alphaDash()
                    ->lowercase()
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(static fn (?string $state): bool => filled($state))
                    ->required(static fn (string $operation): bool => $operation === 'create'),
                FileUpload::make('avatar_path')
                    ->disk('public')
                    ->directory('avatars')
                    ->image(),
                Textarea::make('bio')
                    ->rows(3)
                    ->maxLength(160)
                    ->columnSpanFull(),
                Toggle::make('is_admin'),
                Toggle::make('is_premium')
                    ->label('Premium (long posts)'),
            ]);
    }
}

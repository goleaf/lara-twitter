<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('username')
                            ->required()
                            ->alphaDash()
                            ->formatStateUsing(static fn (?string $state): ?string => filled($state) ? Str::lower($state) : null)
                            ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? Str::lower($state) : null)
                            ->unique(ignoreRecord: true),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email verified'),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(static fn (?string $state): bool => filled($state))
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Profile')
                    ->schema([
                        FileUpload::make('avatar_path')
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->label('Avatar'),
                        FileUpload::make('header_path')
                            ->disk('public')
                            ->directory('headers')
                            ->image()
                            ->label('Header image'),
                        TextInput::make('location')
                            ->maxLength(120),
                        TextInput::make('website')
                            ->url()
                            ->maxLength(160),
                        DatePicker::make('birth_date')
                            ->label('Birth date'),
                        Select::make('birth_date_visibility')
                            ->label('Birth date visibility')
                            ->options(array_combine(User::birthDateVisibilities(), User::birthDateVisibilities()))
                            ->default(User::BIRTH_DATE_PUBLIC)
                            ->required(),
                        Textarea::make('bio')
                            ->rows(3)
                            ->maxLength(160)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Access & Flags')
                    ->schema([
                        Toggle::make('is_admin')
                            ->label('Admin access'),
                        Toggle::make('is_premium')
                            ->label('Premium (long posts)'),
                        Toggle::make('is_verified')
                            ->label('Verified'),
                        Toggle::make('analytics_enabled')
                            ->label('Analytics enabled'),
                    ])
                    ->columns(2),
                Section::make('Messaging')
                    ->schema([
                        Select::make('dm_policy')
                            ->label('DM policy')
                            ->options(array_combine(User::dmPolicies(), User::dmPolicies()))
                            ->default(User::DM_EVERYONE)
                            ->required(),
                        Toggle::make('dm_allow_requests')
                            ->label('Allow DM requests'),
                        Toggle::make('dm_read_receipts')
                            ->label('Read receipts'),
                    ])
                    ->columns(2),
            ]);
    }
}

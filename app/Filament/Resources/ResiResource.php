<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResiResource\Pages;
use App\Filament\Resources\ResiResource\RelationManagers;
use App\Models\Resi;
use Fadlee\FilamentQrCodeField\Forms\Components\QrCodeInput;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResiResource extends Resource
{
    protected static ?string $model = Resi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                QrCodeInput::make('qrcode')
                ->label('QR Code')
                ->placeholder('Click to scan QR code...'),

                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResis::route('/'),
            'create' => Pages\CreateResi::route('/create'),
            'edit' => Pages\EditResi::route('/{record}/edit'),
        ];
    }

    public static function getActions(): array
{
    return [
        Action::make('scanQR')
            ->form([
                QrCodeInput::make('qrcode')
                    ->label('Scan QR Code')
                    ->required(),
            ])
            ->action(function (array $data) {
                // Process the scanned QR code
                // $data['qrcode'] contains the scanned value
            }),
    ];
}
}

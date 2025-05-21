<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockPurchaseResource\Pages;
use App\Filament\Resources\StockPurchaseResource\RelationManagers;
use App\Models\StockPurchase;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockPurchaseResource extends Resource
{
    protected static ?string $model = StockPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->label('Supplier')
                    ->nullable(),

                DatePicker::make('purchase_date')
                    ->required()
                    ->default(now()),

                TextInput::make('total_price')
                    ->disabled()   // otomatis di-hitung dari items

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
            'index' => Pages\ListStockPurchases::route('/'),
            'create' => Pages\CreateStockPurchase::route('/create'),
            'edit' => Pages\EditStockPurchase::route('/{record}/edit'),
        ];
    }
}

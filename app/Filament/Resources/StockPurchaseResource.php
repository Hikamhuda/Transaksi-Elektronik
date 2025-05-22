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
use Filament\Forms\Get;
use Filament\Forms\Set;

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
                    ->preload()
                    ->label('Supplier')
                    ->nullable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\Textarea::make('address'),
                    ]),

                DatePicker::make('purchase_date')
                    ->required()
                    ->default(now()),

                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotalPrice($get, $set);
                            }),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotalPrice($get, $set);
                            }),
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotalPrice($get, $set);
                            }),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::updateTotalPrice($get, $set);
                    }),

                TextInput::make('total_price')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->default(0),
            ]);
    }

    protected static function updateTotalPrice(Get $get, Set $set): void
    {
        $items = collect($get('items'))->filter(fn ($item) => 
            !empty($item['product_id']) && 
            !empty($item['quantity']) && 
            !empty($item['price'])
        );

        $total = $items->sum(fn ($item) => (float) $item['quantity'] * (float) $item['price']);
        
        $set('total_price', number_format($total, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name'),
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    }),
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
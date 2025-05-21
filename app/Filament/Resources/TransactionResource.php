<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

                TextInput::make('total_price')->numeric()->required(),
                TextInput::make('paid_amount')->numeric()->required(),
                TextInput::make('change')->numeric()->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('user.name')->label('Kasir'),
                TextColumn::make('total_price')->label('Total')->money('IDR'),
                TextColumn::make('paid_amount')->label('Bayar')->money('IDR'),
                TextColumn::make('change')->label('Kembali')->money('IDR'),
                TextColumn::make('created_at')->label('Waktu')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name')
                    ->searchable(),

                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),

                Tables\Filters\Filter::make('min_total')
                    ->label('Total Minimal')
                    ->form([
                        Forms\Components\TextInput::make('amount')->numeric()->label('Minimal Total'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['amount'], fn($q) => $q->where('total_price', '>=', $data['amount']));
                    }),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                // ✅ Detail Button with Modal
                Action::make('Detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Transaksi')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Transaction $record) {
                        return view('filament.modals.transaction-detail', [
                            'transaction' => $record,
                        ]);
                    }),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}

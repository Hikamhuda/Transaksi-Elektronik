<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class PointOfSale extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public ?float $change = null;
    public ?int $lastTransactionId = null;

    public $payment_method = 'cash';

    protected static string $view = 'filament.pages.point-of-sale';


    public array $cart = [];
    public $product_id = null;
    public $quantity = 1;
    public $paid_amount = 0;


    public function mount()
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('product_id')
                ->label('Pilih Produk')
                ->options(Product::all()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            TextInput::make('quantity')
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->required(),

        ];
    }

    public function addToCart()
    {
        $product = Product::find($this->product_id);
        if (!$product)
            return;

        $this->cart[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $this->quantity,
            'subtotal' => $product->price * $this->quantity,
        ];

        $this->reset('product_id', 'quantity');
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function getTotal(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function processTransaction()
    {
        $total = $this->getTotal();

        if ($this->payment_method === 'cash' && $this->paid_amount < $total) {
            $this->addError('paid_amount', 'Uang bayar kurang dari total.');
            return;
        }

        $transaction = Transaction::create([
            'user_id' => optional(\Illuminate\Support\Facades\Auth::user())->id,
            'total_price' => $total,
            'paid_amount' => $this->paid_amount,
            'change' => $this->paid_amount - $total,
        ]);

        foreach ($this->cart as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
            ]);

            // Kurangi stok
            Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
        }

        $this->change = $transaction->change;
        $this->lastTransactionId = $transaction->id;

        $this->reset(['cart', 'paid_amount']);

    }
}

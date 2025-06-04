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

    public $payment_method = 'cash'; // default string

    protected static string $view = 'filament.pages.point-of-sale';


    public array $cart = [];
    public $product_id = null;
    public $quantity = 1;
    public $paid_amount = 0;
    public $total = 0;
    public $pendingTransaction = null;

    public $cash_image;
    public $cash_detection_result = null;

    public function mount()
    {
        $this->form->fill();
        $this->total = $this->getTotal();
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
        $this->total = $this->getTotal();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->total = $this->getTotal();
    }

    public function getTotal(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function processTransaction()
    {
        $total = $this->getTotal();
        $this->total = $total;

        if ($this->payment_method === 'cash' && $this->paid_amount < $total) {
            $this->addError('paid_amount', 'Uang bayar kurang dari total.');
            return;
        }
        if ($this->payment_method === 'cash' && !$this->paid_amount) {
            $this->addError('paid_amount', 'Masukkan uang dibayar.');
            return;
        }

        // Simpan data transaksi ke variabel sementara
        $this->pendingTransaction = [
            'user_id' => optional(\Illuminate\Support\Facades\Auth::user())->id,
            'total_price' => $total,
            'paid_amount' => $this->paid_amount,
            'change' => $this->paid_amount - $total,
            'payment_method' => $this->payment_method,
            'cart' => $this->cart,
        ];
        $this->dispatch('show-transaction-modal');
    }

    public function confirmTransaction()
    {
        if (!$this->pendingTransaction) return;
        $data = $this->pendingTransaction;
        $transaction = Transaction::create([
            'user_id' => $data['user_id'],
            'total_price' => $data['total_price'],
            'paid_amount' => $data['paid_amount'],
            'change' => $data['change'],
            'payment_method' => $data['payment_method'],
        ]);
        foreach ($data['cart'] as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
            ]);
            Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
        }
        $this->change = $transaction->change;
        $this->lastTransactionId = $transaction->id;
        $this->reset(['cart', 'paid_amount', 'pendingTransaction']);
        $this->dispatch('show-transaction-modal', id: $transaction->id);
    }

    public function cancelTransaction()
    {
        $this->pendingTransaction = null;
    }

    public function updatedCashImage($value)
    {
        $this->cash_detection_result = null;
    }

    public function detectCashAuthenticity()
    {
        if (!$this->cash_image) {
            $this->cash_detection_result = [
                'is_real' => false,
                'message' => 'Silakan upload gambar uang terlebih dahulu.'
            ];
            return;
        }
        // Validasi file gambar
        $allowedMime = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($this->cash_image->getMimeType(), $allowedMime)) {
            $this->cash_detection_result = [
                'is_real' => false,
                'message' => 'File harus berupa gambar JPG atau PNG.'
            ];
            return;
        }
        try {
            $imagePath = $this->cash_image->getRealPath();
            $imageData = base64_encode(file_get_contents($imagePath));
            if (strlen($imageData) > 5 * 1024 * 1024) { // 5MB
                $this->cash_detection_result = [
                    'is_real' => false,
                    'message' => 'File gambar terlalu besar.'
                ];
                return;
            }
            $apiUrl = 'https://serverless.roboflow.com/deteksi-uang-palsu-skqya/1?api_key=PY2aPJuUrLJY9fwKF6e3';
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $imageData,
                'http_errors' => false,
                'timeout' => 30,
                'verify' => false,
            ]);
        } catch (\Exception $e) {
            $this->cash_detection_result = [
                'is_real' => false,
                'message' => 'Gagal menghubungi API deteksi: ' . $e->getMessage()
            ];
            return;
        }
        if ($response && $response->getStatusCode() === 200) {
            $result = json_decode($response->getBody(), true);
            $classRaw = $result['predictions'][0]['class'] ?? '';
            $class = strtoupper($classRaw);
            // Ambil status dan nominal
            $isReal = strpos($class, 'ASLI') !== false;
            $isFake = strpos($class, 'PALSU') !== false;
            // Nominal: ambil angka dan satuan jika ada
            if (preg_match('/(\d+\s*RIBU|UANG|TIDAK DIKETAHUI)/i', $classRaw, $matches)) {
                $nominal = trim($matches[1]);
            } else {
                $nominal = 'Tidak Diketahui';
            }
            // Status
            if ($isReal) {
                $status = 'ASLI';
            } elseif ($isFake) {
                $status = 'PALSU';
            } else {
                $status = 'TIDAK DIKETAHUI';
            }
            $message = "Nominal: $nominal, Status: $status";
            $this->cash_detection_result = [
                'is_real' => $isReal,
                'message' => $message
            ];
        } else {
            $errorBody = $response ? $response->getBody()->getContents() : '';
            $this->cash_detection_result = [
                'is_real' => false,
                'message' => 'Gagal mendeteksi keaslian uang. Status: ' . ($response ? $response->getStatusCode() : 'No response') . ' | ' . $errorBody
            ];
        }
    }
}

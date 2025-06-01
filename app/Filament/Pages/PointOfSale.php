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
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Log;

class PointOfSale extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public ?float $change = null;
    public ?int $lastTransactionId = null;
    public $payment_method = 'cash';
    public $cash_image = null;

    protected static string $view = 'filament.pages.point-of-sale';

    public array $cart = [];
    public $product_id = null;
    public $quantity = 1;
    public $paid_amount = 0;
    public $total = 0;
    public $pendingTransaction = null;
    public $isProcessing = false;

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

            Select::make('payment_method')
                ->label('Metode Pembayaran')
                ->options([
                    'cash' => 'Tunai',
                    'qris' => 'QRIS'
                ])
                ->default('cash')
                ->reactive(),

            FileUpload::make('cash_image')
                ->label('Upload Foto Uang')
                ->image()
                ->visible(fn () => $this->payment_method === 'cash')
                ->required(fn () => $this->payment_method === 'cash')
                ->maxSize(2048) // Reduced from 5120 to 2048 KB
                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                ->directory('cash-validations')
                ->disk('public'),  // Explicitly set the disk

            TextInput::make('paid_amount')
                ->label('Jumlah Bayar')
                ->numeric()
                ->required(fn () => $this->payment_method === 'cash')
                ->visible(fn () => $this->payment_method === 'cash'),
        ];
    }

    private function detectCashImage($imagePath)
    {
        try {
            // Check if file exists before processing
            $fullPath = storage_path('app/public/' . $imagePath);
            if (!file_exists($fullPath)) {
                throw new \Exception("File not found at path: {$fullPath}");
            }
            
            // Get file content with error handling
            $fileContent = @file_get_contents($fullPath);
            if ($fileContent === false) {
                throw new \Exception("Failed to read file content");
            }
            
            $imageData = base64_encode($fileContent);
            
            // Log API request for debugging
            Log::info('Sending image to cash detection API', ['path' => $imagePath]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://serverless.roboflow.com/deteksi-uang-palsu-skqya/1?api_key=PY2aPJuUrLJY9fwKF6e3");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set timeout to 30 seconds
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new \Exception("cURL Error: " . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                throw new \Exception("API returned HTTP code {$httpCode}");
            }
            
            curl_close($ch);
            
            // Validate JSON response
            $jsonResponse = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response: " . json_last_error_msg());
            }
            
            return $jsonResponse;
        } catch (\Exception $e) {
            // Log detailed error
            Log::error('Cash image detection failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'path' => $imagePath ?? 'not-set'
            ]);
            
            $this->addError('cash_image', 'Gagal memproses gambar: ' . $e->getMessage());
            return null;
        }
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
        if (empty($this->cart)) {
            $this->addError('cart', 'Keranjang belanja kosong');
            return;
        }
    
        $this->isProcessing = true;
        $total = $this->getTotal();
        $this->total = $total;

        try {
            if ($this->payment_method === 'cash') {
                if (!$this->cash_image) {
                    $this->addError('cash_image', 'Foto uang harus diunggah untuk pembayaran tunai.');
                    $this->isProcessing = false;
                    return;
                }

                if ($this->paid_amount < $total) {
                    $this->addError('paid_amount', 'Uang bayar kurang dari total.');
                    $this->isProcessing = false;
                    return;
                }

                // Detect cash image
                $result = $this->detectCashImage($this->cash_image);
                
                if (!$result) {
                    // Error already set in detectCashImage method
                    $this->isProcessing = false;
                    return;
                }
                
                if (empty($result['predictions'])) {
                    $this->addError('cash_image', 'Tidak dapat mendeteksi uang dalam gambar.');
                    $this->isProcessing = false;
                    return;
                }

                foreach ($result['predictions'] as $prediction) {
                    if ($prediction['class'] === 'TIDAK DIKETAHUI') {
                        $this->addError('cash_image', 'Uang tidak dapat diidentifikasi atau mencurigakan.');
                        $this->isProcessing = false;
                        return;
                    }
                }
            }

            $this->pendingTransaction = [
                'user_id' => optional(\Illuminate\Support\Facades\Auth::user())->id,
                'total_price' => $total,
                'paid_amount' => $this->paid_amount,
                'change' => $this->payment_method === 'cash' ? ($this->paid_amount - $total) : 0,
                'payment_method' => $this->payment_method,
                'cart' => $this->cart,
            ];

            $this->isProcessing = false;
            $this->dispatch('show-transaction-modal');
        } catch (\Exception $e) {
            Log::error('Transaction processing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $this->addError('process', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->isProcessing = false;
        }
    }

    public function confirmTransaction()
    {
        if (!$this->pendingTransaction) return;
        
        try {
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
            $this->reset(['cart', 'paid_amount', 'pendingTransaction', 'cash_image', 'total']);
            $this->total = 0;
            $this->dispatch('show-transaction-modal', id: $transaction->id);
        } catch (\Exception $e) {
            Log::error('Transaction confirmation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $this->addError('confirm', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function cancelTransaction()
    {
        $this->pendingTransaction = null;
        $this->reset(['cash_image']);
    }
}
<x-filament::page>
    <div class="flex flex-col md:flex-row gap-6">

        {{-- KIRI: Form Tambah ke Keranjang --}}
        <div class="w-full md:w-1/2 space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-4">
                    Pilih Produk
                </h2>
                <form wire:submit.prevent="addToCart" class="space-y-4">
                    {{ $this->form }}

                    <x-filament::button type="submit" icon="heroicon-m-shopping-cart" class="mt-4">
                        Tambah ke Keranjang
                    </x-filament::button>
                </form>
            </div>
        </div>

        {{-- KANAN: Keranjang & Pembayaran --}}
        <div class="w-full md:w-1/2">
            {{-- Menggunakan flex-col dan h-full agar footer bisa didorong ke bawah --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-6 flex flex-col h-full">
                <div class="flex-grow">
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                        Keranjang
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-700 dark:text-gray-300">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left">Produk</th>
                                    <th scope="col" class="px-3 py-3">Qty</th>
                                    <th scope="col" class="px-3 py-3 text-right">Subtotal</th>
                                    <th scope="col" class="px-3 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cart as $index => $item)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="px-3 py-4 font-medium">{{ $item['name'] }}</td>
                                        <td class="px-3 py-4 text-center">{{ $item['quantity'] }}</td>
                                        <td class="px-3 py-4 text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-4 text-center">
                                            <button wire:click="removeFromCart({{ $index }})" class="text-gray-400 hover:text-red-500 p-1">
                                                <span class="sr-only">Hapus item</span>
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l.383-1.437M7.5 14.25 5.106 5.165A2.25 2.25 0 0 0 2.856 3H2.25" />
                                            </svg>
                                            <p class="text-lg">Keranjang Anda kosong</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Bagian Footer (Total & Pembayaran) --}}
                <div class="mt-auto pt-6 border-t border-gray-200 dark:border-gray-700 space-y-4">
                    {{-- Total --}}
                    <div class="flex justify-between items-center text-lg font-medium text-gray-900 dark:text-white">
                        <span>Total</span>
                        <span class="text-2xl font-bold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Metode Pembayaran</label>
                        <select wire:model.live="payment_method" id="payment_method"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    {{-- Aksi Pembayaran --}}
                    @if($payment_method === 'cash')
                        <div>
                            <label for="paid_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Uang Dibayar</label>
                            <input type="number" wire:model="paid_amount" id="paid_amount"
                                   class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500"
                                   placeholder="Masukkan jumlah uang">
                             @error('paid_amount')<p class="text-red-500 text-sm mt-2">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-filament::button wire:click="processTransaction" icon="heroicon-m-check-circle" size="lg">
                                Proses Transaksi
                            </x-filament::button>
                            <x-filament::button tag="a" href="{{ route('cash-detection-webcam') }}" target="_blank" color="info" icon="heroicon-m-camera" size="lg">
                                Deteksi Webcam
                            </x-filament::button>
                        </div>
                        <x-filament::button color="gray" size="lg" class="w-full" x-on:click="$dispatch('open-qr-modal')" icon="heroicon-m-qr-code">
                            Tampilkan QR Deteksi Uang
                        </x-filament::button>

                    @else {{-- Opsi untuk QRIS atau metode lain --}}
                        <x-filament::button wire:click="processTransaction" size="lg" class="w-full" icon="heroicon-m-check-circle">
                            Proses Transaksi
                        </x-filament::button>
                    @endif
                    
                    @if (session()->has('success'))
                        <p class="text-green-600 text-center font-medium mt-4">{{ session('success') }}</p>
                    @endif
                </div>

                <div x-data="{ open: false }" x-on:open-qr-modal.window="open = true" x-show="open" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
                    <div @click.outside="open = false" class="bg-white rounded-lg shadow-xl p-4 max-w-xs w-full flex flex-col items-center">
                        <h3 class="text-lg font-bold mb-4 text-gray-800">Pindai untuk Deteksi Uang</h3>
                        <img src="{{ asset('storage/image/qr-deteksi-uang.jpg') }}" alt="QR Deteksi Uang" class="max-w-xs rounded-lg shadow-md border mb-4">
                        <x-filament::button color="gray" class="w-full" x-on:click="open = false">
                            Tutup
                        </x-filament::button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SEMUA MODAL TETAP SAMA --}}
    @if($pendingTransaction)
        {{-- Modal Deteksi Uang (z-60, di atas modal konfirmasi) --}}
        <div x-data="{ open: false }" x-on:open-cash-detection-modal.window="open = true" x-show="open" x-cloak
            class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-50" style="z-index: 60;">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
                <h3 class="text-lg font-bold mb-4">Deteksi Keaslian Uang</h3>
                <input type="file" wire:model="cash_image" accept="image/*" class="border p-2 rounded w-full mb-2"
                    id="cash_image_modal_upload">
                @if($cash_image)
                    <x-filament::button wire:click="detectCashAuthenticity"
                        class="mt-2 w-full flex justify-center items-center bg-green-600">
                        <span class="whitespace-nowrap">Deteksi Sekarang</span>
                    </x-filament::button>
                @endif
                @if($cash_detection_result)
                    <div class="mt-2 p-2 rounded text-white"
                        style="background-color: {{ $cash_detection_result['is_real'] ? '#16a34a' : '#dc2626' }};">
                        <strong>Hasil Deteksi:</strong> {{ $cash_detection_result['message'] }}
                    </div>
                @endif
                <button class="mt-4 px-4 py-2 bg-gray-400 text-white rounded w-full"
                    x-on:click="open = false">Tutup</button>
            </div>
        </div>
        {{-- Modal detail transaksi (z-50, di bawah modal deteksi uang) --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-data="{ show: true }"
            x-show="show && !$refs.cashDetectionModalOpen?.open" x-cloak>
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
                <div class="space-y-4 text-sm">
                    <div class="space-y-1">
                        <div><strong>ID Transaksi:</strong> (Belum Tersimpan)</div>
                        <div><strong>Kasir:</strong>
                            {{ \App\Helpers\EncryptHelper::decrypt(\App\Models\User::find($pendingTransaction['user_id'])->name ?? '-') }}
                        </div>
                        <div><strong>Total:</strong> Rp {{ number_format($pendingTransaction['total_price'], 0, ',', '.') }}
                        </div>
                        <div><strong>Bayar:</strong> Rp {{ number_format($pendingTransaction['paid_amount'], 0, ',', '.') }}
                        </div>
                        <div><strong>Kembali:</strong> Rp {{ number_format($pendingTransaction['change'], 0, ',', '.') }}
                        </div>
                        <div><strong>Metode Pembayaran:</strong> {{ ucfirst($pendingTransaction['payment_method'] ?? '-') }}</div>
                        <div><strong>Waktu:</strong> {{ now()->format('d M Y H:i') }}</div>
                    </div>
                    <div class="border-t pt-2">
                        <h3 class="font-semibold mb-2">Item yang Dibeli:</h3>
                        <table class="w-full text-left border text-sm mb-4">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-2 py-1 border">Produk</th>
                                    <th class="px-2 py-1 border">Jumlah</th>
                                    <th class="px-2 py-1 border">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingTransaction['cart'] as $item)
                                    <tr>
                                        <td class="px-2 py-1 border">{{ $item['name'] }}</td>
                                        <td class="px-2 py-1 border">{{ $item['quantity'] }}</td>
                                        <td class="px-2 py-1 border">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-4 text-right flex gap-2 justify-end">
                    <button class="px-4 py-2 bg-green-600 text-black rounded"
                        x-on:click="$dispatch('open-cash-detection-modal')">Cek Keaslian Uang</button>
                    <button class="px-4 py-2 bg-primary-600 text-white rounded"
                        wire:click="confirmTransaction">Konfirmasi</button>
                    <button class="px-4 py-2 bg-gray-400 text-white rounded" wire:click="cancelTransaction"
                        @click="show = false;">Batal</button>
                </div>
            </div>
        </div>
    @endif
    {{-- Modal detail transaksi setelah konfirmasi --}}
    @if($lastTransactionId && !$pendingTransaction)
        @php
            $transaction = \App\Models\Transaction::with('items.product', 'user')->find($lastTransactionId);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-data="{ show: true }"
            x-show="show" x-cloak>
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
                @include('filament.modals.transaction-detail', ['transaction' => $transaction])
                <div class="mt-4 text-right">
                    <button class="px-4 py-2 bg-primary-600 text-white rounded"
                        @click="show = false; window.location.reload();">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
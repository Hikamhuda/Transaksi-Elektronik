<x-filament::page>
    <div class="flex flex-col md:flex-row gap-6">

        {{-- KIRI: Form Tambah ke Keranjang --}}
        <div class="w-full md:w-1/2 space-y-4">
            <form wire:submit.prevent="addToCart" class="space-y-4">
                {{ $this->form }}

                <x-filament::button type="submit">
                    Tambah ke Keranjang
                </x-filament::button>
            </form>
        </div>

        {{-- KANAN: Keranjang & Pembayaran --}}
        <div class="w-full md:w-1/2">
            <div class="bg-white dark:bg-gray-900 rounded shadow p-6 text-gray-900 dark:text-gray-100">
                <h3 class="text-lg font-bold mb-4">Keranjang</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border mb-4 dark:border-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="text-left py-2 px-3">Produk</th>
                                <th class="py-2 px-3">Qty</th>
                                <th class="py-2 px-3 text-right">Harga</th>
                                <th class="py-2 px-3 text-right">Subtotal</th>
                                <th class="py-2 px-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cart as $index => $item)
                                <tr class="border-t dark:border-gray-700">
                                    <td class="py-2 px-3">{{ $item['name'] }}</td>
                                    <td class="py-2 px-3 text-center">{{ $item['quantity'] }}</td>
                                    <td class="py-2 px-3 text-right">{{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-right">{{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <button wire:click="removeFromCart({{ $index }})"
                                            class="text-red-500 hover:underline">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">Keranjang
                                        kosong</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <label for="payment_method" class="block text-md font-medium text-gray-700">Metode
                            Pembayaran</label>
                        <select wire:model="payment_method" id="payment_method"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-lg">Total: <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                        @if($payment_method === 'cash')
                            <input type="number" wire:model="paid_amount"
                                class="border p-2 rounded w-full md:w-40 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                placeholder="Uang Dibayar">
                        @endif
                        <x-filament::button wire:click="processTransaction"
                            class="mt-2 md:mt-0 w-full md:w-auto flex justify-center items-center">
                            <span class="whitespace-nowrap">Proses Transaksi</span>
                        </x-filament::button>
                    </div>
                </div>

                @error('paid_amount')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror

                @if (session()->has('success'))
                    <p class="text-green-600 mt-4">{{ session('success') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal detail transaksi --}}
    @if($pendingTransaction)
        {{-- Modal Deteksi Uang (z-60, di atas modal konfirmasi) --}}
        <div
            x-data="{ open: false }"
            x-on:open-cash-detection-modal.window="open = true"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-50"
            style="z-index: 60;"
        >
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
                <h3 class="text-lg font-bold mb-4">Deteksi Keaslian Uang</h3>
                <input type="file" wire:model="cash_image" accept="image/*"
                    class="border p-2 rounded w-full mb-2" id="cash_image_modal_upload">
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
                <button class="mt-4 px-4 py-2 bg-gray-400 text-white rounded w-full" x-on:click="open = false">Tutup</button>
            </div>
        </div>
        {{-- Modal detail transaksi (z-50, di bawah modal deteksi uang) --}}
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            x-data="{ show: true }"
            x-show="show && !$refs.cashDetectionModalOpen?.open"
            x-cloak
        >
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
                <div class="space-y-4 text-sm">
                    <div class="space-y-1">
                        <div><strong>ID Transaksi:</strong> (Belum Tersimpan)</div>
                        <div><strong>Kasir:</strong> {{ \App\Models\User::find($pendingTransaction['user_id'])->name ?? '-' }}</div>
                        <div><strong>Total:</strong> Rp {{ number_format($pendingTransaction['total_price'], 0, ',', '.') }}</div>
                        <div><strong>Bayar:</strong> Rp {{ number_format($pendingTransaction['paid_amount'], 0, ',', '.') }}</div>
                        <div><strong>Kembali:</strong> Rp {{ number_format($pendingTransaction['change'], 0, ',', '.') }}</div>
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
                    <button
                        class="px-4 py-2 bg-green-600 text-black rounded"
                        x-on:click="$dispatch('open-cash-detection-modal')"
                    >Cek Keaslian Uang</button>
                    <button
                        class="px-4 py-2 bg-primary-600 text-white rounded"
                        wire:click="confirmTransaction"
                    >Konfirmasi</button>
                    <button
                        class="px-4 py-2 bg-gray-400 text-white rounded"
                        wire:click="cancelTransaction"
                        @click="show = false;"
                    >Batal</button>
                </div>
            </div>
        </div>
    @endif
    {{-- Modal detail transaksi setelah konfirmasi --}}
    @if($lastTransactionId && !$pendingTransaction)
        @php
            $transaction = \App\Models\Transaction::with('items.product', 'user')->find($lastTransactionId);
        @endphp
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            x-data="{ show: true }"
            x-show="show"
            x-cloak
        >
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
                @include('filament.modals.transaction-detail', ['transaction' => $transaction])
                <div class="mt-4 text-right">
                    <button
                        class="px-4 py-2 bg-primary-600 text-white rounded"
                        @click="show = false; window.location.reload();"
                    >Tutup</button>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
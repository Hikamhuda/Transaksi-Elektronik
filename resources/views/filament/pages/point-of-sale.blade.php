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
                        <p class="text-lg">Total: <strong>{{ number_format($this->getTotal(), 0, ',', '.') }}</strong>
                        </p>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                        <input type="number" wire:model="paid_amount"
                            class="border p-2 rounded w-full md:w-40 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                            placeholder="Uang Dibayar">
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
</x-filament::page>
<div class="space-y-4 text-sm">
    <div class="space-y-1">
        <div><strong>ID Transaksi:</strong> {{ $transaction->id }}</div>
        <div><strong>Kasir:</strong> {{ $transaction->user->name ?? '-' }}</div>
        <div><strong>Total:</strong> Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</div>
        <div><strong>Bayar:</strong> Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</div>
        <div><strong>Kembali:</strong> Rp {{ number_format($transaction->change, 0, ',', '.') }}</div>
        <div><strong>Waktu:</strong> {{ $transaction->created_at->format('d M Y H:i') }}</div>
    </div>

    <div class="border-t pt-2">
        <h3 class="font-semibold mb-2">Item yang Dibeli:</h3>
        <table class="w-full text-left border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-1 border">Produk</th>
                    <th class="px-2 py-1 border">Jumlah</th>
                    <th class="px-2 py-1 border">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr>
                        <td class="px-2 py-1 border">{{ $item->product->name ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $item->quantity }}</td>
                        <td class="px-2 py-1 border">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

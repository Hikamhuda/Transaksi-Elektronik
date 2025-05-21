<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #000; padding: 5px; text-align: left; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Struk Transaksi #{{ $transaction->id }}</h2>
    <p>Tanggal: {{ $transaction->created_at->format('d-m-Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->product->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="text-right">Total: <strong>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</strong></p>
    <p class="text-right">Bayar: Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</p>
    <p class="text-right">Kembalian: Rp {{ number_format($transaction->change, 0, ',', '.') }}</p>

    <p style="text-align:center; margin-top:20px;">Terima kasih telah berbelanja!</p>
</body>
</html>

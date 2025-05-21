<?php

use Illuminate\Support\Facades\Route;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/receipt/{transaction}/pdf', function (Transaction $transaction) {
    $transaction->load('items.product');

    $pdf = Pdf::loadView('receipt', compact('transaction'));
        return $pdf->download('struk-transaksi-' . $transaction->id . '.pdf');
        })->name('receipt.pdf');

Route::get('/', function () {
    return redirect('/admin/login');
});

<?php

use App\Models\Product;
use App\Models\SaleTransaction;
use App\Services\ReceiptService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

it('generates receipt correctly', function () {
    // Prepare Data
    $product = new Product();
    $product->name = 'Barang Test';
    $product->price = 5000;

    $transaction = new SaleTransaction();
    $transaction->id = 'UUID-TEST';
    $transaction->quantity = 2;
    $transaction->created_at = Carbon::parse('2023-01-01 10:00:00');
    $transaction->setRelation('product', $product);

    // Clean up before test
    if (File::exists('receipts')) {
        File::deleteDirectory('receipts');
    }

    // Execute
    $payAmount = 20000;
    ReceiptService::generate($transaction, $payAmount);

    // Assert
    $files = File::files('receipts');
    expect(count($files))->toBeGreaterThan(0);

    $content = File::get($files[0]);
    expect($content)->toContain('STRUK BELANJA');
    expect($content)->toContain('MAJU MUNDUR MART');
    expect($content)->toContain('Jl. Yang di ridhoi No. 911');
    expect($content)->toContain('Kasir   : Naia');
    expect($content)->toContain('Barang  : Barang Test');
    expect($content)->toContain('Harga   : Rp 5.000');
    expect($content)->toContain('Jumlah  : 2');
    expect($content)->toContain('Total   : Rp 10.000');
    expect($content)->toContain('Tunai   : Rp 20.000');
    expect($content)->toContain('Kembali : Rp 10.000');
    expect($content)->toContain('Tanggal : 01-01-2023 10:00:00');

    // Cleanup
    File::deleteDirectory('receipts');
});

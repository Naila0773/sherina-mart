<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\SaleTransaction;
use Illuminate\Support\Facades\File;

class ReceiptService
{
    public static function generate(SaleTransaction $transaction, int $payAmount): string
    {
        $receiptContent = self::formatReceipt($transaction, $payAmount);
        self::saveReceipt($transaction, $receiptContent);

        return $receiptContent;
    }

    private static function formatReceipt(SaleTransaction $transaction, int $payAmount): string
    {
        $date = $transaction->created_at->format('d-m-Y H:i:s');
        $productName = $transaction->product->name;
        $price = number_format((float) $transaction->product->price, 0, ',', '.');
        $quantity = $transaction->quantity;

        $totalAmount = $transaction->product->price * $transaction->quantity;
        $change = $payAmount - $totalAmount;

        $total = number_format((float) $totalAmount, 0, ',', '.');
        $pay = number_format((float) $payAmount, 0, ',', '.');
        $changeFormatted = number_format((float) $change, 0, ',', '.');

        $separator = str_repeat('=', 30);

        return <<<RECEIPT
{$separator}
      STRUK BELANJA
      MAJU MUNDUR MART
    Jl. Yang di ridhoi No. 911
{$separator}
Tanggal : {$date}
Kasir   : Naia
Barang  : {$productName}
Harga   : Rp {$price}
Jumlah  : {$quantity}
Total   : Rp {$total}
Tunai   : Rp {$pay}
Kembali : Rp {$changeFormatted}
{$separator}
Terima Kasih Telah Berbelanja!
{$separator}
RECEIPT;
    }

    private static function saveReceipt(SaleTransaction $transaction, string $content): void
    {
        $directory = 'receipts';

        if (!File::exists($directory)) {
            File::makeDirectory($directory);
        }

        $filename = "struk-{$transaction->id}-" . time() . ".txt";
        File::put("{$directory}/{$filename}", $content);
    }
}

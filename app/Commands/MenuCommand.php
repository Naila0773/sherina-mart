<?php
declare(strict_types=1);
namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\select;
use App\Models\Category;
use App\Models\Variety;
use App\Models\Product;
use App\Models\SaleTransaction;

class MenuCommand extends Command
{
    public Category $category;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:menu-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'menampilkan Menu pada pengguna';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = "Selamat di Applikasi Kami\nSilahkan Pilih Menu Berikut :";
        $options = [
            "Pilihan 1: Transaksi Pembelian Barang",
            "Pilihan 2: Daftar Kategori Barang",
            "Pilihan 3: Tambah Kategori Barang",
            "Pilihan 4: Ubah Kategori Barang",
            "Pilihan 5: Hapus Kategori Barang",
            "Pilihan 6: Daftar Jenis Barang",
            "Pilihan 7: Tambah Jenis Barang",
            "Pilihan 8: Ubah Jenis Barang",
            "Pilihan 9: Hapus Jenis Barang",
            "Pilihan 10: Daftar Barang",
            "Pilihan 11: Tambah Barang",
            "Pilihan 12: Ubah Barang",
            "Pilihan 13 : Hapus Barang",
            "Pilihan 14 : Daftar Penjualan Barang",
        ];

        $option = $this->menu($title, $options)
            ->setForegroundColour("green")
            ->setBackgroundColour("black")
            ->setWidth(200)
            ->setPadding(10)
            ->setMargin(5)
            ->setExitButtonText("Abort")
            // remove exit button with
            // ->disableDefaultItems()
            ->setTitleSeparator("*-")
            // ->addLineBreak('<3', 2)
            // ->addStaticItem('AREA 2')
            ->open();

        // $this->info("Anda Memilih Pilihan : {$option}");
        if ($option == 0) {
            $sale = new SaleTransaction;
            // $this->info("Anda Memilih Pilihan : {$option} Transaksi Pembelian Barang");
            $products = Product::all()->pluck('name', 'id')->toArray();
            $sale->product_id = select(
                label: 'Pilih Barang:',
                options: $products,
            );
            $choosen_product = Product::find($sale->product_id);
            $sale->price = $choosen_product->price;
            $sale->quantity = (int) $this->ask("Masukkan Jumlah Barang : ");

            // [UPDATED] Added Stock Check Logic
            // Prevent transaction if requested quantity exceeds available stock
            if ($sale->quantity > $choosen_product->stock) {
                $this->error("Stok tidak cukup! Stok saat ini: {$choosen_product->stock}");
            } else {
                if ($sale->save()) {
                    // [UPDATED] Decrement Stock
                    // Automatically reduce stock after successful transaction
                    $choosen_product->decrement('stock', $sale->quantity);
                    $this->notify("Success", "data berhasil disimpan");
                    $payment = $sale->price * $sale->quantity;
                    $this->info("Total Bayar: {$payment}");
                } else {
                    $this->notify("Failed", "data gagal disimpan");
                }
            }
        } else if ($option == 1) {
            $headers = ['kode', 'nama', 'dibuat', 'diubah'];
            $data = Category::all()->map(function ($item) {
                return [
                    'kode' => $item->code,
                    'nama' => $item->name,
                    'dibuat' => $item->created_at,
                    'diubah' => $item->updated_at,
                ];
            })->toArray();
            $this->table($headers, $data);
        } else if ($option == 2) {
            $this->info("Anda Memilih Pilihan : {$option} Tambah Kategori Barang");
            $category = new Category();
            $category->code = (int) $this->ask("Masukkan Kode Kategori : ");
            $category->name = $this->ask("Masukkan Nama Kategori : ");
            if ($category->save()) {
                $this->notify("Success", "data berhasil disimpan");
            } else {
                $this->notify("Failed", "data gagal disimpan");
            }

        } else if ($option == 3) {
            $this->info("Anda Memilih Pilihan : {$option} Ubah Kategori Barang");
            $code = (int) $this->ask("Masukkan Kode Kategori yang akan diubah : ");

            // [UPDATED] Added Null Check for Category Edit
            // Prevents crash if category code does not exist
            $category = Category::where('code', $code)->first();

            if ($category) {
                $category->code = (int) $this->ask("Masukkan Kode Kategori : ", (string) $category->code);
                $category->name = $this->ask("Masukkan Nama Kategori : ", $category->name);
                if ($category->save()) {
                    $this->notify("Success", "data berhasil diubah");
                } else {
                    $this->notify("Failed", "data gagal diubah");
                }
            } else {
                $this->error("Kategori dengan kode {$code} tidak ditemukan!");
            }
        } else if ($option == 4) {
            $this->info("Anda Memilih Pilihan : {$option} Hapus Kategori Barang");
            $code = (int) $this->ask("Masukkan Kode Kategori yang akan dihapus : ");
            $category = Category::where('code', $code)->first();

            if ($category) {
                if ($category->delete()) {
                    $this->notify("Success", "data berhasil dihapus");
                } else {
                    $this->notify("Failed", "data gagal dihapus");
                }
            } else {
                $this->error("Kategori dengan kode {$code} tidak ditemukan!");
            }
        } else if ($option == 5) {
            $this->info("Anda Memilih Pilihan : {$option} Daftar Jenis Barang");
            $headers = ['kode', 'nama', 'dibuat', 'diubah'];
            $data = Variety::all()->map(function ($item) {
                return [
                    'kode' => $item->code,
                    'nama' => $item->name,
                    'dibuat' => $item->created_at,
                    'diubah' => $item->updated_at,
                ];
            })->toArray();
            $this->table($headers, $data);
        } else if ($option == 6) {
            $this->info("Anda Memilih Pilihan : {$option} Tambah Jenis Barang");
            $variety = new Variety();
            $variety->code = (int) $this->ask("Masukkan Kode Jenis : ");
            $variety->name = $this->ask("Masukkan Nama Jenis : ");
            if ($variety->save()) {
                $this->notify("Success", "data berhasil disimpan");
            } else {
                $this->notify("Failed", "data gagal disimpan");
            }
        } else if ($option == 7) {
            $this->info("Anda Memilih Pilihan : {$option} Ubah Jenis Barang");
            $code = (int) $this->ask("Masukkan Kode Jenis yang akan diubah : ");

            // [UPDATED] Added Null Check for Variety Edit
            // Prevents crash if variety code does not exist
            $variety = Variety::where('code', $code)->first();

            if ($variety) {
                $variety->code = (int) $this->ask("Masukkan Kode Jenis : ", (string) $variety->code);
                $variety->name = $this->ask("Masukkan Nama Jenis : ", $variety->name);
                if ($variety->save()) {
                    $this->notify("Success", "data berhasil diubah");
                } else {
                    $this->notify("Failed", "data gagal diubah");
                }
            } else {
                $this->error("Jenis dengan kode {$code} tidak ditemukan!");
            }
        } else if ($option == 8) {
            $this->info("Anda Memilih Pilihan : {$option} Hapus Jenis Barang");
            $code = (int) $this->ask("Masukkan Kode Jenis yang akan dihapus : ");
            $variety = Variety::where('code', $code)->first();

            if ($variety) {
                if ($variety->delete()) {
                    $this->notify("Success", "data berhasil dihapus");
                } else {
                    $this->notify("Failed", "data gagal dihapus");
                }
            } else {
                $this->error("Jenis dengan kode {$code} tidak ditemukan!");
            }

        } else if ($option == 9) {
            $this->info("Anda Memilih Pilihan : {$option} Daftar Barang");
            $this->info("Anda Memilih Pilihan : {$option} Daftar Barang");

            // [UPDATED] Added 'stok' column to header and data map
            $headers = ['kode', 'nama', 'harga', 'stok', 'kategori', 'jenis', 'dibuat', 'diubah'];
            $data = Product::all()->map(function ($item) {
                return [
                    'kode' => $item->code,
                    'nama' => $item->name,
                    'harga' => $item->price,
                    'stok' => $item->stock,
                    'kategori' => $item->category->name,
                    'jenis' => $item->variety->name,
                    'dibuat' => $item->created_at,
                    'diubah' => $item->updated_at,
                ];
            })->toArray();
            $this->table($headers, $data);
        } else if ($option == 10) {
            $product = new Product();
            $this->info("Anda Memilih Pilihan : {$option} Tambah Barang");
            $categories = Category::all()->pluck('name', 'id')->toArray();
            $product->category_id = select(
                label: 'Pilih Kategori Barang:',
                options: $categories,
            );

            $varieties = Variety::all()->pluck('name', 'id')->toArray();
            $product->variety_id = select(
                label: 'Pilih Kategori Barang:',
                options: $varieties,
            );

            $product->code = (int) $this->ask("Masukkan Kode Barang : ");
            $product->name = $this->ask("Masukkan Nama Barang : ");
            $product->price = (int) $this->ask("Masukkan Harga Barang : ");

            // [UPDATED] Added Stock Input for New Products
            $product->stock = (int) $this->ask("Masukkan Stok Awal Barang : ", "0");
            if ($product->save()) {
                $this->notify("Success", "data berhasil disimpan");
            } else {
                $this->notify("Failed", "data gagal disimpan");
            }

        } else if ($option == 11) {
            $this->info("Anda Memilih Pilihan : {$option} Ubah Barang");
            $code = (int) $this->ask("Masukkan Kode Barang yang akan diubah : ");
            $product = Product::where('code', $code)->first();

            // [UPDATED] Added Null Check and Stock Update for Product Edit
            if ($product) {
                $categories = Category::all()->pluck('name', 'id')->toArray();
                $product->category_id = select(
                    label: 'Pilih Kategori Barang Baru:',
                    options: $categories,
                    default: $product->category_id
                );

                $varieties = Variety::all()->pluck('name', 'id')->toArray();
                $product->variety_id = select(
                    label: 'Pilih Jenis Barang Baru:',
                    options: $varieties,
                    default: $product->variety_id
                );

                $product->code = (int) $this->ask("Masukkan Kode Barang Baru : ", (string) $product->code);
                $product->name = $this->ask("Masukkan Nama Barang Baru : ", $product->name);
                $product->price = (int) $this->ask("Masukkan Harga Barang Baru : ", (string) $product->price);
                $product->stock = (int) $this->ask("Masukkan Stok Baru : ", (string) $product->stock);

                if ($product->save()) {
                    $this->notify("Success", "data berhasil diubah");
                } else {
                    $this->notify("Failed", "data gagal diubah");
                }
            } else {
                $this->error("Barang dengan kode {$code} tidak ditemukan!");
            }

        } else if ($option == 12) {
            $this->info("Anda Memilih Pilihan : {$option} Hapus Barang");
            $code = (int) $this->ask("Masukkan Kode Barang yang akan dihapus : ");
            $product = Product::where('code', $code)->first();

            if ($product) {
                if ($product->delete()) {
                    $this->notify("Success", "data berhasil dihapus");
                } else {
                    $this->notify("Failed", "data gagal dihapus");
                }
            } else {
                $this->error("Barang dengan kode {$code} tidak ditemukan!");
            }
        } else if ($option == 13) {
            $headers = ['kode', 'nama', 'harga', 'jumlah', 'bayar', 'dibuat', 'diubah'];
            $data = SaleTransaction::all()->map(function ($item) {
                return [
                    'kode' => $item->product->code,
                    'nama' => $item->product->name,
                    'harga' => $item->product->price,
                    'jumlah' => $item->quantity,
                    'jumlah bayar' => $item->quantity * $item->product->price,
                    'dibuat' => $item->created_at,
                    'diubah' => $item->updated_at,
                ];
            })->toArray();
            $this->table($headers, $data);

        } else {

            $this->info("Terimakasih telah menggunakan applikasi kami.");
        }

    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

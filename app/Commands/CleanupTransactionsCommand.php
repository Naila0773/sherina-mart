<?php
declare(strict_types=1);

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\Models\SaleTransaction;

class CleanupTransactionsCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-transactions';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Membersihkan data penjualan yang lebih lama dari 2 minggu';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Memulai pembersihan data penjualan lama...');

        $dateThreshold = now()->subWeeks(2);
        $count = SaleTransaction::where('created_at', '<', $dateThreshold)->count();

        if ($count > 0) {
            SaleTransaction::where('created_at', '<', $dateThreshold)->delete();
            $this->info("Berhasil menghapus {$count} data penjualan yang lebih lama dari 2 minggu.");
        } else {
            $this->info("Tidak ada data penjualan yang perlu dihapus (semua data masih baru).");
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // Jalankan setiap hari pada pukul 00:00 (tengah malam)
        $schedule->command(static::class)->daily();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanTnmPersonelPozisyonTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tnm:clean-personel-pozisyon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tnm_personel_pozisyon tablosundan ID\'leri 2-9 arasında olan verileri siler.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $idsToDelete = [2, 3, 4, 5, 6, 7, 8, 9];
        $deletedRows = DB::table('tnm_personel_pozisyon')->whereIn('id', $idsToDelete)->delete();

        if ($deletedRows > 0) {
            $this->info($deletedRows . ' adet kayıt (IDler: ' . implode(', ', $idsToDelete) . ') tnm_personel_pozisyon tablosundan başarıyla silindi.');
        } else {
            $this->info('Belirtilen IDlerde (' . implode(', ', $idsToDelete) . ') silinecek kayıt bulunamadı.');
        }
        return Command::SUCCESS;
    }
}

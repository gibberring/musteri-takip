<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanTnmMarkalarTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tnm:clean-markalar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tnm_markalar tablosundan ID\'si 87 olan veriyi siler.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $idToDelete = 87;
        $deletedRows = DB::table('tnm_markalar')->where('id', $idToDelete)->delete();

        if ($deletedRows > 0) {
            $this->info('ID \'' . $idToDelete . '\' olan kayıt tnm_markalar tablosundan başarıyla silindi.');
        } else {
            $this->info('ID \'' . $idToDelete . '\' olan silinecek kayıt bulunamadı.');
        }
        return Command::SUCCESS;
    }
}

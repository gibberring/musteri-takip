<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. servis_durum tablosundaki tüm geçerli ID'leri al (string'e çevirerek).
        $validServisDurumIds = DB::table('servis_durum')
                                 ->pluck('id')
                                 ->map(fn($id) => (string)$id)
                                 ->all();

        // 2. tnm_personel_pozisyon tablosundaki kayıtları işle.
        DB::table('tnm_personel_pozisyon')->orderBy('id')->chunkById(100, function ($pozisyonlar) use ($validServisDurumIds) {
            foreach ($pozisyonlar as $pozisyon) {
                if (empty($pozisyon->asamayetkileri) || $pozisyon->asamayetkileri === ',,') {
                    // Eğer asamayetkileri zaten boşsa veya anlamsızsa (örneğin sadece ,, içeriyorsa) dokunma.
                    // Veya, eğer boşsa ve null değilse, null yapabiliriz:
                    if ($pozisyon->asamayetkileri !== null) {
                         DB::table('tnm_personel_pozisyon')
                            ->where('id', $pozisyon->id)
                            ->update(['asamayetkileri' => null, 'updated_at' => Carbon::now()]);
                    }
                    continue;
                }

                // 3a. asamayetkileri string'ini ID dizisine çevir.
                // Baştaki ve sondaki virgülleri kaldır, sonra virgülle ayır.
                $currentAsamaIds = explode(',', trim($pozisyon->asamayetkileri, ','));
                // Olası boş elemanları temizle (örneğin ',1,,2,' gibi bir durumda oluşabilir)
                $currentAsamaIds = array_filter($currentAsamaIds, fn($id) => !empty(trim($id)));

                // 3b. Sadece geçerli olan ID'leri tut.
                $filteredAsamaIds = array_intersect($currentAsamaIds, $validServisDurumIds);

                // 3c. Yeni asamayetkileri string'ini oluştur.
                $newAsamayetkileriString = null;
                if (!empty($filteredAsamaIds)) {
                    // Diziyi tekrar sıralamak, tutarlılık açısından iyi olabilir (isteğe bağlı)
                    // sort($filteredAsamaIds, SORT_STRING);
                    $newAsamayetkileriString = ',' . implode(',', $filteredAsamaIds) . ',';
                }

                // 3d. Eğer değişiklik varsa güncelle.
                if ($pozisyon->asamayetkileri !== $newAsamayetkileriString) {
                    DB::table('tnm_personel_pozisyon')
                        ->where('id', $pozisyon->id)
                        ->update(['asamayetkileri' => $newAsamayetkileriString, 'updated_at' => Carbon::now()]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu işlem, her bir kayıt için hangi ID'lerin kaldırıldığını bilmeyi gerektirdiğinden
        // otomatik olarak tam olarak geri alınamaz.
        // Geri alma işlemi için veritabanı yedeğinden geri dönülmesi veya
        // kaldırılan ID'lerin manuel olarak tespit edilip eklenmesi gerekebilir.
        // Bu nedenle bu down metodu kasıtlı olarak boştur veya sadece bir uyarı içerir.
        Schema::table('tnm_personel_pozisyon', function (Blueprint $table) {
            // Geri alma işlemi için bir sütun eklemek veya log tutmak gibi bir strateji izlenmediyse,
            // bu kısım genellikle uygulanamaz.
        });
    }
}; 
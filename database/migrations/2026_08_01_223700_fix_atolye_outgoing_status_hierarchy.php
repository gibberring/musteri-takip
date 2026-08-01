<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Atölyeye Alındı (9100) iken Parça Gidecek ile aynı çıkış seçeneklerinin
     * hiyerarşide görünmesini sağlar; Cihaz Teslim'i de Atölye/Parça Gidecek'ten açar.
     *
     * Kök sorun: 9100'e geçince 9105/9104/9115 hangi listelerinde 9100 olmadığı için
     * dropdown seçenekleri "kaybolmuş" gibi görünüyordu. 9103↔9100 çift yön zaten vardı.
     */
    public function up(): void
    {
        // Yerinde Bakım / Müşteri İptal: Parça Gidecek'ten açık, Atölye'den de açık olsun
        $this->appendAllowedPrevious(9105, 9100);
        $this->appendAllowedPrevious(9104, 9100);

        // Cihaz Teslim: yalnızca JS force ile değil, hiyerarşiden de görünsün
        $this->appendAllowedPrevious(9115, 9100);
        $this->appendAllowedPrevious(9115, 9103);
    }

    public function down(): void
    {
        $this->removeAllowedPrevious(9105, 9100);
        $this->removeAllowedPrevious(9104, 9100);
        $this->removeAllowedPrevious(9115, 9100);
        $this->removeAllowedPrevious(9115, 9103);
    }

    private function appendAllowedPrevious(int $statusId, int $previousStatusId): void
    {
        $row = DB::table('servis_durum')->where('id', $statusId)->first();
        if (!$row) {
            return;
        }

        $raw = (string) ($row->hangi_asamalarda_gorunur ?? '');
        $ids = array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($id) => $id !== ''));
        if (in_array((string) $previousStatusId, $ids, true)) {
            return;
        }

        $ids[] = (string) $previousStatusId;
        DB::table('servis_durum')->where('id', $statusId)->update([
            'hangi_asamalarda_gorunur' => implode(',', $ids),
            'updated_at' => now(),
        ]);
    }

    private function removeAllowedPrevious(int $statusId, int $previousStatusId): void
    {
        $row = DB::table('servis_durum')->where('id', $statusId)->first();
        if (!$row) {
            return;
        }

        $raw = (string) ($row->hangi_asamalarda_gorunur ?? '');
        $ids = array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($id) => $id !== ''));
        $ids = array_values(array_filter($ids, fn ($id) => $id !== (string) $previousStatusId));

        DB::table('servis_durum')->where('id', $statusId)->update([
            'hangi_asamalarda_gorunur' => implode(',', $ids),
            'updated_at' => now(),
        ]);
    }
};

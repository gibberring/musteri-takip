<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Parça Gidecek (9103) ↔ Atölyeye Alındı (9100) çift yönlü geçişi hiyerarşiye ekler.
     */
    public function up(): void
    {
        $this->appendAllowedPrevious(9100, 9103); // Atölyeye Alındı: Parça Gidecek'ten gelinebilsin
        $this->appendAllowedPrevious(9103, 9100); // Parça Gidecek: Atölye'den geri dönülebilsin
    }

    public function down(): void
    {
        $this->removeAllowedPrevious(9100, 9103);
        $this->removeAllowedPrevious(9103, 9100);
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

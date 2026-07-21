<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class SettingsAuditLog extends Model
{
    public const ACTION_MUSTERI_CONTACT_UPDATED = 'musteri_contact_updated';

    public $timestamps = false;

    protected $fillable = [
        'personel_id',
        'action',
        'subject_type',
        'subject_id',
        'summary',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }

    /**
     * Ad / tel1 / tel2 değiştiyse settings_audit_logs + laravel log.
     */
    public static function recordMusteriIletisimDegisikligi(int $musteriId, array $onceki, array $sonraki, ?int $personelId = null): void
    {
        $meta = [];
        foreach (['ad', 'tel1', 'tel2'] as $alan) {
            $o = isset($onceki[$alan]) ? trim((string) $onceki[$alan]) : '';
            $s = isset($sonraki[$alan]) ? trim((string) $sonraki[$alan]) : '';
            if ($o !== $s) {
                $meta[$alan] = [
                    'from' => (string) ($onceki[$alan] ?? ''),
                    'to' => (string) ($sonraki[$alan] ?? ''),
                ];
            }
        }
        if ($meta === []) {
            return;
        }
        $ozetParcalari = [];
        if (isset($meta['ad'])) {
            $ozetParcalari[] = 'Ad: "' . $meta['ad']['from'] . '" → "' . $meta['ad']['to'] . '"';
        }
        if (isset($meta['tel1'])) {
            $ozetParcalari[] = 'Tel1: "' . $meta['tel1']['from'] . '" → "' . $meta['tel1']['to'] . '"';
        }
        if (isset($meta['tel2'])) {
            $ozetParcalari[] = 'Tel2: "' . $meta['tel2']['from'] . '" → "' . $meta['tel2']['to'] . '"';
        }
        $summary = 'Müşteri #' . $musteriId . ' — ' . implode('; ', $ozetParcalari);
        try {
            static::create([
                'personel_id' => $personelId,
                'action' => self::ACTION_MUSTERI_CONTACT_UPDATED,
                'subject_type' => 'musteri',
                'subject_id' => $musteriId,
                'summary' => $summary,
                'meta' => $meta,
                'created_at' => now(),
            ]);
            Log::info('Müşteri iletişim bilgisi güncellendi (ayarlar günlüğü).', [
                'musteri_id' => $musteriId,
                'personel_id' => $personelId,
                'degisiklikler' => array_keys($meta),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Settings audit log yazılamadı: ' . $e->getMessage(), ['musteri_id' => $musteriId]);
        }
    }
}

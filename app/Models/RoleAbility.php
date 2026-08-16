<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleAbility extends Model
{
    use HasFactory;

    protected $table = 'role_abilities';

    protected $fillable = [
        'role_id',
        'ability',
        'allowed',
    ];

    /**
     * Personel ayarlarındaki override'ı okur; kayıt yoksa varsayılan rollere düşer.
     * permissions.js isAllowedByOverride ile aynı semantik.
     */
    public static function isAllowed(int $roleId, string $ability, array $defaultRoleIds = []): bool
    {
        $override = static::query()
            ->where('role_id', $roleId)
            ->where('ability', $ability)
            ->value('allowed');

        if ($override !== null) {
            return (int) $override === 1;
        }

        return in_array($roleId, $defaultRoleIds, true);
    }
}



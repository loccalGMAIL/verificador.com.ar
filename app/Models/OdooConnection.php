<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdooConnection extends Model
{
    protected $fillable = [
        'store_id',
        'url',
        'database',
        'api_key',
        'enabled',
        'sync_cursor',
        'last_synced_at',
        'last_sync_status',
        'last_sync_stats',
        'last_error',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'enabled' => 'boolean',
            'last_synced_at' => 'datetime',
            'last_sync_stats' => 'array',
        ];
    }

    // --- Relaciones ---

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // --- Helpers ---

    public function lastSyncFailed(): bool
    {
        return $this->last_sync_status === 'failed';
    }

    /** API key enmascarada para mostrar en pantalla (últimos 4 caracteres) */
    public function maskedApiKey(): string
    {
        return '••••••••'.substr((string) $this->api_key, -4);
    }

    /**
     * Normaliza la URL de Odoo: fuerza esquema, quita path y barra final.
     * Ej: "miempresa.odoo.com/odoo" → "https://miempresa.odoo.com"
     */
    public static function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return 'https://'.$host.$port;
    }

    /**
     * En Odoo Online la base de datos se llama igual que el subdominio.
     * Devuelve null si la URL no es *.odoo.com (dominio propio → hay que indicarla).
     */
    public static function guessDatabase(string $url): ?string
    {
        $host = strtolower((string) parse_url(self::normalizeUrl($url), PHP_URL_HOST));

        if (preg_match('/^([a-z0-9][a-z0-9-]*)\.odoo\.com$/', $host, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

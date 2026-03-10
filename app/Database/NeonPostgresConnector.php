<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Custom connector untuk Neon Postgres di Windows.
 * Menambahkan endpoint ID ke DSN agar libpq lama bisa terhubung tanpa SNI.
 */
class NeonPostgresConnector extends PostgresConnector
{
    protected function getDsn(array $config): string
    {
        // Bangun DSN standar (termasuk SSL options)
        $dsn = parent::getDsn($config);

        // Tambahkan PostgreSQL options untuk endpoint Neon
        // Ini diperlukan agar libpq lama di Windows bisa terhubung tanpa SNI
        if (! empty($config['neon_endpoint'])) {
            $endpoint = $config['neon_endpoint'];
            $dsn .= ";options=endpoint={$endpoint}";
        }

        return $dsn;
    }
}

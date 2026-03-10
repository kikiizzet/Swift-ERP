<?php

namespace App\Providers;

use App\Database\NeonPostgresConnector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binding ini dicek oleh ConnectionFactory::createConnector() (line 242)
        // jika bound, container akan make() connector ini alih-alih new PostgresConnector
        $this->app->bind('db.connector.pgsql', NeonPostgresConnector::class);
    }

    public function boot(): void
    {
        //
    }
}

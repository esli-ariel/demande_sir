<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MigrateLocalDatabase extends Command
{
    protected $signature = 'dma:migrate-local {--admin-config= : Fichier JSON privé de connexion administrative} {--pretend : Afficher le SQL sans appliquer les migrations}';
    protected $description = 'Migrer la base MySQL native locale avec une connexion administrative séparée';

    public function handle(): int
    {
        $path = $this->option('admin-config');
        if (! app()->environment('local') || ! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            $this->error('Commande réservée au mode local, avec un fichier administratif privé lisible.');
            return self::FAILURE;
        }
        if ((fileperms($path) & 0077) !== 0) {
            $this->error('Le fichier administratif doit être accessible uniquement à son propriétaire (chmod 600).');
            return self::FAILURE;
        }
        $admin = json_decode(file_get_contents($path),true);
        $current = DB::connection()->getConfig();
        if (! is_array($admin) || ($current['driver'] ?? null) !== 'mysql' || ($admin['driver'] ?? null) !== 'mysql'
            || ! in_array($admin['host'] ?? null,['127.0.0.1','localhost'],true)
            || ($admin['host'] ?? null) !== ($current['host'] ?? null)
            || ($admin['database'] ?? null) !== ($current['database'] ?? null)
            || (string) ($admin['port'] ?? '') !== (string) ($current['port'] ?? '')
            || ! empty($admin['url'])) {
            $this->error('La connexion administrative doit cibler exactement la base MySQL locale de l’application.');
            return self::FAILURE;
        }
        $default = DB::getDefaultConnection();
        unset($admin['name']);
        config(['database.connections.dma_migration'=>$admin]);
        try {
            $result = Artisan::call('migrate',['--database'=>'dma_migration','--pretend'=>(bool)$this->option('pretend'),'--force'=>true]);
            $this->output->write(Artisan::output());
            return $result;
        } finally {
            DB::setDefaultConnection($default);
            DB::purge('dma_migration');
            Schema::clearResolvedInstance('db.schema');
            config(['database.connections.dma_migration'=>null]);
        }
    }
}

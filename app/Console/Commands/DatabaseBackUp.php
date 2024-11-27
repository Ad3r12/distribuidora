<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class DatabaseBackUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Definir una constante para evitar duplicación
        define('BACKUP_PATH', storage_path() . '/app/public/backup/');

        // Eliminar archivos antiguos en el directorio de backup
        foreach (glob(BACKUP_PATH . '*') as $filename) {
            $path = BACKUP_PATH . basename($filename);
            @unlink($path);
        }

        // Obtener variables de entorno y generar el comando
        $db_pass = env('DB_PASSWORD');
        $filename = "backup-" . Carbon::now()->format('Y-m-d') . ".sql";

        if ($db_pass != '') {
            $command = env('DUMP_PATH') . " --user=" . env('DB_USERNAME') .
                       " --password='$db_pass' --host=" . env('DB_HOST') .
                       " " . env('DB_DATABASE') . " > " . BACKUP_PATH . $filename;
        } else {
            $command = env('DUMP_PATH') . " --user=" . env('DB_USERNAME') .
                       " --password=$db_pass --host=" . env('DB_HOST') .
                       " " . env('DB_DATABASE') . " > " . BACKUP_PATH . $filename;
        }

        // Ejecutar el comando
        $returnVar = null; // NULL en minúsculas
        $output = null;    // NULL en minúsculas
        exec($command, $output, $returnVar);
    }
}

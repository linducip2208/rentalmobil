<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Create a database backup using mysqldump';

    protected int $keepDays = 7;

    public function handle(): int
    {
        $this->info('Starting database backup...');

        $backupDir = storage_path('backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $filename = 'rentalmobil_' . now()->format('Y-m-d_His') . '.sql.gz';
        $filepath = $backupDir . '/' . $filename;

        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        if (!$database) {
            $this->error('Database name not configured.');
            return Command::FAILURE;
        }

        $cmd = sprintf(
            'mysqldump -h %s -P %s -u %s %s --single-transaction --routines --triggers | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        if ($password) {
            $cmd = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s --single-transaction --routines --triggers | gzip > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );
        }

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Backup failed with exit code: {$exitCode}");
            Log::error('Database backup failed', ['exit_code' => $exitCode]);
            return Command::FAILURE;
        }

        $size = File::size($filepath);
        $sizeFormatted = round($size / 1024 / 1024, 2) . ' MB';

        $this->info("Backup created: {$filename} ({$sizeFormatted})");

        $this->cleanupOldBackups($backupDir);

        Log::info('Database backup completed', [
            'filename' => $filename,
            'size' => $size,
        ]);

        return Command::SUCCESS;
    }

    protected function cleanupOldBackups(string $backupDir): void
    {
        $files = collect(File::files($backupDir))
            ->filter(fn ($file) => $file->getExtension() === 'gz')
            ->sortByDesc(fn ($file) => $file->getMTime());

        $count = 0;
        foreach ($files as $index => $file) {
            if ($index >= $this->keepDays) {
                File::delete($file->getPathname());
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("Cleaned up {$count} old backup(s).");
        }
    }
}

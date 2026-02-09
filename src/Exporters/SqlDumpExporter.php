<?php

namespace Flex\DbDump\Exporters;

use Symfony\Component\Process\Process;

class SqlDumpExporter
{
    public function handle(): string
    {
        $db   = config('database.connections.mysql');
        $path = config('flex-db-dump.path');

        // Ensure backup path exists
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        $file = $path . '/backup-' . now()->format('Ymd_His') . '.sql';

        // ✅ READ mysqldump path from config
        $mysqldump = config('flex-db-dump.mysqldump_path', 'mysqldump');

        $command = [
            $mysqldump,
            '--user=' . $db['username'],
            '--host=' . $db['host'],
            '--port=' . ($db['port'] ?? 3306),
        ];

        // Add password only if it exists
        if (!empty($db['password'])) {
            $command[] = '--password=' . $db['password'];
        }

        // Database name
        $command[] = $db['database'];

        $process = new Process($command);

        $process->run(function ($type, $buffer) use ($file) {
            file_put_contents($file, $buffer, FILE_APPEND);
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                'mysqldump failed: ' . $process->getErrorOutput()
            );
        }

        return $file;
    }
}

<?php

namespace Flex\DbDump\Exporters;

use Symfony\Component\Process\Process;

class SqlDumpExporter
{
    public function handle(): string
    {
        $db = config('database.connections.mysql');
        $path = config('flex-db-dump.path');

        // Ensure backup path exists
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        $file = $path . '/backup-' . now()->format('Ymd_His') . '.sql';

        // Build the mysqldump command
        $command = [
            'mysqldump',
            '--user=' . $db['username'],
            '--host=' . $db['host'],
        ];

        // Add password only if it exists
        if (!empty($db['password'])) {
            $command[] = '--password=' . $db['password'];
        }

        // Add the database name
        $command[] = $db['database'];

        $process = new Process($command);

        // Run the process and write output to file
        $process->run(fn ($type, $buffer) => file_put_contents($file, $buffer, FILE_APPEND));

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('mysqldump failed: ' . $process->getErrorOutput());
        }

        return $file;
    }
}

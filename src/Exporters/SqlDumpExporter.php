<?php

namespace Flex\DbDump\Exporters;

use Symfony\Component\Process\Process;

class SqlDumpExporter
{
    public function handle(): string
    {
        $db = config('database.connections.mysql');
        $path = config('flex-db-dump.path');

        if (!is_dir($path)) mkdir($path, 0775, true);

        $file = $path . '/backup-' . now()->format('Ymd_His') . '.sql';

        $process = new Process([
            'mysqldump',
            '--user=' . $db['username'],
            '--password=' . $db['password'],
            '--host=' . $db['host'],
            $db['database']
        ]);

        $process->run(fn ($t, $b) => file_put_contents($file, $b, FILE_APPEND));

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('mysqldump failed');
        }

        return $file;
    }
}

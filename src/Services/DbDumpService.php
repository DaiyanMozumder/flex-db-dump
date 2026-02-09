<?php

namespace Flex\DbDump\Services;

use Flex\DbDump\Exporters\PhpExporter;
use Flex\DbDump\Exporters\SqlDumpExporter;

class DbDumpService
{
    public function export(): string
    {
        return match (config('flex-db-dump.mode')) {
            'sql' => (new SqlDumpExporter)->handle(),
            default => (new PhpExporter)->handle(),
        };
    }
}

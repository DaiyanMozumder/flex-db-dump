<?php

namespace Flex\DbDump\Exporters;

use Illuminate\Support\Facades\DB;

class PhpExporter
{
    protected int $chunk;
    protected int $insertBatch;

    public function __construct()
    {
        $this->chunk = config('flex-db-dump.chunk', 1000);
        $this->insertBatch = 200; // rows per INSERT
    }

    public function handle(): string
    {
        $path = config('flex-db-dump.path');
        if (!is_dir($path)) mkdir($path, 0775, true);

        $file = $path . '/backup-' . now()->format('Ymd_His') . '.sql';
        $handle = fopen($file, 'w');

        // Important for huge DBs
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        fwrite($handle, "-- Flex DB Dump\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET UNIQUE_CHECKS=0;\n");
        fwrite($handle, "SET AUTOCOMMIT=0;\n\n");

        $dbName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$dbName}"};

            $this->dumpTableStructure($handle, $tableName);
            $this->dumpTableData($handle, $tableName);
        }

        fwrite($handle, "\nCOMMIT;\n");
        fwrite($handle, "SET AUTOCOMMIT=1;\n");
        fwrite($handle, "SET UNIQUE_CHECKS=1;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");

        fclose($handle);
        return $file;
    }

    protected function dumpTableStructure($handle, string $table)
    {
        fwrite($handle, "\n-- ----------------------------\n");
        fwrite($handle, "-- Structure for `$table`\n");
        fwrite($handle, "-- ----------------------------\n");

        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");

        $create = DB::select("SHOW CREATE TABLE `$table`")[0]->{'Create Table'};
        fwrite($handle, $create . ";\n\n");
    }

    protected function dumpTableData($handle, string $table)
    {
        fwrite($handle, "-- Dumping data for `$table`\n");

        $primaryKey = $this->getPrimaryKey($table);

        if ($primaryKey) {
            $this->dumpUsingPrimaryKey($handle, $table, $primaryKey);
        } else {
            $this->dumpSequential($handle, $table);
        }
    }

    /**
     * Fastest & safest (indexed chunking)
     */
    protected function dumpUsingPrimaryKey($handle, string $table, string $pk)
    {
        $lastId = 0;

        while (true) {
            $rows = DB::table($table)
                ->where($pk, '>', $lastId)
                ->orderBy($pk)
                ->limit($this->chunk)
                ->get();

            if ($rows->isEmpty()) break;

            $this->writeInsertBatch($handle, $table, $rows);

            $lastId = $rows->last()->{$pk};
        }
    }

    /**
     * Fallback if no PK (slower but safe)
     */
    protected function dumpSequential($handle, string $table)
    {
        DB::table($table)->orderByRaw('1')->chunk(
            $this->chunk,
            fn ($rows) => $this->writeInsertBatch($handle, $table, $rows)
        );
    }

    protected function writeInsertBatch($handle, string $table, $rows)
    {
        $batch = [];

        foreach ($rows as $row) {
            $values = array_map(
                fn ($v) => $v === null ? 'NULL' : "'" . addslashes((string)$v) . "'",
                (array)$row
            );

            $batch[] = '(' . implode(',', $values) . ')';

            if (count($batch) >= $this->insertBatch) {
                fwrite(
                    $handle,
                    "INSERT INTO `$table` VALUES " . implode(',', $batch) . ";\n"
                );
                $batch = [];
            }
        }

        if ($batch) {
            fwrite(
                $handle,
                "INSERT INTO `$table` VALUES " . implode(',', $batch) . ";\n"
            );
        }
    }

    protected function getPrimaryKey(string $table): ?string
    {
        $result = DB::select("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        return $result[0]->Column_name ?? null;
    }
}

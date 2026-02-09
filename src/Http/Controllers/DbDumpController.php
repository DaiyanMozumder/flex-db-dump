<?php

namespace Flex\DbDump\Http\Controllers;

use Flex\DbDump\Services\DbDumpService;

class DbDumpController
{
    public function download(DbDumpService $service)
    {
        $file = $service->export();
        return response()->download($file)->deleteFileAfterSend(true);
    }
}

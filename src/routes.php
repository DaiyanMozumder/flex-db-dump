<?php

use Flex\DbDump\Http\Controllers\DbDumpController;
use Illuminate\Support\Facades\Route;

Route::get('/flex/db-dump', [DbDumpController::class, 'download']);

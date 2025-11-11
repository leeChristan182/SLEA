<?php

use Illuminate\Support\Facades\Route;

Route::get('/ping', fn() => ['pong' => true]); // GET /api/ping

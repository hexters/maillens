<?php

use Hexters\MailLens\Http\Controllers\MailLensController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('maillens.middleware', ['web']))
    ->prefix(config('maillens.route_prefix', 'mail'))
    ->group(function () {
        Route::get('/', [MailLensController::class, 'index'])->name('maillens.index');
        Route::get('/logo.png', [MailLensController::class, 'logo'])->name('maillens.logo');
        Route::delete('/', [MailLensController::class, 'clear'])->name('maillens.clear');
        Route::get('/{message}/html', [MailLensController::class, 'html'])->name('maillens.html');
        Route::get('/{message}/source', [MailLensController::class, 'source'])->name('maillens.source');
        Route::get('/{message}/attachments/{index}', [MailLensController::class, 'attachment'])->name('maillens.attachment');
        Route::delete('/{message}', [MailLensController::class, 'destroy'])->name('maillens.destroy');
    });

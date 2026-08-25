<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:self-ordering', function (): void {
    $this->info('Self Ordering API');
})->purpose('Show application name');

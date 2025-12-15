<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

/**
 * Trait para crear la aplicación de pruebas
 */
trait CreatesApplication
{
    /**
     * Crea la aplicación.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Establecer la clave de cifrado
        Hash::setRounds(4);

        return $app;
    }
}

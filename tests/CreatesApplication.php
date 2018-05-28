<?php

namespace Alive2212\LaravelQueryHelperTest;


//use Illuminate\Contracts\Console\Kernel;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../../bootstrap/app.php';
//        $kernel = new Kernel();
//        dd($kernel);
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}

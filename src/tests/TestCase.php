<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        // Keep test-compiled Blade views away from the web process cache.
        // Otherwise tests running as root can make PHP-FPM unable to
        // overwrite the compiled files.
        $compiledPath = dirname(__DIR__) . '/storage/framework/views/testing';

        if (! is_dir($compiledPath)) {
            mkdir($compiledPath, 0775, true);
        }

        putenv('VIEW_COMPILED_PATH=' . $compiledPath);
        $_ENV['VIEW_COMPILED_PATH'] = $compiledPath;
        $_SERVER['VIEW_COMPILED_PATH'] = $compiledPath;

        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}

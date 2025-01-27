<?php

namespace Brickhouse\Rebound\Tests;

use Brickhouse\Core\Application;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        Application::create();
    }
}

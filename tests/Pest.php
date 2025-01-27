<?php

use Brickhouse\Rebound\Tests;

pest()
    ->extend(Tests\TestCase::class)
    ->in('Unit', 'Feature');

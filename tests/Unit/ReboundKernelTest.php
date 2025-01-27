<?php

use Brickhouse\Rebound\ReboundKernel;

describe('ReboundKernel', function () {
    it('returns default hostname and port given empty array', function () {
        $kernel = new ReboundKernel();

        $params = $kernel->parseHostingParameters([]);

        expect($params)->toMatchArray(['127.0.0.1', 9000]);
    });

    it('returns default port given array with hostname', function () {
        $kernel = new ReboundKernel();

        $params = $kernel->parseHostingParameters(['hostname' => '0.0.0.0']);

        expect($params)->toMatchArray(['0.0.0.0', 9000]);
    });

    it('returns default hostname given array with port', function () {
        $kernel = new ReboundKernel();

        $params = $kernel->parseHostingParameters(['port' => 9090]);

        expect($params)->toMatchArray(['127.0.0.1', 9090]);
    });

    it('returns parameters given array with hostname and port', function () {
        $kernel = new ReboundKernel();

        $params = $kernel->parseHostingParameters(['hostname' => '0.0.0.0', 'port' => 9090]);

        expect($params)->toMatchArray(['0.0.0.0', 9090]);
    });

    it('returns IP address given localhost hostname', function () {
        $kernel = new ReboundKernel();

        $params = $kernel->parseHostingParameters(['hostname' => 'localhost']);

        expect($params)->toMatchArray(['127.0.0.1', 9000]);
    });

    it('throws InvalidArgumentException given invalid port number', function (int $port) {
        $kernel = new ReboundKernel();

        $kernel->parseHostingParameters(['port' => $port]);
    })->with([
        [0],
        [-1],
        [65536],
        [1000000],
    ])->throws(\InvalidArgumentException::class);
});

<?php
/**
 *
 * LICENCE
 *
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * @author Schwartz Michaël
 * @copyright Copyright (c) EPOKMEDIA SARL
 *
 */

// Please run client.php

/** @var Composer\Autoload\ClassLoader $loader */
include __DIR__ . '/../vendor/autoload.php';

class App
{
    public function hello1($a)
    {
        return $a + 1;
    }

    public function hello2($a)
    {
        return $a + 2;
    }

    public function fail()
    {
        throw new Exception('hoge');
    }
}

function testIs($no, $a, $b)
{
    if ($a === $b) {
        echo "OK:{$no}/{$a}/{$b}\n";
    } else {
        echo "NO:{$no}/{$a}/{$b}\n";
    }
}

try {
    $server = new \Epkm\MessagePackRpc\Server('1985', new App());
    $server->recv();
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
exit;

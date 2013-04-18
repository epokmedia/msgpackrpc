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

namespace EpkmTest\MessagePackRpc;

use Epkm\MessagePackRpc\Client;
use Epkm\MessagePackRpc\Exception\RequestErrorException;
use PHPUnit_Framework_TestCase;

/**
 * Class ClientTest
 *
 * @package EpkmTest\MessagePackRpc
 */
class ClientTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = new Client('127.0.0.1', 1985);
    }

    public function tearDown()
    {
        $this->client = null;
    }

    public function testCall()
    {
        $result = $this->client->call('hello1', array(2));

        $this->assertEquals(3, $result);

        $result = $this->client->call('hello2', array(3));

        $this->assertEquals(5, $result);
    }

    /**
     * @expectedException \Epkm\MessagePackRpc\Exception\RequestErrorException
     */
    public function testCallFail()
    {
        $this->client->call('fail', array());
    }
}

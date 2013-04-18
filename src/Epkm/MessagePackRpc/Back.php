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

namespace Epkm\MessagePackRpc;


use Epkm\MessagePackRpc\Exception\NetworkErrorException;
use Epkm\MessagePackRpc\Exception\ProtocolErrorException;
use Zend\Serializer\Adapter\MsgPack;
use Zend\Stdlib\ErrorHandler;

/**
 * Class Back
 *
 * @package Epkm\MessagePackRpc
 */
class Back {

    /**
     * @var int
     */
    protected $size;

    /**
     * @var resource
     */
    public $clientSocket = null;

    /**
     * @var bool
     */
    public $reuseConnection = true;

    /**
     * @param int   $size
     * @param array $opts
     */
    public function __construct($size = 1024, $opts = array())
    {
        $this->size = $size;
        $this->serializer = new MsgPack();

        if (array_key_exists('reuse_connection', $opts)) {
            $this->reuseConnection = $opts['reuse_connection'];
        }
    }


    public function __destruct()
    {
        if ($this->clientSocket) {
            fclose($this->clientSocket);
        }
    }

    /**
     * @param $code
     * @param $func
     * @param $args
     *
     * @return array
     */
    public function clientCallObject($code, $func, $args)
    {
        $data    = array();
        $data[0] = 0;
        $data[1] = $code;
        $data[2] = $func;
        $data[3] = $args;

        return $data;
    }

    /**
     * @param $host
     * @param $port
     * @param $call
     *
     * @return string
     * @throws Exception\NetworkErrorException
     */
    public function clientConnection($host, $port, $call)
    {
        $size = $this->size;
        $send = $this->serializer->serialize($call);
        $sock = $this->connect($host, $port);

        if ($sock === false) {
            throw new NetworkErrorException("Cannot open socket");
        }

        ErrorHandler::start();
        $puts = fwrite($sock, $send);
        $error = ErrorHandler::stop();

        if ($puts === false) {
            throw new NetworkErrorException("Cannot write to socket", 0, $error);
        }

        ErrorHandler::start();
        $read = fread($sock, $size);
        $error = ErrorHandler::stop();

        if ($read === false) {
            throw new NetworkErrorException("Cannot read from socket", 0, $error);
        }

        if (!$this->reuseConnection) {
            ErrorHandler::start();
            fclose($sock);
            ErrorHandler::stop();
        }

        return $read;
    }

    /**
     * @param $host
     * @param $port
     *
     * @return null|resource
     */
    public function connect($host, $port) {

        if (!$this->reuseConnection) {
            ErrorHandler::start();
            $sock = fsockopen($host, $port);
            ErrorHandler::stop();

            return $sock;
        }

        $sock = $this->clientSocket;

        if ($sock && !feof($sock)) {
            return $sock;
        }

        if (!$sock) {
            ErrorHandler::start();
            $sock = fsockopen($host, $port);
            ErrorHandler::stop();
        } else if (feof($sock)) {
            ErrorHandler::start();
            $sock = fsockopen($host, $port);
            ErrorHandler::stop();
        }

        $this->clientSocket = $sock;

        return $sock;
    }

    /**
     * @param $recv
     *
     * @return Future
     * @throws Exception\ProtocolErrorException
     */
    public function clientRecvObject($recv)
    {
        $data = $this->serializer->unserialize($recv);

        $type = $data[0];
        $code = $data[1];
        $errs = $data[2];
        $sets = $data[3];

        if ($type != 1) {
            throw new ProtocolErrorException("Invalid message type for response: {$type}");
        }

        $feature = new Future();
        $feature->setErrors($errs);
        $feature->setResult($sets);

        return $feature;
    }

    /**
     * @param $code
     * @param $sets
     * @param $errs
     *
     * @return string
     */
    public function serverSendObject($code, $sets, $errs)
    {
        $data    = array();
        $data[0] = 1;
        $data[1] = $code;
        $data[2] = $errs;
        $data[3] = $sets;

        $send = $this->serializer->serialize($data);

        return $send;
    }

    /**
     * @param  mixed $recv
     *
     * @return array
     * @throws Exception\ProtocolErrorException
     */
    public function serverRecvObject($recv)
    {
        $data = $this->serializer->unserialize($recv);

        if (count($data) != 4) {
            throw new ProtocolErrorException("Invalid message structure.");
        }

        $type = $data[0];
        $code = $data[1];
        $func = $data[2];
        $args = $data[3];

        if ($type != 0) {
            throw new ProtocolErrorException("Invalid message type for request: {$type}");
        }

        return array($code, $func, $args);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }



}
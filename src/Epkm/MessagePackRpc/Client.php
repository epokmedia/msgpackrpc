<?php
/**
 *
 * LICENCE
 *
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * @author EPOKMEDIA
 * @copyright Copyright (c) EPOKMEDIA SARL
 *
 */

namespace Epkm\MessagePackRpc;

use Epkm\MessagePackRpc\Exception\RequestErrorException;

/**
 * Class Client
 *
 * @package Epkm\MessagePackRpc
 */
class Client {

    /**
     * @var Back
     */
    protected $back = null;

    /**
     * @var string
     */
    protected $host = null;

    /**
     * @var int
     */
    protected $port = null;

    /**
     * @param string    $host
     * @param int       $port
     * @param Back\null $back
     */
    public function __construct($host, $port, $back = null)
    {
        $this->back = $back == null ? new Back() : $back;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @param string $func
     * @param mixed  $args
     *
     * @return mixed
     * @throws Exception\RequestErrorException
     */
    public function send($func, $args)
    {
        $host    = $this->host;
        $port    = $this->port;
        $code    = 0;
        $call    = $this->back->clientCallObject($code, $func, $args);
        $send    = $this->back->clientConnection($host, $port, $call);

        $future = $this->back->clientRecvObject($send);

        $result  = $future->getResult();
        $errors  = $future->getErrors();

        if ($errors !== null) {

            if (is_array($errors)) {
                $errors = '[' . implode(', ', $errors) . ']';
            } else if (is_object($errors)) {
                if (method_exists($errors, '__toString')) {
                    $errors = $errors->__toString();
                } else {
                    $errors = print_r($errors, true);
                }
            }

            throw new RequestErrorException("{$errors}");
        }

        return $result;
    }

    /**
     * @param string $func
     * @param mixed  $args
     *
     * @return mixed
     */
    public function call($func, $args)
    {
        return $this->send($func, $args);
    }
}
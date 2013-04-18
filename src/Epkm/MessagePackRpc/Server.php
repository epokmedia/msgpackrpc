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
use Zend\Stdlib\ErrorHandler;

/**
 * Class Server for testing purpose
 *
 * @package Epkm\MessagePackRpc
 */
class Server {

    /**
     * @var int
     */
    protected $port = null;

    /**
     * @var Back
     */
    protected $back = null;

    /**
     * @var mixed
     */
    protected $hand = null;

    /**
     * @var resource
     */
    protected $listenSocket = null;

    /**
     * @param      $port
     * @param      $hand
     * @param Back $back
     */
    public function __construct($port, $hand, Back $back = null)
    {
        $this->back = $back == null ? new Back() : $back;
        $this->port = $port;
        $this->hand = $hand;
    }

    public function __destruct()
    {
        $this->closeSocket();
    }

    public function closeSocket()
    {
        if (is_resource($this->listenSocket)) {
            socket_close($this->listenSocket);
        }
    }


    /**
     * @throws Exception\NetworkErrorException
     */
    public function recv()
    {
        try {
            ErrorHandler::start();
            $this->listenSocket = socket_create_listen($this->port);
            $error = ErrorHandler::stop();
            $sockList = array($this->listenSocket);

            if ($this->listenSocket === false) {
                throw new NetworkErrorException("Cannot listen on port " . $this->port, 0, $error);
            }

            while (true) {
                $moveList = $sockList;
                $moveNums = socket_select($moveList, $w = null, $e = null, null);
                foreach ($moveList as $moveItem) {

                    if ($moveItem == $this->listenSocket) {
                        $acptItem   = socket_accept($this->listenSocket);
                        $sockList[] = $acptItem;
                    } else {
                        $data = socket_read($moveItem, $this->back->getSize());

                        list($code, $func, $args) = $this->back->serverRecvObject($data);
                        $hand = $this->hand;
                        $error = null;
                        try {
                            $ret = call_user_func_array(array($hand, $func), $args);
                        } catch (\Exception $e) {
                            $ret = null;
                            $error = $e->__toString();
                        }
                        $send = $this->back->serverSendObject($code, $ret, $error);
                        socket_write($moveItem, $send);

                        unset($sockList[array_search($moveItem, $sockList)]);
                        socket_close($moveItem);
                    }
                }
            }

        } catch (\Exception $e) {
            throw new NetworkErrorException("Server error", 0, $e);
        }
    }
}
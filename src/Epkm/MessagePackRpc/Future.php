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

/**
 * Class Future
 *
 * @package Epkm\MessagePackRpc
 */
class Future {

    /**
     * @var mixed
     */
    protected $result = null;

    /**
     * @var mixed
     */
    protected $errors = null;

    /**
     * @param $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        $result = $this->result;
        return $result;
    }

    /**
     * @param $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        $errors = $this->errors;
        return $errors;
    }
}
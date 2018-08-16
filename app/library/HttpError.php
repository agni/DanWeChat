<?php
/**
 * User: yz.chen
 * Time: 2017-11-15 21:08
 */

namespace Dandelion;

use Error;

class HttpError extends Error
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}

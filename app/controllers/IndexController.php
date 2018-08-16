<?php

namespace Dandelion\Controllers;


class IndexController extends ControllerBase
{
    public function notFoundAction()
    {
        return $this->sendNotFound();
    }

    public function forbiddenAction()
    {
        return $this->sendForbidden();
    }

    public function unauthorizedAction()
    {
        return $this->sendUnauthorized();
    }

}

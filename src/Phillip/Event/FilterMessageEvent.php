<?php

namespace Phillip\Event;

use Symfony\Component\EventDispatcher\Event;

class FilterMessageEvent extends Event
{

    protected $request;
    protected $response;
    protected $container;

    public static function create()
    {
        return new self();
    }

    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }

    public function getContainer()
    {
        return $this;
    }

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }
}

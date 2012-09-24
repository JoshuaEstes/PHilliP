<?php

namespace Phillip\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Joshua Estes
 */
class FilterMessageEvent extends Event
{

    /**
     * @var Phillip\Request
     */
    protected $request;

    /**
     * @var Phillip\Response
     */
    protected $response;

    /**
     * @car Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * @return Phillip\Event\FilterMessageEvent
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Phillip\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param Phillip\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Phillip\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Phillip\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}

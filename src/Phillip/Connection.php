<?php

namespace Phillip;

/**
 * @author Joshua Estes
 */
class Connection
{

    private $container;
    private $socket;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function getInstance()
    {
        return new self();
    }

    public function setContainer($container = null)
    {
        $this->container = $container;
        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function connect()
    {
        if (null !== $this->socket) {
            throw new \RuntimeException('Connection already made.');
        }

        $server     = $this->container->getParameter('server');
        $port       = $this->container->getParameter('port');

        if (!$this->socket = pfsockopen($server, $port)) {
            throw new \RuntimeException(sprintf('Could not connect to "%s:%s"', $server, $port));
        }
        
        $this->container->get('dispatcher')->dispatch('connect', \Phillip\Event\FilterMessageEvent::create()
            ->setResponse($this->container->get('response'))
            ->setContainer($this->container)
        );
    }

    public function getStream()
    {
        return $this->socket;
    }

    public function writeln($message)
    {
        $this->send($message . "\r\n");
    }

    public function send($message)
    {
        fwrite($this->socket, $message);
    }

    public function disconnect()
    {
        fclose($this->socket);
    }

}

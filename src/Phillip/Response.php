<?php

namespace Phillip;

class Response
{

    protected $command;
    protected $parameters = array();

    public static function create($command, $parameters = null)
    {
        $r = new self();
        $r->setCommand($command)->setParameters($parameters);
        return $r;
    }

    public function setCommand($command)
    {
        $this->command = strtoupper($command);
        return $this;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function setParameters($parameters)
    {
        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }
        $this->parameters = $parameters;
        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function isValid()
    {
        return (null !== $this->command);
    }

    public function __toString()
    {
        if ($this->isValid()) {
            return $this->getCommand() . ' '  . implode(' ', $this->getParameters());
        }

        return '';
    }

}

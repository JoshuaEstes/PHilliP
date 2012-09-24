<?php

namespace Phillip;

/**
 * @author Joshua Estes
 */
class Request
{

    protected $message;
    protected $prefix;
    protected $command;
    protected $middle;
    protected $parameters;
    protected $trailing;

    private static $RE_MSG    = '/^(?:[:@]([^\\s]+) )?([^\\s]+)(?: ((?:[^:\\s][^\\s]* ?)*))?(?: ?:(.*))?$/';
    private static $RE_SENDER = '/^([^!@]+)!(?:[ni]=)?([^@]+)@([^ ]+)$/';

    /**
     * @param null|string $message
     */
    public function __construct($message = null)
    {
        if (null !== $message) {
            $this->setMessage($message);
        }
    }

    /**
     * Parse the incoming request
     */
    protected function parse()
    {
        preg_match(self::$RE_MSG, $this->getMessage(), $matches);
        foreach ($matches as $k => $v) {
            $matches[$k] = trim(str_replace(array(chr(10), chr(13)), '', $matches[$k]));
        }
        if (count($matches)) {
            $this->prefix   = $matches[1];
            $this->command  = strtolower($matches[2]);
            $this->middle   = $matches[3] ? explode(' ', $matches[3]) : null;
            $this->trailing = isset($matches[4]) ? $matches[4] : null;
        }
    }

    /**
     * @param string $message
     */
    public static function createFromMessage($message)
    {
        $r = new self($message);
        return $r;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        $this->parse();
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return null|array
     */
    public function getMiddle()
    {
        return $this->middle;
    }

    /**
     * @return null|string
     */
    public function getTrailing()
    {
        return $this->trailing;
    }

    public function getServer()
    {
        return $this->prefix;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return boolean
     */
    public function isChannel()
    {
        return (bool) strspn($this->middle[0], '#&!+', 0, 1) >= 1;
    }

    /**
     * @return boolean
     */
    public function isFromServer()
    {
        return !$this->isFromUser();
    }

    /**
     * @return boolean
     */
    public function isFromUser()
    {
        return (bool) preg_match(self::$RE_SENDER, $this->getPrefix());
    }

    /**
     * @return null|string
     */
    public function getUser()
    {
        if ($this->isFromUser()) {
            preg_match(self::$RE_SENDER, $this->getPrefix(), $matches);
            return $matches[1];
        }

        return null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage();
    }

}

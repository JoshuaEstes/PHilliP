<?php

namespace Phillip;

use Phillip\Event\FilterMessageEvent;

/**
 * Plugins must extend the AbstractPlugin class
 *
 * @author Joshua Estes
 */
class CorePlugin extends AbstractPlugin
{

    /**
     * Events that need this class is listening for
     */
    static public function getSubscribedEvents()
    {
        return array(
            'connect'         => array('onConnect'),
            'command.ping'    => array('onPing'),
            'command.privmsg' => array('onPrivmsg'),
        );
    }

    /**
     * @param FilterMessageEvent $event
     */
    public function onConnect(FilterMessageEvent $event)
    {
        $container  = $event->getContainer();
        $username   = $container->getParameter('username');
        $hostname   = $container->getParameter('hostname');
        $servername = $container->getParameter('servername');
        $realname   = $container->getParameter('realname');
    }

    /**
     * Send a pong back to the server
     *
     * @param FilterMessageEvent $event
     */
    public function onPing(FilterMessageEvent $event)
    {
        $event
            ->getResponse()
            ->setCommand('pong')
            ->setParameters($event->getRequest()->getTrailing());
    }

    /**
     * @param FilterMessageEvent
     */
    public function onPrivmsg(FilterMessageEvent $event)
    {
        $request = $event->getRequest();
    }

}

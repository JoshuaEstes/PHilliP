<?php

namespace Phillip;

use Phillip\Event\FilterMessageEvent;
use Phillip\Response;

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
            'connect'         => array('onConnect', 1),
            'command.ping'    => array('onPing', 1),
            'command.privmsg' => array('onPrivmsg',1),
        );
    }

    /**
     * When we first connect to a server we need to send the
     * USER and NICK commends
     *
     * @param FilterMessageEvent $event
     */
    public function onConnect(FilterMessageEvent $event)
    {
        $container  = $event->getContainer();
        $username   = $container->getParameter('username');
        $hostname   = $container->getParameter('hostname');
        $servername = $container->getParameter('servername');
        $realname   = $container->getParameter('realname');

        Response::create('user', array($username, $hostname, $servername, $realname))
            ->setContainer($container)
            ->send();
        Response::create('nick', $username)
            ->setContainer($container)
            ->send();
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
        $container = $event->getContainer();
        $container->get('output')->writeln(array(
            sprintf('prefix: %s', $request->getPrefix()),
            sprintf('command: %s', $request->getCommand()),
            sprintf('middle: %s', $request->getMiddle()),
            sprintf('trailing: %s', $request->getTrailing()),
            sprintf('server: %s', $request->getServer()),
            sprintf('parameters: %s', $request->getParameters()),
            sprintf('user: %s', $request->getUser()),
            sprintf('is Channel: %s', $request->isChannel() ? 'yes' : 'no'),
            sprintf('is User: %s', $request->isUser() ? 'yes' : 'no'),
            sprintf('From Server: %s', $request->isFromServer() ? 'yes' : 'no'),
            sprintf('From User: %s', $request->isFromUser() ? 'yes' : 'no'),
        ));
    }

}

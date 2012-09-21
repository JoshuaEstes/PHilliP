<?php

namespace Phillip;

class CorePlugin extends AbstractPlugin
{

    static public function getSubscribedEvents()
    {
        return array(
            'command.ping'    => array('onPing'),
            'command.privmsg' => array('onPrivmsg'),
        );
    }

    public function onPing($event)
    {
        $event
            ->getResponse()
            ->setCommand('pong')
            ->setParameters($event->getRequest()->getTrailing());
    }

    public function onPrivmsg($event)
    {
        $request = $event->getRequest();
        var_dump(
            $request->getTrailing(),
            $request->getServer(),
            $request->getParameters(),
            $request->isChannel(),
            $request->isFromServer(),
            $request->isFromUser(),
            $request->getUser()
        );
    }

}

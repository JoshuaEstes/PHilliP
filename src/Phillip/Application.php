<?php

namespace Phillip;

use Phillip\Config\Loader\ConfigurationLoader;
use Phillip\Event\FilterMessageEvent;
use Phillip\Request;
use Phillip\Response;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Monolog\Logger;

/**
 * The main application that runs the IRC bot
 *
 * @author Joshua Estes
 */
class Application
{

    protected $socket;
    protected $container;

    public function __construct()
    {
        /**
         * Yo dawg! Heard you like DI
         */
        $this->container = new ContainerBuilder();
        $this->container
            ->register('dispatcher', 'Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher')
            ->addArgument($this->container);
        $this->container
            ->register('output', 'Symfony\Component\Console\Output\ConsoleOutput');
        $this->container
            ->register('logger.handler', 'Monolog\Handler\StreamHandler')
            ->addArgument('log/phillip.log')
            ->addArgument(Logger::INFO);
        $this->container
            ->register('logger')
            ->addArgument('phillip')
            ->addMethodCall('setHandler', array('%logger.handler%'));
        $this->container
            ->register('connection', 'Phillip\Connection')
            ->setFactoryClass('Phillip\Connection')
            ->setFactoryMethod('getInstance')
            ->addMethodCall('setContainer', array($this->container));
        $this->container
            ->register('request', 'Phillip\Request');
        $this->container
            ->register('response', 'Phillip\Response')
            ->addMethodCall('setContainer', array($this->container));

        $this->loadConfiguration();
        $this->loadPlugins();
    }

    public function run()
    {
        $this->container->get('connection')->connect();

        do {
            $message = trim(fgets($this->container->get('connection')->getStream(), 512));
            if (empty($message)) {
                continue;
            }
            $this->container->get('output')->writeln($message);

            $event = FilterMessageEvent::create()
                ->setRequest($this->container->get('request')->setMessage($message))
                ->setResponse($this->container->get('response'))
                ->setContainer($this->container);

            $this->container->get('dispatcher')->dispatch('irc.message', $event);

            $command = $event->getRequest()->getCommand();
            $this->container->get('dispatcher')->dispatch('command.'.$command, $event);

            if ($event->getResponse()->isValid()) {
                $response = (string) $event->getResponse();
                $this->container->get('output')->writeln($response);
                $this->container->get('connection')->writeln($response);
            }
        } while (!feof($this->container->get('connection')->getStream()));
    }

    /**
     * Load the configuration file. It can be located in your
     * home folder or in the config folder.
     */
    protected function loadConfiguration()
    {
        $configDirectories = array(
            getenv('HOME') . '/.ircbot',
            __DIR__ . '/../../config',
        );

        $locator             = new FileLocator($configDirectories);
        $configurationLoader = new ConfigurationLoader($locator);
        $this->configuration = $configurationLoader->load($configurationLoader->getLocator()->locate('config.yml', null, true));
        foreach ($this->configuration as $key => $value) {
            $this->container->setParameter($key, $value);
        }
    }

    /**
     * Load all the plugins
     */
    protected function loadPlugins()
    {
        $plugins = $this->configuration['plugins'];
        foreach ($plugins as $plugin) {
            if (!class_exists($plugin)) {
                throw new \RuntimeException(sprintf('Could not find plugin "%s"', $plugin));
            }
            if (!class_implements('\Phillip\AbstractPlugin')) {
                throw new \RuntimeException(sprintf('Plugin must extend "Phillip\AbstractPlugin"'));
            }
            $this->container->get('dispatcher')->addSubscriber(new $plugin());
        }
    }

}

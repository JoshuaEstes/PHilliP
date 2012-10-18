<?php

namespace Phillip;

use Phillip\Config\Loader\ConfigurationLoader;
use Phillip\Event\FilterMessageEvent;
use Phillip\Request;
use Phillip\Response;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
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
            ->addArgument('logs/phillip.log')
            ->addArgument(Logger::DEBUG);
        $this->container
            ->register('logger', 'Monolog\Logger')
            ->addArgument('phillip')
            ->addMethodCall('pushHandler', array(new Reference('logger.handler')));
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

        $manager   = new \Spork\ProcessManager(new \Spork\EventDispatcher\EventDispatcher(), true);
        $container = $this->container;
        $manager->fork(function() use ($container){
            do {
                $message = trim(fgets($container->get('connection')->getStream(), 512));
                if (empty($message)) {
                    continue;
                }
                $container->get('logger')->info($message);
                $container->get('output')->writeln($message);
                $container->get('dispatcher')->dispatch('post.request');

                $event = FilterMessageEvent::create()
                    ->setRequest($container->get('request')->setMessage($message))
                    ->setResponse($container->get('response'))
                    ->setContainer($container);

                $container->get('dispatcher')->dispatch('irc.message', $event);

                $command = $event->getRequest()->getCommand();
                $container->get('dispatcher')->dispatch('command.'.$command, $event);

                if ($event->getResponse()->isValid()) {
                    $response = (string) $event->getResponse();
                    $container->get('logger')->info($response);
                    $container->get('output')->writeln($response);
                    $container->get('connection')->writeln($response);
                }
            } while (!feof($container->get('connection')->getStream()));
        });
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
            getcwd() . '/config',
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

    protected function postResponse()
    {
        $this->container->get('output')->writeln('test');
    }

    protected function autocompleter($text)
    {
        return null;
    }

}

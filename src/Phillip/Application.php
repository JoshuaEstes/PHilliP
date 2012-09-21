<?php

namespace Phillip;

use Phillip\Config\Loader\ConfigurationLoader;
use Phillip\Event\FilterMessageEvent;
use Phillip\Request;
use Phillip\Response;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\Process\PhpProcess;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * @author Joshua Estes
 */
class Application
{

    protected $output;
    protected $socket;
    protected $configuration;
    protected $dispatcher;
    protected $container;
    protected $logger;

    public function __construct()
    {
        $this->container  = new ContainerBuilder();
        $this->dispatcher = new ContainerAwareEventDispatcher($this->container);
        $this->output     = new ConsoleOutput();
        $this->logger     = new Logger('phillip');
        $this->loadConfiguration();
        $this->loadPlugins();
    }

    public function run()
    {
        $server = $this->configuration['server'];
        $port   = $this->configuration['port'];
        $this->socket = new StreamOutput(fsockopen($server, $port));
        $this->socket->writeln((string) Response::create('user', array($this->configuration['username'], 'example.com', 'example.com', 'jestes')));
        $this->socket->writeln((string) Response::create('nick', array($this->configuration['username'])));
        do {
            $message = trim(fgets($this->socket->getStream(), 512));
            if (empty($message)) {
                continue;
            }
            $this->output->writeln($message);
            $event = FilterMessageEvent::create()
                ->setRequest(Request::createFromMessage($message))
                ->setResponse(new Response())
                ->setContainer($this->dispatcher->getContainer());
            $this->dispatcher->dispatch('irc.message', $event);
            $command = $event->getRequest()->getCommand();
            $this->dispatcher->dispatch('command.'.$command, $event);
            if ($event->getResponse()->isValid()) {
                $response = (string) $event->getResponse();
                var_dump($response);
                $this->socket->writeln($response);
            }
        } while (!feof($this->socket->getStream()));
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
            $this->dispatcher->addSubscriber(new $plugin());
        }
    }

}

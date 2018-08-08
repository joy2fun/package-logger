<?php

namespace PackageLogger;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallerEvent;
use Composer\Installer\InstallerEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Util\StreamContextFactory;
use Exception;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DEPENDENCIES_SOLVING => "onDependenciesSolved"
        ];
    }

    public function onDependenciesSolved(InstallerEvent $event)
    {
        $packages = [];

        foreach ($event->getOperations() as $op) {
            if ($op instanceof InstallOperation) {
                $packages[$op->getPackage()->getName()] = $op->getPackage()->getPrettyVersion();
            } else if ($op instanceof UpdateOperation) {
                $packages[$op->getTargetPackage()->getName()] = $op->getTargetPackage()->getPrettyVersion();
            }
        }

        $this->logPackages($packages);
    }

    protected function logPackages(array $packages) {
        if (empty ($packages)) {
            return;
        }

        $extra = $this->composer->getPackage()->getExtra();

        if (! isset($extra['package-logging-url'])) {
            return;
        }

        $url = $extra['package-logging-url'];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => ['Content-Type: application/json'],
                'content' => json_encode([
                    'packages' => $packages,
                ], JSON_UNESCAPED_SLASHES),
                'timeout' => 10,
            ],
        ];

        $context = StreamContextFactory::getContext($url, $options);

        try {
            file_get_contents($url, false, $context);
        } catch (Exception $e) {
        }
    }
}
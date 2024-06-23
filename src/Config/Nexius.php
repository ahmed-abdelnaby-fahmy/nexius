<?php

namespace Nexius\Config;

use Console\Kernel;
use DirectoryIterator;
use Symfony\Component\Console\Application;

class Nexius
{
    protected $application;

    public function __construct()
    {
        $kernel = new Kernel();
        $this->application = new Application();
        $commands = array_merge($kernel->commands(), $this->commands());
        $this->application->addCommands($commands);
    }

    public function run()
    {
        return $this->application->run();
    }

    public function commands()
    {
        $namespace = 'Nexius\\Config\\Commands';
        $commands = [];
        foreach (new DirectoryIterator(__DIR__ . '/Commands') as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                $commandClass = $namespace . '\\' . $fileInfo->getBasename('.php');
                $commands[] = new $commandClass;
            }
        }
        return $commands;
    }
}
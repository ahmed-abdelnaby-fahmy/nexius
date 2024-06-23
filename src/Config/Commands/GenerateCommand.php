<?php

namespace Nexius\Config\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:command')
            ->setDescription('Generate a new console command.')
            ->addArgument('commandName', InputArgument::REQUIRED, 'Name of the new command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandName = $input->getArgument('commandName');
        $template = <<<EOT
        <?php
        
        namespace Console\Commands;
        
        use Symfony\Component\Console\Command\Command;
        use Symfony\Component\Console\Input\InputInterface;
        use Symfony\Component\Console\Output\OutputInterface;

        class $commandName extends Command
        {
            protected function configure()
            {
                \$this->setName('$commandName')
                     ->setDescription('Description of your command');
            }

            protected function execute(InputInterface \$input, OutputInterface \$output)
            {
                // Your command logic goes here
            }
        }
        EOT;

        $fileName = $this->getDefaultPath() . "/$commandName.php"; // Change the path as needed

        // Check if the file already exists
        if (file_exists($fileName)) {
            $output->writeln("Command '$commandName' already exists in '$fileName'");
        } else {
            // Create the new command file
            if (file_put_contents($fileName, $template) !== false) {
                $output->writeln("Command '$commandName' created in '$fileName'");
            } else {
                $output->writeln("Failed to create the command '$commandName'");
            }
        }
        return 0;
    }

    private function getDefaultPath()
    {
        $currentWorkingDirectory = getcwd();
        return $currentWorkingDirectory . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Commands';
    }
}


<?php

namespace Nexius\Config\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateController extends Command
{

    protected function configure()
    {
        $this->setName('make:controller')
            ->setDescription('Generate a new controller.')
            ->addArgument('controllerName', InputArgument::REQUIRED, 'Name of the new controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $controllerName = $input->getArgument('controllerName');
        $template = <<<EOT
        <?php
        
        namespace App\Http\Controllers;
        
        use Nexius\Routing\Controller;
        
        class $controllerName extends Controller
        {
            
        }
        EOT;

        $fileName = $this->getDefaultPath() . "/$controllerName.php"; // Change the path as needed

        // Check if the file already exists
        if (file_exists($fileName)) {
            $output->writeln("Command '$controllerName' already exists in '$fileName'");
        } else {
            // Create the new command file
            if (file_put_contents($fileName, $template) !== false) {
                $output->writeln("Command '$controllerName' created in '$fileName'");
            } else {
                $output->writeln("Failed to create the command '$controllerName'");
            }
        }
        return 0;
    }

    private function getDefaultPath()
    {
        $currentWorkingDirectory = getcwd();
        return $currentWorkingDirectory . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers';
    }
}
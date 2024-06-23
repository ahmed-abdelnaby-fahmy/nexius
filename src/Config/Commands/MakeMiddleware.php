<?php

namespace Nexius\Config\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMiddleware extends Command
{
    protected function configure()
    {
        $this->setName('make:middleware')
            ->setDescription('Generate a new middleware.')
            ->addArgument('middlewareName', InputArgument::REQUIRED, 'Name of the new middleware');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $middlewareName = $input->getArgument('middlewareName');
        $request = '$request';
        $next = '$next';
        $template = <<<EOT
        <?php
        
        namespace App\Http\Middleware;
        
        class $middlewareName
        {
            public function handle($request, $next)
            {
                return $next($request);
            }
        }
        EOT;

        $fileName = $this->getDefaultPath() . "/$middlewareName.php"; // Change the path as needed

        // Check if the file already exists
        if (file_exists($fileName)) {
            $output->writeln("Command '$middlewareName' already exists in '$fileName'");
        } else {
            // Create the new command file
            if (file_put_contents($fileName, $template) !== false) {
                $output->writeln("Command '$middlewareName' created in '$fileName'");
            } else {
                $output->writeln("Failed to create the command '$middlewareName'");
            }
        }
        return 0;
    }

    private function getDefaultPath()
    {
        $currentWorkingDirectory = getcwd();
        return $currentWorkingDirectory . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Middleware';
    }
}
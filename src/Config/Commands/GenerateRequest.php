<?php

namespace Nexius\Config\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRequest extends Command
{
    protected function configure()
    {
        $this->setName('make:request')
            ->setDescription('create a new request')
            ->addArgument('requestName', InputArgument::REQUIRED, 'Name of the new request');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requestName = $input->getArgument('requestName');
        $template = <<<EOT
        <?php
        
        namespace App\Requests;
        
        use Nexius\Http\Request;
        
        class $requestName extends Request
        {
            public function rules(): array
            {
                return [
                     //rules
                ];
            }
        }
        EOT;

        $fileName = $this->getDefaultPath() . "/$requestName.php"; // Change the path as needed

        // Check if the file already exists
        if (file_exists($fileName)) {
            $output->writeln("Request '$requestName' already exists in '$fileName'");
        } else {
            // Create the new command file
            if (file_put_contents($fileName, $template) !== false) {
                $output->writeln("Request '$requestName' created in '$fileName'");
            } else {
                $output->writeln("Failed to create the request '$requestName'");
            }
        }
        return 0;
    }

    private function getDefaultPath()
    {
        $currentWorkingDirectory = getcwd();
        return $currentWorkingDirectory . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests';
    }
}
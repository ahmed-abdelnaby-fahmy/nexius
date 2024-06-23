<?php

namespace Nexius\Config\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateModel extends Command
{
    protected function configure()
    {
        $this->setName('make:model')
            ->setDescription('create a new model')
            ->addArgument('modelName', InputArgument::REQUIRED, 'Name of the new model');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $modelName = $input->getArgument('modelName');
        $template = <<<EOT
        <?php
        
        namespace App\Models;
        
        use Nexius\Database\Model;
        
        class $modelName extends Model
        {
        
        }
        EOT;

        $fileName = $this->getDefaultPath() . "/$modelName.php"; // Change the path as needed

        // Check if the file already exists
        if (file_exists($fileName)) {
            $output->writeln("Model '$modelName' already exists in '$fileName'");
        } else {
            // Create the new command file
            if (file_put_contents($fileName, $template) !== false) {
                $output->writeln("Model '$modelName' created in '$fileName'");
            } else {
                $output->writeln("Failed to create the model '$modelName'");
            }
        }
        return 0;
    }

    private function getDefaultPath()
    {
        $currentWorkingDirectory = getcwd();
        return $currentWorkingDirectory . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Models';
    }
}
<?php

namespace Nexius\Config\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{

    protected function configure()
    {
        $this->setName('serve')
            ->setDescription('Run the PHP web server')
            ->addArgument('host', InputArgument::OPTIONAL, 'Host to listen on', 'localhost')
            ->addArgument('port', InputArgument::OPTIONAL, 'Port to use', 5555);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host') ?? '127.0.0.1';
        $port = $input->getArgument('port') ?? 5555;

        $documentRoot = getcwd();
        $serverCommand = "php -S $host:$port -t $documentRoot";
        $output->writeln("Starting PHP web server on $host:$port");
        $output->writeln("Document root: $documentRoot");
        $output->writeln("Press Ctrl+C to stop the server");
        $pid = exec($serverCommand . " > /dev/null 2>&1 & echo $!");

        pcntl_signal(SIGINT, function ($signal) use ($pid, $output) {
            exec("kill $pid");
            exit(0);
        });
        sleep(PHP_INT_MAX);
        return Command::SUCCESS;
    }

}
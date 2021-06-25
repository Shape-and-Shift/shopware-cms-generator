<?php

namespace Sas\CmsGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCms extends Command
{
    public static $defaultName = 'sas:generate-cms';

    protected function configure(): void
    {
        $this->setDescription('Generates Cms Element Structure');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('');

        return 0;
    }
}
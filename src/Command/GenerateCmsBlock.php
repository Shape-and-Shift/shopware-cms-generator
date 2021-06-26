<?php

namespace Sas\CmsGenerator\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\UnicodeString;

class GenerateCmsBlock extends Command
{
    public static $defaultName = 'sas:generate-cms:block';

    private array $pluginInfos;
    private string $projectDir;

    public function __construct(string $projectDir, array $pluginInfos)
    {
        parent::__construct();
        $this->pluginInfos = $pluginInfos;
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generates Cms Block Structure')
            ->addArgument('blockName', InputArgument::REQUIRED, 'The name of the block.')
            ->addArgument('pluginName', InputArgument::REQUIRED, 'Plugin Name');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select the category where you want to create a new block)',
            [
                'commerce',
                'form',
                'image',
                'sidebar',
                'text-image',
                'text',
                'video'
            ],
            0
        );
        $question->setErrorMessage('Block category %s is invalid.');

        $blockCategory = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: '.$blockCategory);

        return Command::SUCCESS;
    }

    /**
     * Get information about the Plugin
     * @param string $name
     * @return string
     * @throws \ReflectionException
     */
    private function determinePluginPath(string $name): string
    {
        foreach ($this->pluginInfos as $pluginInfo) {
            if ($pluginInfo['name'] !== $name) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($pluginInfo['baseClass']);

            return dirname($reflectionClass->getFileName());
        }

        throw new \RuntimeException(sprintf('Cannot find plugin by name "%s"', $name));
    }
}

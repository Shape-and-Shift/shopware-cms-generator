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

        $this->buildCmsBlock(
            $input->getArgument('blockName'),
            $input->getArgument('pluginName'),
            $blockCategory
        );

        $this->buildStorefrontBlock(
            $input->getArgument('blockName'),
            $input->getArgument('pluginName'),
            $blockCategory
        );

        $output->writeln('You have just selected: '.$blockCategory);
        $output->writeln(
            'CMS Block: '.$input->getArgument('blockName') . ' scaffolding installed successfully'
        );

        return Command::SUCCESS;
    }

    /**
     * Build the CMS Block files.
     *
     * @param string $blockName
     * @param string $pluginName
     * @return void
     * @throws \ReflectionException
     */
    private function buildCmsBlock(string $blockName, string $pluginName, string $blockCategory)
    {
        // Get the plugin path
        $pluginPath = $this->determinePluginPath($pluginName);

        // Loop trough all stubs in /../../stubs/ and create an array of those
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../stubs/block/');

        // Generate the folder path
        $fileSystem = new Filesystem();
        $blockFolderPath = $pluginPath . '/Resources/app/administration/src/module/sw-cms/blocks/' . $blockCategory . '/' . $blockName . '/';

        // If folder path does not exist, create it
        if (!file_exists($blockFolderPath)) {
            $fileSystem->mkdir($blockFolderPath);
        }

        if ($finder->hasResults()) {

            foreach ($finder as $file) {
                $fileContent = file_get_contents($file->getPathname());
                $fileContent = str_replace('{{ name }}', $blockName, $fileContent);
                $fileContent = str_replace('{{ category }}', $blockCategory, $fileContent);

                // Convert element-name to element_name for the twig block
                $twigBlockName = new UnicodeString($blockName);
                $fileContent = str_replace('{{ block }}', $twigBlockName->snake(), $fileContent);

                // Convert element-name to elementName for the label
                $labelName = new UnicodeString($blockName);
                $fileContent = str_replace('{{ label }}', $labelName->camel(), $fileContent);

                // Create the index file for the element
                if (strpos($file->getFilename(), 'base')) {
                    file_put_contents($blockFolderPath . '/' . 'index.js', $fileContent);
                }

                // create the files based on the type
                if (
                    strpos($file->getFilename(), 'component') ||
                    strpos($file->getFilename(), 'preview')
                ) {

                    // Create the type string based on the stub file
                    if (strpos($file->getFilename(), 'component') ) {
                        $type = 'component';
                    } elseif (strpos($file->getFilename(), 'preview')) {
                        $type = 'preview';
                    }

                    // if folder does not exist, create it
                    if (!file_exists($blockFolderPath . '/' . $type)) {
                        $fileSystem->mkdir($blockFolderPath . '/' . $type);
                    }

                    if (strpos($file->getFilename(), 'twig')) {
                        file_put_contents($blockFolderPath . '/' . $type . '/sw-cms-block-'. $type . '-' . $blockName .'.html.twig', $fileContent);
                    }

                    if (strpos($file->getFilename(), 'scss')) {
                        file_put_contents($blockFolderPath . '/' . $type . '/sw-cms-block-'. $type . '-' .  $blockName .'.scss', $fileContent);
                    }

                    if (strpos($file->getFilename(), 'index')) {
                        file_put_contents($blockFolderPath . '/' . $type . '/sw-cms-block-'. $type . '-' .  $blockName .'.js', $fileContent);
                    }
                }
            }
        }
    }

    /**
     * Build the CMS Storefront file.
     *
     * @param string $elementName
     * @param string $pluginName
     * @return void
     * @throws \ReflectionException
     */
    public function buildStorefrontBlock(string $blockName, string $pluginName, string $blockCategory)
    {
        $storefrontTemplate = file_get_contents(__DIR__ . '/../../stubs/block/block.storefront.stub');

        // Replace placeholder within the stub file
        $storefrontTemplate = str_replace('{{ name }}', $blockName, $storefrontTemplate);

        // Convert element-name to element_name for Twig block
        $twigBlockName = new UnicodeString($blockName);
        $storefrontTemplate = str_replace('{{ block }}', $twigBlockName->snake(), $storefrontTemplate);

        // Generate the folder path
        $fileSystem = new Filesystem();
        $templateFolderPath = $this->determinePluginPath($pluginName) . '/Resources/views/storefront/block/';

        if (!file_exists($templateFolderPath)) {
            $fileSystem->mkdir($templateFolderPath);
        }

        // Move the generated file to the correct folder path
        file_put_contents($templateFolderPath . '/cms-block-' . $blockCategory . '-' . $blockName . '.html.twig', $storefrontTemplate);
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

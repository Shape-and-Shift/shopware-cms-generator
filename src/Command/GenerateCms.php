<?php

namespace Sas\CmsGenerator\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\UnicodeString;

class GenerateCms extends Command
{
    public static $defaultName = 'sas:generate-cms';

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
        ->setDescription('Generates Cms Element Structure')
        ->addArgument('elementName', InputArgument::REQUIRED, 'The name of the element.')
        ->addArgument('pluginName', InputArgument::REQUIRED, 'Plugin Name');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->buildCmsElement(
            $input->getArgument('elementName'),
            $input->getArgument('pluginName')
        );

        $this->buildComponent(
            $input->getArgument('elementName'),
            $input->getArgument('pluginName')
        );

        $output->writeln(
            'CMS Element: '.$input->getArgument('elementName') . ' scaffolding installed successfully'
        );

        return Command::SUCCESS;
    }

    /**
     * Build the CMS Element file.
     *
     * @param string $elementName
     * @param string $pluginName
     * @return void
     * @throws \ReflectionException
     */
    protected function buildCmsElement(string $elementName, string $pluginName)
    {
        $pluginPath = $this->determinePluginPath($pluginName);
        $cmsElement = file_get_contents(__DIR__ . '/../../stubs/element.index.stub');

        // Convert foo-bar to fooBar for the element label
        $elementLabel = new UnicodeString($elementName);

        // Replace placeholder within the stub file
        $cmsElement = str_replace('{{ name }}', $elementName, $cmsElement);
        $cmsElement = str_replace('{{ label }}', $elementLabel->camel(), $cmsElement);

        // Remove empty lines...
        $cmsElement = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $cmsElement);

        // Generate the folder path
        $fileSystem = new Filesystem();
        $elementFolderPath = $pluginPath . '/Resources/app/administration/src/module/sw-cms/elements/' . $elementName . '/';

        if (!file_exists($elementFolderPath)) {
            $fileSystem->mkdir($elementFolderPath);
        }

        // Move the generated file to the correct folder path
        file_put_contents($elementFolderPath . '/index.js', $cmsElement);

    }

    protected function buildComponent(string $elementName, string $pluginName)
    {
        $pluginPath = $this->determinePluginPath($pluginName);
        $cmsElement = file_get_contents(__DIR__ . '/../../stubs/element.component.index.stub');
        $componentTwig = file_get_contents(__DIR__ . '/../../stubs/element.component.twig.stub');
        $componentScss = file_get_contents(__DIR__ . '/../../stubs/element.component.scss.stub');


        // Convert foo-bar to foo_bar for the twig block
        $twigBlockName = new UnicodeString($elementName);

        // Component index.js
        $cmsElement = str_replace('{{ name }}', $elementName, $cmsElement);

        // Twig component
        $componentTwig = str_replace('{{ block }}', $twigBlockName->snake(), $componentTwig);
        $componentTwig = str_replace('{{ name }}', $elementName, $componentTwig);

        // Scss component
        $componentScss = str_replace('{{ name }}', $elementName, $componentScss);

        // Remove empty lines...
        $cmsElement = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $cmsElement);

        // Generate the folder path
        $fileSystem = new Filesystem();
        $elementFolderPath = $pluginPath . '/Resources/app/administration/src/module/sw-cms/elements/' . $elementName . '/components/';

        if (!file_exists($elementFolderPath)) {
            $fileSystem->mkdir($elementFolderPath);
        }

        // Move the generated file to the correct folder path
        file_put_contents($elementFolderPath . '/index.js', $cmsElement);
        file_put_contents($elementFolderPath . 'sw-cms-el-'. $elementName .'.html.twig', $componentTwig);
        file_put_contents($elementFolderPath . 'sw-cms-el-'. $elementName .'.scss', $componentScss);
    }

    private function getFileInformation(string $elementName, string $pluginName)
    {
        $pluginPath = $this->determinePluginPath($pluginName);
        $cmsElement = file_get_contents(__DIR__ . '/../../stubs/element.component.index.stub');

        // Replace placeholder within the stub file
        $cmsElement = str_replace('{{ name }}', $elementName, $cmsElement);

        // Remove empty lines...
        $cmsElement = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $cmsElement);
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

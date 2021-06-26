# Generate CMS elements or blocks

Tired of creating the whole folder structure for a CMS block or element?
With this plugin you can create the scaffolding within a few seconds 🚀

### Installation

- `composer require sas/cms-generator`
- `bin/console plugin:refresh`
- `bin/console plugin:install SasCmsGenerator -a`

### Commands
The first parameter accepts the name for the element or block. 
The second parameter accepts the name of the plugin for which the scaffolding should be generated.

- `bin/console sas:create-cms:element element-name PluginName`
- `bin/console sas:create-cms:block block-name PluginName`

# Generate CMS elements or blocks

This plugin puts products which are sold out always at the end of the listing and search results
and also shows a sold out badge.

### Installation

`composer require sas/cms-generator`

### Commands
The first parameter accepts the name for the element or block. 
The second parameter accepts the name of the plugin for which the scaffolding should be generated.

- `bin/console sas:create-cms:element element-name PluginName`
- `bin/console sas:create-cms:block block-name PluginName`

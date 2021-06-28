# Generate CMS elements or blocks

Tired of creating the whole folder structure for a CMS block or element?
With this plugin you can create the scaffolding within a few seconds 🚀

![](https://res.cloudinary.com/dtgdh7noz/image/upload/v1624871065/Bildschirmfoto_2021-06-28_um_11.56.55_b7t19n.png)

### Installation

- `composer require sas/cms-generator`
- `bin/console plugin:refresh`
- `bin/console plugin:install SasCmsGenerator -a`

### Commands
The first parameter accepts the name for the element or block. 
The second parameter accepts the name of the plugin for which the scaffolding should be generated.

- `bin/console sas:generate-cms:element element-name PluginName`
- `bin/console sas:generate-cms:block block-name PluginName`

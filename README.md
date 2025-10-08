# Bake Plugin for CakePHP 2.x

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/bake?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/bake?label=Packagist)](https://packagist.org/packages/pieceofcake2/bake)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/bake/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/bake/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/bake/CI.yml?label=CI)](https://github.com/pieceofcake2/bake/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/bake?label=Coverage)](https://codecov.io/gh/pieceofcake2/bake)

The Bake plugin provides code generation and scaffolding capabilities for CakePHP 2.x applications.

## Installation

### Using Composer

Install the plugin using Composer:

```bash
composer require --dev pieceofcake2/bake
```

## Usage

Once installed, you can use the bake commands:

```bash
# Bake a model
app/Console/cake bake model User

# Bake a controller
app/Console/cake bake controller Users

# Bake views
app/Console/cake bake view Users

# Bake everything for a model
app/Console/cake bake all User

# Create a new project
app/Console/cake bake project /path/to/new/project

# Create a new plugin
app/Console/cake bake plugin MyPlugin
```

## Features

- **Model Generation**: Create models with associations and validation
- **Controller Generation**: Create controllers with CRUD actions
- **View Generation**: Create views for index, view, add, and edit actions
- **Test Generation**: Create test cases for your code
- **Fixture Generation**: Create fixtures for testing
- **Plugin Generation**: Scaffold new plugins
- **Project Generation**: Create new CakePHP projects
- **Custom Templates**: Use custom bake templates for code generation

## Bake Templates

The Bake plugin uses templates for code generation. You can customize these templates by creating your own bake theme.

### Custom Bake Themes

Create a directory structure in your app:

```
app/Console/Templates/myTheme/
├── classes/
│   ├── controller.ctp
│   ├── model.ctp
│   └── fixture.ctp
└── views/
    ├── index.ctp
    ├── view.ctp
    └── form.ctp
```

Then use your theme:

```bash
app/Console/cake bake controller Users --theme myTheme
```

## Requirements

- PHP 8.0+
- CakePHP 2.11+

## License

MIT License

## Support

- Issues: https://github.com/pieceofcake2/bake/issues
- Documentation: https://book.cakephp.org/2.0/en/console-and-shells/code-generation-with-bake.html

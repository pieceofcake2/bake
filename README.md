# Bake Plugin for CakePHP 2.x

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/bake?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/bake?label=Packagist)](https://packagist.org/packages/pieceofcake2/bake)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/bake/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/bake/actions/workflows/CI.yml)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/bake/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/bake/CI.yml?label=CI)](https://github.com/pieceofcake2/bake/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/bake?label=Coverage)](https://codecov.io/gh/pieceofcake2/bake)

The Bake plugin provides the `bake` console shell, which generates models, controllers,
views, fixtures, tests, plugins and projects for CakePHP 2 applications running on
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp).

## Documentation

- Website: https://pieceofcake2.github.io/bake/
- Markdown: [docs/README.md](docs/README.md) (for AI tools and offline reading)

The Markdown documentation is also included in the Composer package, in the plugin's directory: `plugins/Bake/docs/README.md` (the location depends on your `installer-paths`). Install the package with `--dev` if you only need it for development.

## Requirements

- PHP 8.0+
- pieceofcake2/cakephp 2.12+

## Installation

```bash
composer require --dev pieceofcake2/bake
```

Then load the plugin with `CakePlugin::load('Bake')` and run `bin/cake bake`
(legacy layout: `app/Console/cake bake`).

## Contributing

See the [contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).

## License

Licensed under the MIT License. See [LICENSE](LICENSE).

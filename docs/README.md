# Bake Documentation

The Bake plugin provides the `bake` console shell, which generates models, controllers,
views, fixtures, tests, plugins and projects for CakePHP 2 applications running on
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp). See also
[Code Generation with Bake](https://pieceofcake2.github.io/docs/console-and-shells/code-generation-with-bake.html)
in the CakePHP 2 book.

- [Installation](#installation)
- [Running Bake](#running-bake)
- [Commands](#commands)
- [Options](#options)
- [Bake Themes](#bake-themes)
- [Baked Views and the Legacy Theme](#baked-views-and-the-legacy-theme)

## Installation

Install the plugin with [Composer](https://getcomposer.org/) as a development dependency.
Applications created from the [pieceofcake2/app](https://github.com/pieceofcake2/app)
skeleton already have it.

```bash
composer require --dev pieceofcake2/bake
```

The plugin has the `cakephp-plugin` type, so it is installed into your plugins directory
by `composer/installers`. Set `installer-paths` in your application's `composer.json` so it
is installed into the right place:

| Layout | `installer-paths` |
| --- | --- |
| Modern (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| Legacy (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

Load the plugin in `config/bootstrap.php` (legacy layout: `app/Config/bootstrap.php`).
If you already use `CakePlugin::loadAll()`, this is not necessary:

```php
CakePlugin::load('Bake');
```

Bake is a development dependency, so it is not installed when you deploy with
`composer install --no-dev`. Loading a plugin that is not installed throws a
`MissingPluginException`, so only load it where it is installed, for example:

```php
if (Configure::read('debug')) {
    CakePlugin::load('Bake');
}
```

## Running Bake

Run Bake from the project root with `bin/cake` (legacy layout: `app/Console/cake`):

```bash
# Bake a model
bin/cake bake model User

# Bake a controller
bin/cake bake controller Users

# Bake views
bin/cake bake view Users

# Bake the model, controller, views, fixture and tests of a model
bin/cake bake all User

# Bake a new plugin
bin/cake bake plugin MyPlugin
```

Running `bin/cake bake` (or a command such as `bin/cake bake model`) without a name starts
an interactive mode that guides you through the generation. If `database.php` does not
exist yet, Bake first offers to create it.

The files are written to the directories of your application, so a modern application gets
`src/Model/`, `src/Controller/` and `templates/`, and a legacy application gets
`app/Model/`, `app/Controller/` and `app/View/`.

Use `--help` to see the arguments and options of each command:

```bash
bin/cake bake controller --help
```

## Commands

| Command | Description |
| --- | --- |
| `all [<model>]` | Bake the model, controller and views of a model, and its fixture and tests when PHPUnit is installed. |
| `model [<name>]` | Bake a model with its validation rules and associations. `model all` bakes all models. |
| `controller [<name>]` | Bake a controller. `--public` adds the CRUD actions (`index`, `view`, `add`, `edit`, `delete`), `--admin` adds them for one of the `Routing.prefixes`. `controller all` bakes all controllers. |
| `view [<controller>] [<action>] [<alias>]` | Bake the views (`index`, `view`, `add`, `edit`) of a controller, or a single view. |
| `fixture [<name>]` | Bake a test fixture. `fixture all` bakes the fixtures of all tables. |
| `test [<type>] [<name>]` | Bake a test case for a controller, model, helper, component or behavior. |
| `plugin [<name>]` | Bake the directory structure, `AppController` and `AppModel` of a new plugin. |
| `db_config` | Bake the `database.php` configuration file. |
| `project [<path>]` | Bake a new application from a skeleton. |

Names can be given as `Plugin.Name` to bake into a plugin, for example
`bin/cake bake model ContactManager.Contact`.

`bake project` copies the legacy `app/`-style skeleton bundled with the plugin
(`Console/Templates/skel`). Use `--skel` to copy another directory. To start a new
application with the modern layout, use `composer create-project pieceofcake2/app`
instead (see [pieceofcake2/app](https://github.com/pieceofcake2/app)).

## Options

| Option | Commands | Description |
| --- | --- | --- |
| `--connection`, `-c` | all, model, controller, view, fixture | Database connection to use (default: `default`). |
| `--plugin`, `-p` | model, controller, view, fixture, test | Plugin to bake into. |
| `--theme`, `-t` | all commands | [Bake theme](#bake-themes) to use. |
| `--force`, `-f` | model, controller, view, fixture, test | Overwrite existing files without prompting. |
| `--public` | controller | Add the CRUD actions. |
| `--admin` | controller, view | Add the actions or views for the admin routing prefix. |
| `--count`, `-n` | fixture | Number of records to generate (default: 1). |
| `--records`, `-r` | fixture | Import records from the live table (`--count` records, default 10). |
| `--schema`, `-s` | fixture | Import the schema instead of writing it into the fixture. |
| `--empty` | project | Create empty files in each directory, for example to keep them in Git. |
| `--skel` | project | Directory to copy the new application from. |

## Bake Themes

Bake generates the code from templates. The default templates are in the plugin's
`Console/Templates/default/` directory:

```
Console/Templates/default/
├── actions/
│   └── controller_actions.ctp
├── classes/
│   ├── controller.ctp
│   ├── fixture.ctp
│   ├── model.ctp
│   └── test.ctp
└── views/
    ├── form.ctp
    ├── index.ctp
    └── view.ctp
```

To customize the generated code, create a bake theme: a directory in
`src/Console/Templates/` of your application (legacy layout: `app/Console/Templates/`) or
in `Console/Templates/` of a plugin, with the templates you want to change. Copy them from
the default theme and edit them:

```
src/Console/Templates/myTheme/
├── classes/
│   ├── controller.ctp
│   ├── fixture.ctp
│   └── model.ctp
└── views/
    ├── form.ctp
    ├── index.ctp
    └── view.ctp
```

A theme directory must contain at least one of the `actions/`, `classes/` and `views/`
directories. Templates that a theme does not have are taken from the other installed themes
and finally from the default theme.

Select the theme with `--theme`:

```bash
bin/cake bake controller Users --theme myTheme
```

When more than one theme is installed and no theme is given, Bake asks which one to use.

## Baked Views and the Legacy Theme

The views baked by the `default` theme follow the templates of CakePHP 5's bake and use
the styles of the [pieceofcake2/app](https://pieceofcake2.github.io/app/) skeleton
(`normalize.min.css`, `milligram.min.css`, `fonts.css` and `cake.css`, the stylesheets of
the CakePHP 5 application skeleton):

- `index.ctp`: a content box with a "New" button, a responsive table with sortable columns
  and View / Edit / Delete links, and a paginator.
- `view.ctp` and `form.ctp` (add and edit): an "Actions" side navigation next to the
  content box. The view shows the record in a table and the related records of `hasOne`,
  `hasMany` and `hasAndBelongsToMany` associations below it.

The generated code still uses the CakePHP 2 helpers (`$this->Form->input()`,
`$this->Paginator->numbers()`, `$this->Form->postLink()` and so on).

![A baked index view](img/baked-index.png)

![A baked view](img/baked-view.png)

![A baked edit form](img/baked-edit.png)

### Applications that use cake.generic.css

Applications created from an older skeleton load `cake.generic.css` in their layout. The
new markup does not match that stylesheet (the action links are not laid out as a side
column, and the buttons and the paginator are not styled), so such applications should
bake their views with the bundled `legacy` theme, which keeps the templates of the
earlier Bake releases:

```bash
bin/cake bake view Articles --theme legacy
bin/cake bake all Article --theme legacy
```

The `legacy` theme only contains view templates; models, controllers, fixtures and tests
come from the `default` theme. It is never selected automatically: without `--theme`,
Bake uses the `default` theme, and the `legacy` theme is not offered when Bake asks which
of several installed themes to use.

To use the new templates in an older application instead, copy the stylesheets from
`webroot/css/` (and the fonts from `webroot/font/`) of
[pieceofcake2/app](https://github.com/pieceofcake2/app) and load them in your layout.

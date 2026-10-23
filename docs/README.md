# Bake Documentation

The Bake plugin provides the `bake` console shell, which generates models, controllers,
views, fixtures, tests, plugins and projects for CakePHP 2 applications running on
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp). See also
[Code Generation with Bake](https://pieceofcake2.github.io/docs/console-and-shells/code-generation-with-bake.html)
in the CakePHP 2 book.

- [Installation](#installation)
- [Running Bake](#running-bake)
- [Namespaced Applications and Plugins](#namespaced-applications-and-plugins)
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

Bake looks at the application or plugin that it bakes into, and writes the files where and as
that application or plugin keeps them:

| | Detected from | Result |
| --- | --- | --- |
| Namespace | `App.namespace` of the application; a `src/` directory of a plugin | Namespaced classes (`App\Model\Post`, `ContactManager\Model\Contact`), else global classes; see [Namespaced Applications and Plugins](#namespaced-applications-and-plugins) |
| Classes | The paths of the application (`App::path()`); for a plugin `src/` or the plugin directory | `src/Model/`, `src/Controller/` or `app/Model/`, `app/Controller/`; `src/Model/` or `Model/` of a plugin |
| Views | `templates/` of the application or plugin, else `View/` | `templates/Posts/` or `View/Posts/` |
| View extension | The views that are already there | `.ctp` in `View/`, which CakePHP 2.13 only reads as `.ctp`. In `templates/`, `.php`, unless the views there are `.ctp` files: first the views of the controller, then all views (an application of pieceofcake2/app-legacy keeps baking `.ctp`) |
| Test cases and fixtures | `tests/TestCase/` or `Test/Case/` of the application or plugin | `tests/TestCase/` and `tests/Fixture/`, or `Test/Case/` and `Test/Fixture/` |

Bake writes the views of a controller only; the layouts and elements stay as they are, with
the directory names that the application uses (`layout/`, `element/`, `email/`, `Error/` or
`Layouts/`, `Elements/`, `Emails/`, `Errors/`).

A modern application autoloads `src/` and the CakePHP classes with the Composer classmap,
so the baked classes and tests have no `App::uses()` calls, except for the class under test
of a component, helper or behavior test (a class baked just now is not in the classmap
until `composer dump-autoload` runs). Classes baked for a legacy application or into a
plugin keep their `App::uses()` calls. A namespaced application imports the classes with
`use` statements instead, see [Namespaced Applications and Plugins](#namespaced-applications-and-plugins).

The baked code has the code style of the [pieceofcake2/app](https://github.com/pieceofcake2/app)
skeleton: 4 spaces of indentation, short arrays (`[]`), the opening braces of classes and
methods on their own lines, and the return types that PHPUnit 9 needs in the test cases
(`setUp(): void`). The classes, fixtures and test cases pass the `CakePHP2` standard of
[pieceofcake2/phpcs-cakephp2](https://github.com/pieceofcake2/phpcs-cakephp2), and the
`CakePHP2Namespace` standard in a namespaced application.

Use `--help` to see the arguments and options of each command:

```bash
bin/cake bake controller --help
```

## Namespaced Applications and Plugins

pieceofcake2/cakephp 2.13 lets an application keep its classes in a namespace: the namespace
is set in `App.namespace` of `config/core.php`, and Composer loads the classes with PSR-4
(`App\` from `src/`, `App\Test\` from `tests/`), as in the
[pieceofcake2/app](https://github.com/pieceofcake2/app) skeleton. When `App.namespace` is set,
Bake writes namespaced classes:

| Baked | Namespace | File |
| --- | --- | --- |
| Model | `App\Model` | `src/Model/Post.php` |
| Controller | `App\Controller` | `src/Controller/PostsController.php` |
| Views | | `templates/Posts/index.php`, `view.php`, `add.php`, `edit.php` |
| Fixture | `App\Test\Fixture` | `tests/Fixture/PostFixture.php` |
| Test case | `App\Test\TestCase\Model` | `tests/TestCase/Model/PostTest.php` |

The test cases of the other classes are in the namespace of their directory, e.g.
`App\Test\TestCase\Controller\Component` for `tests/TestCase/Controller/Component/`. A baked
controller starts like this:

```php
<?php

namespace App\Controller;

use App\Model\Post;
use Cake\Controller\Component\PaginatorComponent;
use Cake\Error\NotFoundException;

/**
 * Posts Controller
 *
 * @property Post $Post
 * @property PaginatorComponent $Paginator
 */
class PostsController extends AppController
{
    /**
     * Components
     *
     * @var array
     */
    public $components = [PaginatorComponent::class];
```

- The classes are imported with `use` statements, sorted alphabetically, and the docblocks
  name them by their short names, as the `CakePHP2Namespace` standard of
  [pieceofcake2/phpcs-cakephp2](https://github.com/pieceofcake2/phpcs-cakephp2) expects. There
  are no `App::uses()` calls.
- The class names of the settings (`$components`, `$helpers`, `$actsAs` and the `className` of
  the associations) are written as `::class` when the class exists when Bake runs, as
  `bin/cake upgrade namespace` of [pieceofcake2/upgrade](https://github.com/pieceofcake2/upgrade)
  writes them. A class that does not exist yet, such as the model of an associated table
  that is not baked yet, keeps its name in a string (`'className' => 'Comment'`), which
  CakePHP resolves when the application runs.
- The test cases name their fixtures as before (`'app.post'`); CakePHP finds
  `App\Test\Fixture\PostFixture` for them.

A plugin with the CakePHP 5 layout, a plugin with a `src/` directory, has its classes in the
namespace of the plugin name. Bake writes the classes of such a plugin into `src/` (for
example `src/Model/Contact.php` in the `ContactManager\Model` namespace), the views into
`templates/` and the fixtures and test cases into `tests/` (`ContactManager\Test\...`).
The models and controllers extend `ContactManagerAppModel` and `ContactManagerAppController`
of the plugin's namespace, as in other plugins; `bake plugin` creates a plugin with the
CakePHP 2 layout, which has global classes. Add the plugin to the PSR-4 autoloading of your
`composer.json`:

```json
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "ContactManager\\": "plugins/ContactManager/src/"
    }
},
"autoload-dev": {
    "psr-4": {
        "App\\Test\\": "tests/",
        "ContactManager\\Test\\": "plugins/ContactManager/tests/"
    }
}
```

Applications without `App.namespace` and plugins without `src/` get the same global classes
as before.

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

`bake project` copies the skeleton bundled with the plugin (`Console/Templates/skel`), which
is the skeleton of [pieceofcake2/app](https://github.com/pieceofcake2/app): namespaced classes
in `src/` (`App\`), `.php` templates in `templates/` with the CakePHP 5 directory names
(`layout/`, `element/`, `email/`, `Error/`), `config/` with the `.env` files, `tests/`,
`webroot/`, `bin/cake`, `composer.json` and the configuration of phpcs, PHPStan and PHPUnit. It
leaves out what belongs to the skeleton repository only (its README, LICENSE, documentation,
CI workflows and Qlty configuration). Bake then writes a random `Security.salt` and
`Security.cipherSeed` as the defaults in `config/core.php` (the environment variables
`SECURITY_SALT` and `SECURITY_CIPHER_SEED` still win) and the name of the directory as the
cache prefix. Run `composer install` in the new directory before you use it:

```bash
bin/cake bake project /path/to/myapp
cd /path/to/myapp
composer install
```

`--empty` keeps the `empty` files that keep the empty directories in Git. Use `--skel` to copy
another directory. `composer create-project pieceofcake2/app` creates the same application.
For the former form (global classes, `.ctp` templates), see
[pieceofcake2/app-legacy](https://github.com/pieceofcake2/app-legacy).

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
│   └── controller_actions.php
├── classes/
│   ├── controller.php
│   ├── fixture.php
│   ├── model.php
│   └── test.php
└── views/
    ├── form.php
    ├── index.php
    └── view.php
```

To customize the generated code, create a bake theme: a directory in
`src/Console/Templates/` of your application (legacy layout: `app/Console/Templates/`) or
in `Console/Templates/` of a plugin, with the templates you want to change. Copy them from
the default theme and edit them:

```
src/Console/Templates/myTheme/
├── classes/
│   ├── controller.php
│   ├── fixture.php
│   └── model.php
└── views/
    ├── form.php
    ├── index.php
    └── view.php
```

A theme directory must contain at least one of the `actions/`, `classes/` and `views/`
directories. Templates that a theme does not have are taken from the other installed themes
and finally from the default theme.

The templates are `.php` files. The `.ctp` templates of themes made for earlier releases of
Bake still work; when a directory has both, the `.php` file is used.

Select the theme with `--theme`:

```bash
bin/cake bake controller Users --theme myTheme
```

When more than one theme is installed and no theme is given, Bake asks which one to use.

For a [namespaced application or plugin](#namespaced-applications-and-plugins), the class
templates get the namespace of the file in `$namespace` and the classes of the `use`
statements in `$imports`; `$namespace` is `null` for global classes. A theme that copied
`classes/` from an earlier release writes global classes; copy the templates again to get
the namespaces.

## Baked Views and the Legacy Theme

The views baked by the `default` theme follow the templates of CakePHP 5's bake and use
the styles of the [pieceofcake2/app](https://pieceofcake2.github.io/app/) skeleton
(`normalize.min.css`, `milligram.min.css`, `fonts.css` and `cake.css`, the stylesheets of
the CakePHP 5 application skeleton):

- `index.php`: a content box with a "New" button, a responsive table with sortable columns
  and View / Edit / Delete links, and a paginator.
- `view.php` and `form.php` (add and edit): an "Actions" side navigation next to the
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

## Origin

This package is based on the `bake` console shell of
[cakephp/cakephp](https://github.com/cakephp/cakephp) (`cakephp/cakephp` on Packagist, last upstream
version 2.10.24), originally by the CakePHP community (Cake Software Foundation, Inc.). It was
extracted into a separate package and is maintained for CakePHP 2 on modern PHP by
[pieceofcake2](https://github.com/pieceofcake2). Changes from the original are listed in the
[commit history](https://github.com/pieceofcake2/bake/commits/1.x).

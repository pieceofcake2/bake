# Bake ドキュメント

Bake プラグインは `bake` コンソールシェルを提供します。このシェルは、
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp) で動作する CakePHP 2 アプリケーションのモデル、
コントローラー、ビュー、フィクスチャ、テスト、プラグイン、プロジェクトを生成します。CakePHP 2 のクックブックの
[Bake でコード生成](https://pieceofcake2.github.io/docs/ja/console-and-shells/code-generation-with-bake.html)
も参照してください。

- [インストール](#installation)
- [Bake の実行](#running-bake)
- [名前空間を使うアプリケーションとプラグイン](#namespaced-applications-and-plugins)
- [コマンド](#commands)
- [オプション](#options)
- [Bake テーマ](#bake-themes)
- [bake したビューと legacy テーマ](#baked-views-and-the-legacy-theme)

<a id="installation"></a>

## インストール

[Composer](https://getcomposer.org/) で、開発用の依存パッケージとしてプラグインをインストールします。
[pieceofcake2/app](https://github.com/pieceofcake2/app) スケルトンから作成したアプリケーションには、すでに含まれています。

```bash
composer require --dev pieceofcake2/bake
```

このプラグインは `cakephp-plugin` タイプなので、`composer/installers` によってプラグインのディレクトリに
インストールされます。正しい場所にインストールされるように、アプリケーションの `composer.json` で
`installer-paths` を設定してください。

| 構成 | `installer-paths` |
| --- | --- |
| モダン (`config/`、`src/`、`templates/`、`plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| レガシー (`app/Config/`、`app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

プラグインは `config/bootstrap.php` (レガシー構成では `app/Config/bootstrap.php`) で読み込みます。
すでに `CakePlugin::loadAll()` を使っている場合は必要ありません。

```php
CakePlugin::load('Bake');
```

Bake は開発用の依存パッケージなので、`composer install --no-dev` でデプロイした環境にはインストールされません。
インストールされていないプラグインを読み込むと `MissingPluginException` が投げられるので、インストールされている
環境でだけ読み込んでください。たとえば次のようにします。

```php
if (Configure::read('debug')) {
    CakePlugin::load('Bake');
}
```

<a id="running-bake"></a>

## Bake の実行

プロジェクトルートで `bin/cake` (レガシー構成では `app/Console/cake`) を使って Bake を実行します。

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

名前を指定せずに `bin/cake bake` (または `bin/cake bake model` などのコマンド) を実行すると、対話モードが始まり、
生成の手順を案内します。`database.php` がまだない場合、Bake はまずそれを作成するか尋ねます。

ファイルはアプリケーションのディレクトリに書き込まれます。モダン構成のアプリケーションでは `src/Model/`、
`src/Controller/` と `templates/` に、レガシー構成のアプリケーションでは `app/Model/`、`app/Controller/` と
`app/View/` に書き込まれます。

モダン構成のアプリケーションは、`src/` と CakePHP のクラスを Composer のクラスマップでオートロードするので、
bake したクラスとテストには `App::uses()` の呼び出しがありません。例外は、コンポーネント、ヘルパー、ビヘイビアの
テストでのテスト対象のクラスです (bake したばかりのクラスは、`composer dump-autoload` を実行するまでクラスマップに
含まれないためです)。レガシー構成のアプリケーション向けや、プラグインの中に bake したクラスでは、`App::uses()` の
呼び出しが残ります。名前空間を使うアプリケーションでは、代わりに `use` 文でクラスをインポートします。
[名前空間を使うアプリケーションとプラグイン](#namespaced-applications-and-plugins) を参照してください。

bake したコードは、[pieceofcake2/app](https://github.com/pieceofcake2/app) スケルトンと同じコードスタイルです。
インデントは 4 つのスペースで、配列は短い構文 (`[]`) で書き、クラスとメソッドの開き波かっこは次の行に置きます。
テストケースは、PHPUnit 9 が必要とする戻り値の型を宣言します (`setUp(): void`)。クラス、フィクスチャ、
テストケースは、[pieceofcake2/phpcs-cakephp2](https://github.com/pieceofcake2/phpcs-cakephp2) の `CakePHP2`
規約に、名前空間を使うアプリケーションでは `CakePHP2Namespace` 規約に合格します。

各コマンドの引数とオプションは `--help` で確認できます。

```bash
bin/cake bake controller --help
```

<a id="namespaced-applications-and-plugins"></a>

## 名前空間を使うアプリケーションとプラグイン

pieceofcake2/cakephp 2.13 では、アプリケーションのクラスを名前空間に置けます。名前空間は `config/core.php` の
`App.namespace` で設定し、クラスは Composer が PSR-4 で読み込みます (`App\` を `src/` から、`App\Test\` を
`tests/` から)。[pieceofcake2/app](https://github.com/pieceofcake2/app) スケルトンはこの形です。`App.namespace` が
設定されていると、Bake は名前空間付きのクラスを書き込みます。

| bake するもの | 名前空間 | ファイル |
| --- | --- | --- |
| モデル | `App\Model` | `src/Model/Post.php` |
| コントローラー | `App\Controller` | `src/Controller/PostsController.php` |
| ビュー | | `templates/Posts/index.php`、`view.php`、`add.php`、`edit.php` |
| フィクスチャ | `App\Test\Fixture` | `tests/Fixture/PostFixture.php` |
| テストケース | `App\Test\TestCase\Model` | `tests/TestCase/Model/PostTest.php` |

ほかのクラスのテストケースは、ディレクトリに対応する名前空間に入ります。たとえば
`tests/TestCase/Controller/Component/` なら `App\Test\TestCase\Controller\Component` です。bake したコントローラーは
次のように始まります。

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

- クラスは `use` 文でインポートされ、アルファベット順に並びます。docblock では短い名前で書かれます。
  [pieceofcake2/phpcs-cakephp2](https://github.com/pieceofcake2/phpcs-cakephp2) の `CakePHP2Namespace` 規約が求める
  形です。`App::uses()` の呼び出しはありません。
- 設定に書くクラス名 (`$components`、`$helpers`、`$actsAs` とアソシエーションの `className`) は、Bake の実行時に
  クラスがあれば `::class` で書かれます。[pieceofcake2/upgrade](https://github.com/pieceofcake2/upgrade) の
  `bin/cake upgrade namespace` が書くのと同じ形です。まだ bake していない関連テーブルのモデルのように、クラスがまだ
  ないときは、名前を文字列のまま書きます (`'className' => 'Comment'`)。この名前は、アプリケーションの実行時に
  CakePHP が解決します。
- テストケースは、これまでどおりの名前でフィクスチャを指定します (`'app.post'`)。CakePHP はこの名前から
  `App\Test\Fixture\PostFixture` を見つけます。
- ビューは `templates/` の `.php` ファイルです。

CakePHP 5 の構成のプラグイン、つまり `src/` ディレクトリがあるプラグインは、プラグイン名の名前空間にクラスを
置きます。Bake は、そのようなプラグインのクラスを `src/` に (たとえば `ContactManager\Model` 名前空間の
`src/Model/Contact.php`)、ビューを `templates/` に、フィクスチャとテストケースを `tests/`
(`ContactManager\Test\...`) に書き込みます。モデルとコントローラーは、ほかのプラグインと同じように、プラグインの
名前空間の `ContactManagerAppModel` と `ContactManagerAppController` を継承します。`bake plugin` が作るのは
CakePHP 2 の構成のプラグインで、そのクラスはグローバルです。プラグインは、`composer.json` の PSR-4 の
オートロードに追加してください。

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

`App.namespace` のないアプリケーションと、`src/` のないプラグインには、これまでと同じグローバルなクラスが
書き込まれます。

<a id="commands"></a>

## コマンド

| コマンド | 説明 |
| --- | --- |
| `all [<model>]` | 1 つのモデルについて、モデル、コントローラー、ビューと、PHPUnit がインストールされていればそのフィクスチャとテストを bake します。 |
| `model [<name>]` | バリデーションルールとアソシエーションを含むモデルを bake します。`model all` はすべてのモデルを bake します。 |
| `controller [<name>]` | コントローラーを bake します。`--public` は CRUD アクション (`index`、`view`、`add`、`edit`、`delete`) を追加し、`--admin` は `Routing.prefixes` のいずれかに対してそれらを追加します。`controller all` はすべてのコントローラーを bake します。 |
| `view [<controller>] [<action>] [<alias>]` | コントローラーのビュー (`index`、`view`、`add`、`edit`)、または 1 つのビューを bake します。 |
| `fixture [<name>]` | テストのフィクスチャを bake します。`fixture all` はすべてのテーブルのフィクスチャを bake します。 |
| `test [<type>] [<name>]` | コントローラー、モデル、ヘルパー、コンポーネント、ビヘイビアのテストケースを bake します。 |
| `plugin [<name>]` | 新しいプラグインのディレクトリ構成、`AppController` と `AppModel` を bake します。 |
| `db_config` | `database.php` 設定ファイルを bake します。 |
| `project [<path>]` | スケルトンから新しいアプリケーションを bake します。 |

プラグインの中に bake するには、`bin/cake bake model ContactManager.Contact` のように、名前を `Plugin.Name` の形で
指定します。

`bake project` は、プラグインに同梱されているレガシーな `app/` 形式のスケルトン (`Console/Templates/skel`) を
コピーします。別のディレクトリをコピーするには `--skel` を使います。モダン構成の新しいアプリケーションを始めるには、
代わりに `composer create-project pieceofcake2/app` を使ってください
([pieceofcake2/app](https://github.com/pieceofcake2/app) を参照)。

<a id="options"></a>

## オプション

| オプション | コマンド | 説明 |
| --- | --- | --- |
| `--connection`、`-c` | all、model、controller、view、fixture | 使用するデータベース接続 (デフォルト: `default`)。 |
| `--plugin`、`-p` | model、controller、view、fixture、test | bake する先のプラグイン。 |
| `--theme`、`-t` | すべてのコマンド | 使用する [Bake テーマ](#bake-themes)。 |
| `--force`、`-f` | model、controller、view、fixture、test | 確認せずに既存のファイルを上書きします。 |
| `--public` | controller | CRUD アクションを追加します。 |
| `--admin` | controller、view | admin ルーティングプレフィックス用のアクションまたはビューを追加します。 |
| `--count`、`-n` | fixture | 生成するレコードの数 (デフォルト: 1)。 |
| `--records`、`-r` | fixture | 実際のテーブルからレコードをインポートします (`--count` 件、デフォルト 10)。 |
| `--schema`、`-s` | fixture | スキーマをフィクスチャに書き込む代わりにインポートします。 |
| `--empty` | project | 各ディレクトリに空のファイルを作成します。たとえば Git でディレクトリを残すためです。 |
| `--skel` | project | 新しいアプリケーションのコピー元のディレクトリ。 |

<a id="bake-themes"></a>

## Bake テーマ

Bake はテンプレートからコードを生成します。デフォルトのテンプレートは、プラグインの `Console/Templates/default/`
ディレクトリにあります。

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

生成されるコードをカスタマイズするには、bake テーマを作成します。bake テーマは、アプリケーションの
`src/Console/Templates/` (レガシー構成では `app/Console/Templates/`) か、プラグインの `Console/Templates/` に置く
ディレクトリで、変更したいテンプレートを入れます。テンプレートはデフォルトのテーマからコピーして編集します。

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

テーマのディレクトリには、`actions/`、`classes/`、`views/` のディレクトリのうち少なくとも 1 つが必要です。
テーマにないテンプレートは、インストールされている他のテーマから、最後にはデフォルトのテーマから取得されます。

テーマは `--theme` で選択します。

```bash
bin/cake bake controller Users --theme myTheme
```

複数のテーマがインストールされていて、テーマが指定されていない場合、Bake はどれを使うか尋ねます。

[名前空間を使うアプリケーションやプラグイン](#namespaced-applications-and-plugins) では、クラスのテンプレートは
ファイルの名前空間を `$namespace` で、`use` 文のクラスを `$imports` で受け取ります。グローバルなクラスでは
`$namespace` は `null` です。以前のリリースから `classes/` をコピーしたテーマはグローバルなクラスを書き込むので、
名前空間を使うには、テンプレートをコピーし直してください。

<a id="baked-views-and-the-legacy-theme"></a>

## bake したビューと legacy テーマ

`default` テーマで bake したビューは CakePHP 5 の bake のテンプレートに倣っており、
[pieceofcake2/app](https://pieceofcake2.github.io/app/ja/) スケルトンのスタイル (CakePHP 5 のアプリケーション
スケルトンのスタイルシートである `normalize.min.css`、`milligram.min.css`、`fonts.css` と `cake.css`) を使います:

- `index.ctp`: 「New」ボタンのあるコンテンツボックス、並べ替えのできる列と View / Edit / Delete リンクを持つ
  レスポンシブなテーブル、そしてページネーター。
- `view.ctp` と `form.ctp` (add と edit): コンテンツボックスの横に「Actions」のサイドナビゲーション。
  view はレコードをテーブルで表示し、その下に `hasOne`、`hasMany`、`hasAndBelongsToMany` アソシエーションの
  関連レコードを表示します。

生成されるコードは、引き続き CakePHP 2 のヘルパー (`$this->Form->input()`、`$this->Paginator->numbers()`、
`$this->Form->postLink()` など) を使います。

![bake した index ビュー](../img/baked-index.png)

![bake したビュー](../img/baked-view.png)

![bake した edit フォーム](../img/baked-edit.png)

<a id="applications-that-use-cakegenericcss"></a>

### cake.generic.css を使うアプリケーション

古いスケルトンから作成したアプリケーションは、レイアウトで `cake.generic.css` を読み込んでいます。新しいマークアップは
このスタイルシートに合わない (アクションのリンクがサイドの列として配置されず、ボタンとページネーターにスタイルが
付かない) ので、そうしたアプリケーションでは、同梱の `legacy` テーマでビューを bake してください。このテーマは
以前の Bake のリリースのテンプレートを維持しています。

```bash
bin/cake bake view Articles --theme legacy
bin/cake bake all Article --theme legacy
```

`legacy` テーマに含まれるのはビューのテンプレートだけです。モデル、コントローラー、フィクスチャ、テストは `default`
テーマから取得されます。このテーマが自動的に選択されることはありません。`--theme` を指定しなければ Bake は `default`
テーマを使い、インストールされている複数のテーマのどれを使うか Bake が尋ねるときにも `legacy` テーマは候補に表示されません。

古いアプリケーションで新しいテンプレートを使う場合は、代わりに
[pieceofcake2/app](https://github.com/pieceofcake2/app) の `webroot/css/` からスタイルシートを
(そして `webroot/font/` からフォントを) コピーし、レイアウトで読み込んでください。

<a id="origin"></a>

## 由来

このパッケージは [cakephp/cakephp](https://github.com/cakephp/cakephp) (Packagist では
`cakephp/cakephp`、アップストリームの最終バージョンは 2.10.24) の `bake` コンソールシェルをもとに別のパッケージとして切り出したもので、
元の作者は CakePHP コミュニティ (Cake Software Foundation, Inc.) です。
[pieceofcake2](https://github.com/pieceofcake2) が、モダンな PHP で動く CakePHP 2 向けに
メンテナンスしています。オリジナルからの変更点は
[コミット履歴](https://github.com/pieceofcake2/bake/commits/1.x)
で確認できます。

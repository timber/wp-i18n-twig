# wp-i18n-twig

Parsing WordPress translations in Twig/Timber templates has always been quirky and not very convenient. Especially since WordPress grabs translations from multiple sources (theme stylesheet, {block,theme}.json, block editor JS, PHP files).

Since WP-CLI is the recommanded way to create/update POT files, `timber/wp-i18n-twig` provides (almost native) Twig translation extraction by overriding the default `wp i18n make-pot`.

## Install

Because WP-CLI default commands are not meant to be overriden, there are two working ways to install `timber/wp-i18n-twig`:

### Global

If you want to use it with a global installed `wp` (`/usr/local/bin/wp` or similar), you have to install it as a WP-CLI package:

```bash
wp package install timber/wp-i18n-twig
```

### Local

You can also install the package at the project level using composer and run WP-CLI from the local `vendor/bin/wp`:

```bash
composer require timber/wp-i18n-twig
```

## Usage

Usage is the same than `wp i18n make-pot` command.

```bash
wp i18n make-pot /path/to/my/theme languages/my-theme.pot
```

`--debug` flag is handy if you want to check the templates that have been parsed (or check for parsing errors):

```bash
wp i18n make-pot /path/to/my/theme languages/my-theme.pot --debug
```

Please refer to the [`wp i18n` command documentation](https://developer.wordpress.org/cli/commands/i18n/) for more detailed information.

To match `wp i18n make-pot` behavior, a `--skip-twig` flag has been added in case skipping Twig files extraction is needed.

## Translations

`timber/wp-i18n-twig` handles the [same translations functions and feature scope](features/makepot.feature) `wp i18n` does.

It does also support translators comments, at [an evil cost](/src/Utils/TwigFunctionsScanner.php#L29-L39).

Adding translators comments in your Twig templates works like it would in PHP, place it on the same line or the line before the translation string:

```twig
{# translators: Translators 1! #}
{{ __( 'hello world', 'foo-theme' ) }}

{# Translators: Translators 2! #}
{{ __( 'foo', 'foo-theme' ) }}

{# translators: this should get extracted. #} {{ __( 'baba', 'foo-theme' ) }}

{# translators: boo #} {# translators: this should get extracted too. #} {# some other comment #} {{__( 'bubu', 'foo-theme' ) }}
```

## Limitations

To make this package work in any Twig environement, templates are not compiled to PHP but only parsed. This main benefit is that it's (almost) configuration agnostic (custom filters, functions, template location).

However, since Twig is highly configurable, there are still some (minor) limitations.

- Custom tokens (such as `{% cache %}` when using `twig/cache-extra`) will throw parsing errors. This might be handled in the future by requiring optional extensions.
- Using [non default tags](https://github.com/twigphp/Twig/blob/4c179c8a64fece77e17ef299d1a2a3f908993107/src/Lexer.php#L58-L64) will obviously end in complete parsing failure.

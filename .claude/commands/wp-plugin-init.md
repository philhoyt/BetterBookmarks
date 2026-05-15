---
description: Scaffold a new WordPress plugin with the standard structure, security baseline, and test setup
---

Scaffold a new WordPress plugin. Ask for the following if not already provided:
1. **Plugin name** (human-readable, e.g. "My Awesome Plugin")
2. **Plugin slug** (lowercase-hyphenated, e.g. "my-awesome-plugin") — used for text domain, prefixes, and directory name
3. **PHP namespace prefix** (UpperCamelCase, e.g. "MyAwesomePlugin")
4. **Description** (one sentence)
5. **Author name**
6. **Minimum WordPress version** (default: 6.4)
7. **Minimum PHP version** (default: 8.1)

## Files to Generate

### `plugin-slug.php` (main file)
- Plugin header comment block
- `ABSPATH` guard
- Version/path constants
- Composer autoload require
- Bootstrap via `plugins_loaded`

### `includes/class-plugin-slug.php`
- Main plugin class
- `__construct()` with loader, i18n, admin, public instantiation
- `run()` method

### `includes/class-plugin-slug-loader.php`
- Actions array, filters array
- `add_action()`, `add_filter()`, `run()` methods

### `includes/class-plugin-slug-i18n.php`
- `load_plugin_textdomain()` on `plugins_loaded`

### `includes/class-plugin-slug-admin.php`
- Constructor with `$plugin_name`, `$version`
- `enqueue_styles()` and `enqueue_scripts()` stubs

### `includes/class-plugin-slug-public.php`
- Constructor with `$plugin_name`, `$version`
- `enqueue_styles()` and `enqueue_scripts()` stubs

### `admin/css/plugin-slug-admin.css` — empty placeholder
### `admin/js/plugin-slug-admin.js` — empty placeholder
### `public/css/plugin-slug-public.css` — empty placeholder
### `public/js/plugin-slug-public.js` — empty placeholder

### `composer.json`
- PSR-4 autoloading for the namespace
- `require-dev`: phpunit, wpcs, phpcs
- Scripts: `lint`, `lint:fix`, `test`

### `phpunit.xml`
- Bootstrap pointing to `tests/phpunit/bootstrap.php`
- Test suites: `unit` and `integration`
- Coverage include: `includes/`

### `tests/phpunit/bootstrap.php`
- Load Composer autoloader
- Load WordPress test suite (compatible with `wp-env`)

### `tests/phpunit/test-plugin-core.php`
- One `WP_UnitTestCase` test class
- `test_plugin_is_defined()` checking the version constant

### `.phpcs.xml`
```xml
<?xml version="1.0"?>
<ruleset name="Plugin Standards">
    <rule ref="WordPress"/>
    <arg name="extensions" value="php"/>
    <file>.</file>
    <exclude-pattern>vendor/*</exclude-pattern>
    <exclude-pattern>tests/*</exclude-pattern>
    <exclude-pattern>node_modules/*</exclude-pattern>
</ruleset>
```

### `package.json`
```json
{
  "scripts": {
    "env:start": "wp-env start",
    "env:stop": "wp-env stop",
    "test:php": "wp-env run tests-phpunit -- phpunit"
  }
}
```

### `.wp-env.json`
```json
{
  "plugins": ["."],
  "phpVersion": "8.1"
}
```

## After Scaffolding

Tell the user:
1. Run `composer install` to install PHP dependencies
2. Run `npm install` and `npm run env:start` to spin up the local environment
3. Run `npm run test:php` to verify the scaffold works
4. Fill in the `CLAUDE.md` at the project root with project-specific details

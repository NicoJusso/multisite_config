# Multi-site Configuration

[Documentation en français](README.fr.md)

Multi-site Configuration (`multisite_config`) lets Drupal sites sharing the same
active configuration storage use site-specific configuration values. It is
intended for multisite installations sharing a database and a common configuration
baseline.

## Features

- Read shared configuration with site-specific overrides.
- Save configuration through Drupal's configuration factory under a site prefix.
- Seed new site-specific configuration from the effective shared configuration,
  excluding the top-level `_core` and `uuid` properties.
- Separate cache bins by site key through cache factory decorators.
- Suppress the standard configuration override check on configuration forms for
  sites with a key.

## Requirements

- The module metadata declares Drupal `^10 || ^11` compatibility. Compatibility
  with each target release still needs validation: the implementation uses
  attribute-based hooks (`Drupal\Core\Hook\Attribute\Hook`).
- PHP 8.2 or later is required by the module's syntax; your Drupal version may
  require a newer version.
- Sites must already be configured to share the same active configuration
  storage. The module does not set up multisite routing or database connections.
- No additional contributed module dependencies are declared. Drush is optional
  and is used in the command examples below.

## Installation

1. Place the `multisite_config` directory in a Drupal modules directory, for
   example `web/modules/custom/multisite_config`.
2. Enable the module on the shared installation, using the appropriate site URI:

   ```sh
   drush --uri=https://main.example.org en multisite_config -y
   ```

The module does not currently contain a `composer.json`. These instructions use
manual placement rather than assume that a published Composer package exists.

## Configuration

In each site's `settings.php`, assign a unique, non-empty string key:

```php
$settings['multisite_config_key'] = 'site_a';
```

Use a different key for another site:

```php
$settings['multisite_config_key'] = 'site_b';
```

Leave this setting undefined on the site that manages the shared baseline.
There is no module-specific administration page.

Rebuild caches for each site after enabling the module or changing its key:

```sh
drush --uri=https://main.example.org cr
drush --uri=https://a.example.org cr
drush --uri=https://b.example.org cr
```

Keep keys stable: changing a key selects a different configuration prefix and
cache namespace; it does not migrate existing data.

## Usage example

Suppose the shared `system.site` configuration contains the name `Shared site`.
On the site using the key `site_a`, open **Configuration > System > Basic site
settings** (`/admin/config/system/site-information`), change the site name to
`Site A`, and save.

Code using the configuration factory follows the same behavior:

```php
// On the site using the key "site_a".
\Drupal::configFactory()
  ->getEditable('system.site')
  ->set('name', 'Site A')
  ->save();

$name = \Drupal::config('system.site')->get('name'); // Site A.
```

The editable configuration is stored as `site_a.system.site`. Reads of
`system.site` merge that site's stored values over the shared configuration.
A site without a key continues to read and edit `system.site` directly.

The first editable copy is populated from the effective shared values. Saving a
form can therefore save more than the field just changed. Values stored in this
copy continue to override subsequent changes to the shared baseline.

Cache bin names receive the suffix `.multisite_site_a` for this site when
obtained through the decorated cache factories.

## Scope and limitations

- This module changes the configuration factory and decorates cache factories.
  Check compatibility with other modules replacing or decorating these services.
- It does not isolate content, users, sessions, files, or database tables in
  general, and is not a security boundary between independent tenants.
- Code accessing configuration storage directly bypasses the factory behavior.
- Prefixed records reside in the same active configuration storage. The module
  provides no dedicated export/import workflow, configuration schema mapping,
  or migration tooling for these records. Validate configuration synchronization
  and configuration entity workflows before deployment.
- Removing a site key or uninstalling the module does not provide a migration
  of prefixed configuration back to the baseline. Plan data cleanup separately.
- No automated tests are currently included in the module directory.

## Contributing and maintenance

Use the issue tracker and contribution workflow of the repository distributing
this module. For bug reports, include Drupal and PHP versions, relevant module
versions, anonymized site keys, reproduction steps, and expected versus actual
behavior. Never include credentials or a complete production `settings.php`.

Contributions should describe their impact on shared configuration, site-specific
writes, and cache separation. The module currently provides no maintainer contact
or dedicated support URL.

## License

The included [LICENSE](LICENSE) contains the GNU General Public License,
version 3 (GPLv3).

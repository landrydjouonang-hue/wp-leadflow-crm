# Contributing

Thanks for taking a look at WP LeadFlow CRM.

## Getting set up

1. Install WordPress 6.4+ on PHP 8.0+ (XAMPP, Local, wp-env — anything works).
2. Clone this repository into `wp-content/plugins/wp-leadflow-crm`.
3. Activate the plugin from the **Plugins** screen.
4. Turn on debugging while you work:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'SCRIPT_DEBUG', true );   // cache-busts CSS/JS with file times
define( 'LEADFLOW_CRM_DEBUG', true ); // plugin debug log
```

There is no build step: no compiler, no bundler, no runtime dependencies.

## Coding standards

The code follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/),
with one deliberate exception: files are named after their class for PSR-4
autoloading instead of `class-*.php`.

```bash
composer install
composer phpcs      # check
composer phpcbf     # fix what can be fixed automatically
composer lint       # php -l over every file
```

House rules worth knowing:

- **Security:** every state change checks a nonce, then a capability, then
  writes; every value is escaped at output; all SQL goes through
  `$wpdb->prepare()` inside `src/Data` or `src/Database`.
- **Data:** never query the CRM tables outside a repository.
- **Translations:** every user-facing string uses the `wp-leadflow-crm`
  text domain.
- **Accessibility:** admin screens must work with the keyboard and a
  screen reader, and without JavaScript wherever that is reasonable.
- **No new dependencies** unless there is a strong reason.

## Architecture in one minute

- `src/Core` — activation, upgrades, uninstall.
- `src/Database` — schema and migrations (add a new migration; never edit
  an applied one).
- `src/Data` — repositories, models, analytics, retention.
- `src/Admin` — reusable admin layer (screens, forms, list tables, notes).
- `src/Modules/<Feature>` — one folder per feature area.
- `src/Rest` — REST controllers.
- `views/` — templates; they receive `$data` and escape everything.

Full details: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md),
[docs/DATABASE.md](docs/DATABASE.md), [docs/API.md](docs/API.md).

## Pull requests

- Describe the change and why it is needed.
- Keep it focused: one topic per pull request.
- Say how you tested it (screens visited, roles used, data involved).
- Update the docs and `CHANGELOG.md` when behaviour changes.
- New database columns or indexes need a migration and a `DATABASE.md` entry.

## Reporting bugs

Open an issue with the WordPress and PHP version, what you expected, what
happened, and the relevant part of **LeadFlow CRM → Settings → System
status**. For anything security-related, see [SECURITY.md](SECURITY.md)
instead — please do not open a public issue.

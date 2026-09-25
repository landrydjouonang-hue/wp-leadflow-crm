# Changelog

All notable changes to this project are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/) and the
project uses [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-09-24

First stable release.

### Added
- *Settings → Data*: retention policies for activities, sent emails and trashed records (off by default), a daily background clean-up in batches, "Run clean-up now", and the result of the last run.
- Debug logging (*Settings → Advanced*): a private, rotating log file that never records passwords or tokens, with a viewer, a download and "Clear log" on *System status*.
- System status: retention, cron and logging rows, plus a downloadable report.
- Export tools: a complete JSON backup of every CRM record and the settings, streamed in batches.
- Contextual help on every CRM screen (what the screen does, who may do what) with documentation links that the `leadflow_crm_docs_url` filter can point at a published copy of `docs/`.
- Empty states with a call to action on every list, "Clear filters" when a search has no results, and a consistent busy state on buttons that start long actions.
- Repository files for publication: `LICENSE` (GPL-2.0), `CONTRIBUTING.md`, `SECURITY.md`, `docs/TESTING.md`, `.gitignore`, `.distignore`, `.editorconfig`, `phpcs.xml.dist`, `composer.json` and a GitHub Actions workflow (PHP 8.0–8.3 syntax check and PHPCS).
- Migration `add_email_log_index` (`emails.created_at`).

### Changed
- Flash notices are announced to screen readers (`role="status"`, or `role="alert"` for errors).
- Record counts and grouped counts are cached per request and invalidated on every write, so list screens with status views issue fewer queries.

### Fixed
- Documentation links in the help sidebar pointed at the site root when no documentation URL was configured; they now show the file name instead.

## [0.9.0] - 2026-09-23

### Added
- Access control screen (*LeadFlow CRM → Access control*, `leadflow_manage_users`): a permission matrix for every CRM capability and every role, a Team tab to give users the CRM Manager or Sales Agent role, and API access information.
- Role-based permissions: changes are stored as overrides on top of the module defaults, apply to the admin and the API at once, and survive plugin updates. Administrators always keep every CRM permission; non-administrators cannot edit their own role, grant permissions they lack, or hand out roles stronger than their own.
- Capabilities `leadflow_manage_users`, `leadflow_manage_notes`, `leadflow_manage_others_notes` and `leadflow_manage_activities`. Notes now need "Manage notes"; pinning or deleting other people's notes needs "Manage others' notes".
- REST API: full CRUD for leads, companies, contacts, tasks and activities (`GET|POST /{resource}`, `GET|PUT|PATCH|DELETE /{resource}/{id}`) with permission callbacks, schema-driven validation and sanitization, paging headers and `Link`, filters, sorting, `context` and `_links`, `201 Created` with `Location`, trash/restore/force delete, and documented error codes. Authentication through the logged-in cookie with a nonce or WordPress Application Passwords.
- `docs/API.md`, `OPTIONS` schema on every route, and the hooks `leadflow_crm_rest_insert_{entity}`, `leadflow_crm_rest_prepare_{entity}`, `leadflow_crm_permission_groups`, `leadflow_crm_permissions_saved`, `leadflow_crm_user_role_changed`.
- `Repository::definitions()`, `writable()`, `soft_deletes()`, `entity()`.

### Changed
- The id/name lookup endpoints used by the admin pickers moved from `/leads`, `/companies`, `/contacts` to `/lookup/…`, so the collection routes can serve the full API. Update any custom integration that used them.
- `Rest\Controller` now extends `WP_REST_Controller`.

## [0.8.0] - 2026-09-22

### Added
- Analytics dashboard: KPI cards for total, new, qualified, won and lost leads, pipeline value (with weighted value), conversion rate, open tasks, overdue tasks and upcoming follow-ups, with trends vs the previous period.
- Charts: leads by stage, leads by source, monthly leads (new + won), won vs lost, pipeline value by stage. Dependency-free SVG renderer (`assets/js/charts.js`, dashboard only) with keyboard-accessible tooltips, data tables, reduced-motion support and responsive redraw.
- Date filtering: 8 presets and custom range (site timezone), plus an owner filter (everyone, only me, or a team member for managers). Filters are in the URL and work without JavaScript.
- `Data\Analytics` aggregate queries and `Modules\Dashboard\AnalyticsReport` with object caching invalidated on every CRM change.
- Migration `add_analytics_indexes` (`leads.status_closed`, `leads.owner_created`).
- Filters `leadflow_crm_qualified_stage`. `Page` load callback on the dashboard.

### Changed
- The dashboard welcome text is replaced by the filters; module widgets are grouped under "Your work" (section titles are now `h3`).

## [0.7.0] - 2026-09-22

### Added
- Email sending from leads, contacts and companies: "Send email" button and compose screen with recipient suggestions, Cc/Bcc, template picker (with no-JavaScript fallback), placeholders, live preview as the recipient sees it, "Send me a copy" and double-submit protection.
- Emails are sent through `wp_mail()` (works with any SMTP plugin) as HTML in a responsive layout with a plain-text alternative. Transport errors are captured and shown.
- Emails tab on record screens with expandable history and "Send again" / "Try again".
- *LeadFlow CRM → Emails* log: status views (all, sent, failed), search, sender filter for managers, and a screen for each email.
- Sent emails appear on the activity timeline ("Email sent: …").
- *Settings → Email*: From name, From email, Reply-To (sender or From address), signature with placeholders, "Send me a copy" default, emails per user per hour (default 50), and a test email.
- Capabilities `leadflow_send_emails`, `leadflow_view_emails`, `leadflow_view_others_emails` and meta capability `leadflow_view_email`. Sending also requires edit permission on the record.
- Security: header-injection protection for addresses, names and subjects; per-record nonces; hourly sending limit.
- Migration `create_emails_table` (`{prefix}leadflow_emails`, 4 foreign keys).
- Hooks: `leadflow_crm_record_actions`, `leadflow_crm_email_recipients`, `leadflow_crm_email_message`, `leadflow_crm_email_headers`, `leadflow_crm_email_html`, `leadflow_crm_email_hourly_limit`, `leadflow_crm_email_sent`, `leadflow_crm_email_failed`.

### Changed
- Emails are related records: they follow the same parent inheritance and permanent-delete rules as activities, notes, tasks and follow-ups. Deleting a template unlinks it from sent emails.
- The template placeholder picker and live preview are reused on the compose screen.

## [0.6.0] - 2026-09-21

### Added
- Email templates: list, add, edit, preview. 19 placeholders (contact, company, lead, general) with fallbacks (`{{first_name|there}}`), a click-to-insert placeholder picker, AJAX live preview with sample data or a real lead/contact, and validation of unknown placeholders. Three starter templates. Sending is not included yet.
- CSV import for leads, contacts and companies: upload, automatic column mapping, conversion of money, dates, countries, statuses, stages, owners, companies and contacts, repository validation, duplicate detection (database and within the file) with skip/update/create modes, exact preview, AJAX batch import with progress (no-JS fallback), summary and downloadable error report.
- CSV export for leads, contacts and companies with status and "only mine" filters. Exports re-import cleanly.
- Security: dedicated `leadflow_import_data` / `leadflow_export_data` capabilities, protected random-named upload folder with daily cleanup, formula-injection protection in exports and error reports, per-user import sessions.
- `Repository::pluck()`, `Repository::check()`, `EntityAdmin::form_aside()`.
- Migration `create_email_templates_table`.

### Changed
- Uninstalling with "Delete all data" also removes `uploads/leadflow-crm`.

## [0.5.0] - 2026-09-21

### Added
- Tasks module: list (Overdue and My open tasks views, due/priority/assignee filters, due-date sort with undated last, bulk complete), add, edit and view screens. Related lead/company/contact pickers with REST search.
- Task completion checkboxes via AJAX in lists, record tabs, the dashboard and the task screen, with a no-JS fallback link.
- Overdue indicators: red row marker and "Overdue" pill, a Tasks menu bubble with your overdue count, overdue counts on lead screens and the dashboard.
- Activity timeline on company, contact and lead screens: automatic entries (created, stage changes, won/lost, assignment, value, notes, tasks, follow-ups, trash/restore), "Log activity" (call/email/meeting) via AJAX, "Load older" via AJAX.
- Follow-ups: schedule with date/time, type, notes and assignee. Mark as done with an outcome (AJAX) or cancel. "Next follow-up" column on the leads list and in the lead sidebar.
- Record screens use accessible tabs: Activity, Leads, Tasks, Follow-ups, Notes.
- Dashboard: My tasks, My follow-ups and Recent activity widgets (all placeholders replaced).
- REST endpoint `GET leadflow-crm/v1/leads`.
- Capabilities `leadflow_{view,edit,edit_others,delete}_tasks`.
- Filters `leadflow_crm_auto_log_activities`. Hook `leadflow_crm_record_panels` now passes a `RecordPanels` registry.
- Timezone-aware `datetime` form fields (stored in UTC).

### Fixed
- Activities could not be logged for records in the trash.

## [0.4.0] - 2026-09-21

### Added
- Leads module: list (search, status views, stage/assignee/company/source filters, sorting, pagination, bulk trash and "Assign to…"), add, edit and view screens.
- Lead fields: name, company, contact, email, phone, source, value and currency, status, sales stage, win probability, assigned user, expected close date, lost reason, description, created date.
- Configurable pipeline stages (Settings → Pipeline): rename, color, default probability, type (open/won/lost), reorder, add, and delete with lead reassignment.
- Visual pipeline board with drag and drop and an accessible "Move to" select, saved via AJAX with live column totals, plus a no-JavaScript fallback.
- Lead screen: clickable stage bar, "Mark as won / lost", assignment form, notes.
- "Leads" panel on company and contact screens (via the new `leadflow_crm_record_panels` hook).
- Dashboard: live Sales pipeline and Leads widgets.
- REST endpoint `GET leadflow-crm/v1/contacts`.
- Capabilities `leadflow_{view,edit,edit_others,delete}_leads` and `leadflow_manage_pipeline`.
- Migrations: `pipeline_stages` table (7 default stages), leads `email`, `phone`, `position`.
- `AbstractMigration::add_column()` / `add_index()`, `Table::column_exists()` / `index_exists()`, repository `color` type, `Format::money()`.
- Extensible bulk actions in `EntityAdmin`, `date`/`number` fields in `FormRenderer`.

### Changed
- Lead status now follows the stage type. Entering a stage applies its default probability.

## [0.3.0] - 2026-09-21

### Added
- Companies module: list, add, edit and view screens with search, status views (incl. Trash), owner/country filters, sorting, pagination, Screen Options, bulk trash/restore/delete, and a contacts count column.
- Contacts module: list, add, edit and view screens with company and owner filters, company picker with REST live search, and pre-selecting the company from a company profile.
- Notes panel on company and contact profiles: add, pin/unpin, delete, with contact notes shown on the company.
- Optional first note when creating a company or contact.
- Capabilities `leadflow_{view,edit,edit_others,delete}_{companies,contacts}` and meta capabilities `leadflow_{edit,delete}_{company,contact}`. Sales Agents edit only records they own.
- Reusable admin layer: `EntityAdmin`, `EntityListTable`, `FormRenderer` (accessible fields and error summary), `NoteActions`, `UI` helpers.
- Dashboard: live Companies and Contacts widgets replace their placeholders.
- REST endpoint `GET leadflow-crm/v1/companies`.
- Country list (ISO 3166-1, translatable), date/user formatting helpers.
- `Repository::distinct_values()`, `Repository::count_by()`. `query()` primes the per-record cache.

### Changed
- Slug fields are normalized with `sanitize_title()` ("Web Form" → `web-form`).
- The admin page header supports an avatar, a status badge and confirmation/danger buttons.

## [0.2.0] - 2026-09-19

### Added
- CRM database schema: companies, contacts, leads, activities, notes, tasks, follow-ups (7 migrations, applied automatically on upgrade).
- InnoDB foreign keys between CRM tables (SET NULL / CASCADE), idempotent and skipped on non-InnoDB engines.
- Soft deletes (`deleted_at`/`deleted_by`) on all entities except the append-only activity log.
- User ownership and audit columns (`owner_id`, `assigned_to`, `created_by`, `updated_by`) with automatic reassignment when a WordPress user is deleted.
- Data access layer: base `Repository` (validation, prepared queries with `%i` identifiers, pagination, search, soft delete, object cache, lifecycle hooks) and one repository per entity.
- `Relations` service for relationship retrieval, with batch loading.
- Parent inheritance: related records automatically inherit contact/company from their lead or contact.
- Permanent-delete rules applied in a transaction (`Transaction::run()`).
- `Migrator::current_version()` schema version. System status shows the schema version, table health and foreign key support.
- `docs/DATABASE.md`.

### Changed
- Uninstall, rollback and multisite site deletion drop tables with foreign key checks disabled.

## [0.1.0] - 2026-09-18

### Added
- Plugin bootstrap with PHP/WordPress requirements check and admin notice.
- PSR-4 autoloader, service container and modular architecture (`Contracts\Module`).
- Database migration system with a migrations log table, batches, rollback and error reporting.
- Version management: automatic upgrades on version change, with locking and back-off.
- Activation, deactivation and uninstall handling (opt-in data removal), multisite aware.
- Capability framework with *CRM Manager* and *Sales Agent* roles.
- Security helpers: `Guard`, `Nonce`, `Input`.
- Settings framework on the WordPress Settings API (tabs, typed fields, sanitization).
- Settings: business name, default currency, records per page, delete data on uninstall.
- CRM dashboard with placeholder sections for Leads, Companies, Contacts, Tasks, Follow-ups, Activities and the Sales pipeline.
- System status tab and `GET leadflow-crm/v1/system-status` REST endpoint.
- Admin assets loaded only on CRM screens; responsive, accessible admin UI.
- Internationalization for PHP and JavaScript.

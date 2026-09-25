=== WP LeadFlow CRM ===
Contributors: djouonanglandry
Tags: crm, leads, sales pipeline, contacts, customer management
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

CRM and lead management for WordPress: leads, companies, contacts, sales pipeline, activities, tasks, follow-ups, notes and customer communication.

== Description ==

WP LeadFlow CRM is a CRM platform built into WordPress. It lets businesses manage leads, companies, contacts, sales stages, activities, tasks, follow-ups, notes and customer communication without leaving their site.

Features:

* Analytics dashboard: KPIs, charts (stages, sources, monthly leads, won vs lost, pipeline value) and date filters
* Work widgets for pipeline, leads, tasks, follow-ups and recent activity
* Companies and contacts with notes, search, filters and bulk actions
* Leads with a configurable, drag-and-drop sales pipeline
* Tasks with due dates and overdue indicators, follow-ups with outcomes
* Automatic activity timeline on every record
* Email templates with placeholders such as {{first_name}} and {{company}}
* Send emails to leads, contacts and companies, with email history and a sending log
* CSV import (column mapping, duplicate detection, preview, error report) and CSV export
* Dedicated CRM roles: CRM Manager and Sales Agent, with an access control screen to fine-tune what each role may do
* Secure REST API for leads, companies, contacts, tasks and activities
* Data retention options, debug logging, system status and a complete JSON backup
* Multisite support, translation-ready, accessible admin screens

Coming next: lead capture forms and report exports.

= Privacy =

WP LeadFlow CRM stores CRM data in your own WordPress database. It does not send data to external services. Emails you send from the CRM are delivered through your site's own email system (or the SMTP service you configured), and a copy of each email is kept in your database.

= Sending email =

Emails use WordPress's `wp_mail()`. For reliable delivery, connect your site to an SMTP or transactional email service with a dedicated plugin; LeadFlow CRM uses it automatically. Use *Settings → Email → Send a test email* to check the setup.

== Installation ==

1. Upload the `wp-leadflow-crm` folder to `/wp-content/plugins/`.
2. Activate the plugin from the **Plugins** screen.
3. Open **LeadFlow CRM → Dashboard**.
4. Assign the *CRM Manager* or *Sales Agent* role to team members under **Users**.

== Frequently Asked Questions ==

= Is my data deleted when I deactivate the plugin? =

No. Deactivating never deletes data. Data is only removed when you delete the plugin **and** you enabled *Settings → Advanced → Delete all CRM data when the plugin is deleted*.

= Who can access the CRM? =

Administrators, CRM Managers and Sales Agents. Only Administrators and CRM Managers can change settings.

= Why was my email not sent? =

Your site could not hand the email to a mail server. The attempt is saved with the error in the record's Emails tab and in LeadFlow CRM → Emails, where you can try again. Install and configure an SMTP plugin, then send a test email from Settings → Email.

= Who can send emails? =

Administrators, CRM Managers and Sales Agents, to records they are allowed to edit. Sales Agents see only their own emails in the email log.

= Can I delete old data automatically? =

Yes. Under *Settings → Data* you can delete activities and sent emails older than a number of months, and empty the trash after a number of days. All three are off by default, and the clean-up runs daily in the background.

= Can other applications read my CRM data? =

Yes, through the REST API, using a WordPress application password. Each request is limited to the permissions of that user. See docs/API.md.

== Screenshots ==

1. Dashboard: ten KPIs, five charts and your own work, filtered by period and owner.
2. Sales pipeline: drag cards between stages, or move them from the keyboard.
3. Leads: search, filters, sorting, bulk actions and bulk assignment.
4. A lead record: stage bar, details, and tabs for activity, tasks, emails, follow-ups and notes.
5. A company record: details, its contacts and the shared timeline of the account.
6. Contacts with job title, company, country, status and owner.
7. Tasks with priority, due dates, overdue highlighting and one-click completion.
8. Sending an email from a record, with a template, placeholders and a live preview.
9. The email template editor with its placeholder palette and preview.
10. The email log: every message sent from the CRM, including delivery failures.
11. CSV import: upload, map columns, preview, import, summary.
12. Access control: every capability against every role, in one matrix.
13. Data retention, the daily clean-up and the export tools.
14. System status: versions, migrations, tables, environment and the debug log.

== Changelog ==

= 1.0.0 =
* First stable release.
* Data retention options with a daily clean-up, and a complete JSON backup.
* Debug logging with a viewer, and a downloadable system status report.
* Contextual help, clearer empty states and loading states.

= 0.9.0 =
* Access control: edit what each role may do, and manage who is in the CRM.
* Secure REST API for leads, companies, contacts, tasks and activities.

= 0.8.0 =
* Analytics dashboard with 10 KPIs, 5 charts and date / owner filters.
* Faster dashboard queries with new database indexes and caching.

= 0.7.0 =
* Send emails to leads, contacts and companies from templates, with live preview and signature.
* Email history on every record and an email log.
* Email settings (sender, reply-to, signature, hourly limit) and a test email.

= 0.6.0 =
* Email templates with placeholders and live preview.
* CSV import with column mapping, duplicate detection, preview and error report.
* CSV export of leads, contacts and companies.

= 0.5.0 =
* Tasks with due dates, priorities, assignees and overdue indicators.
* Activity timeline on leads, companies and contacts.
* Follow-ups with dates, outcomes and a "Next follow-up" column.

= 0.4.0 =
* Leads with a visual drag-and-drop sales pipeline.
* Configurable pipeline stages.
* Lead assignment, search and filters.

= 0.3.0 =
* Companies and contacts: list, add, edit and view screens with search, filters, sorting, pagination and bulk actions.
* Notes on companies and contacts.
* Sales Agents can edit only the records they own.

= 0.2.0 =
* CRM database: companies, contacts, leads, activities, notes, tasks and follow-ups with relationships, soft deletes and automatic upgrades.
* Data access layer with validated, prepared queries.

= 0.1.0 =
* Initial foundation: modular architecture, migrations, roles and capabilities, settings framework, dashboard, system status.

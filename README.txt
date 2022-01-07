=== Plugin Name ===
Contributors: Daniel Howard (Bellweather Agency)
Tags: SimpleView, integration, api, crm
Requires at least: 7.4.1
Tested up to: 7.4.1
Stable tag: 7.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to pull data from SimpleView CRM into wordpress.

== Description ==

This plugin allows you to pull data from SimpleView CRM into wordpress.

== Installation ==

1. Composer require bellweather/sv-api or upload plugin folder to the `/wp-content/plugins/` directory and activate.
2. Navigate to Plugins > SimpleView API > API Settings.
3. Fill in the following fields: `Listings API URL`, `Listings API Username`, `Listings API Password`, `Events API URL`, `Events API Key`.
4. Hit `Save Changes`.
5. Under the `Status` tab, click `Run Listings Import`.
6. Once that has completed, select `Run Events Import`.

== Frequently Asked Questions ==

= What do I do if there is a problem with a listing? =

Grab the SimpleView ID from the post, then delete that post. Navigate to Plugins > SimpleView API > Status.
Paste the ID above the button `Run Single Listing Import`, select `SimpleView ID, and run the import.

== Changelog ==

= 0.0.0 =
* establish stable plugin version

== Upgrade Notice ==

= 0.0.0 =
Init.

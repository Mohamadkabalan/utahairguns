=== Request Form for Zendesk ===
Contributors: pipdig
Donate link: https://wordpress.org/support/plugin/zendesk-request-form/reviews/#new-post
Tags: zendesk, zendesk integration, zendesk support, zendesk ticket, helpdesk, support, form, zendesk api
Requires at least: 4.9
Tested up to: 6.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add a Zendesk Support Form directly to your WordPress site.

== Description ==

Zendesk Support is a trademark of Zendesk, Inc.

Add a **Zendesk Support** form to your WordPress site. This plugin will embed the form directly into your page content, matching your theme's styling where possible. You can add a basic form very easily in minutes!

Want to create multiple forms with different fields? You can setup custom fields and options using the advanced features available.

**Features and Options:**

* Uses the Zendesk API to open a ticket directly within your account. No need to worry about missed emails!
* Add file attachments to tickets.
* Support for Zendesk custom [ticket fields](https://support.zendesk.com/hc/en-us/articles/203661506-About-ticket-fields).
* Create multiple forms with different ticket fields.
* Set the Priority level of tickets submitted by each form.
* Pre-fill form fields with user data when logged in to WordPress.
* Redirect user after form submission.
* Automatic spam protection (without captcha).
* Google reCAPTCHA spam protection.
* Double click protection - Stop people from submitting the form twice by accident.
* Google Analytics Events tracking on form submission.
* Fully [translatable](https://translate.wordpress.org/projects/wp-plugins/zendesk-request-form/dev) into any language.
* Automatically check email address for typos and suggest solutions via [mailcheck.js](https://github.com/mailcheck/mailcheck).
* Add User Agent and [CloudFlare Geolocation](https://support.cloudflare.com/hc/en-us/articles/200168236-What-does-CloudFlare-IP-Geolocation-do) data (if available) to the ticket.
* HTTPS or plain HTTP support (HTTPS recommended).
* HTML5 Pattern (Regular Expression) validation on fields.
* Data is validated/sanitized before sending to Zendesk.

We created this plugin for our own support system. So you can be sure that we will update and maintain it into the future. If you have any feature suggestions, you are welcome to ask in the [support forum](https://wordpress.org/support/plugin/zendesk-request-form).

**Supported Custom Field Types:**

* Text
* Textarea
* Number
* URL
* Email
* Password
* Checkbox
* Dropdown/Select
* Date/Datepicker
* Hidden
* Descriptive (Arbitrary HTML/Text)

If you have found this plugin useful, please consider [leaving a review](https://wordpress.org/support/plugin/zendesk-request-form/reviews/#new-post). Share some Zen :)

**Languages / Localization**

If you would like to translate the form into your language, please [click here](https://translate.wordpress.org/projects/wp-plugins/zendesk-request-form/dev).

== Installation ==

1. Go to 'Plugins > Add New' in your WordPress dashboard and search for "Zendesk Request Form". Install and activate the plugin.
2. Add your Zendesk API information to the options under 'Settings > Zendesk Form' in your WP dashboard. You can generate an API key from your Zendesk dashboard using [this guide](https://support.zendesk.com/hc/en-us/articles/226022787-Generating-a-new-API-token-).
3. Go to 'Settings > Zendesk Form' and configure your forms.

== Screenshots ==

1. Basic request form with no options.
2. Advanced request form using custom fields and options.
3. Settings page.

== Frequently Asked Questions ==

= What about privacy and GDPR? =

This plugin does not store any data submitted via the form. Your site connects directly to the Zendesk API to transmit the ticket data immediately. For information about privacy and GDPR, please see Zendesk's policy on [this page](https://www.zendesk.co.uk/company/customers-partners/eu-data-protection/).

= How do I get my Zendesk API key? =

You can generate an API key from your Zendesk dashboard using [this guide](https://support.zendesk.com/hc/en-us/articles/226022787-Generating-a-new-API-token-).

= How are messages sent to Zendesk? =

This plugin will connect directly to your Zendesk account via the Zendesk API. This means you do not need to worry about missed emails being sent. The data is transferred directly via the WordPress HTTP API.

= Can I translate the form into my language? =

This plugin is fully translatable into any language. If you find that there is some text that has not already been translated, you can add your language on [this page](https://translate.wordpress.org/projects/wp-plugins/zendesk-request-form/dev).

= How do I change the order of the form fields? =

You can set the position of each field using the [Order value](http://i.imgur.com/oyZ5BE4.png) whilst editing the custom field. Lower numbers appear first in the form.

You can also use a plugin such as [this](https://wordpress.org/plugins/intuitive-custom-post-order/) to re-order the fields. This is much easier since it allows you to drag and drop them into the correct order.

= Is this plugin free? =

Yep! This plugin was created for our own suppport site, so we will continue to add new features. If you have a suggestion, you are welcome to post it in the [support forum](https://wordpress.org/support/plugin/zendesk-request-form).

If you'd like to make a donation, the best thing you can do is [leave a 5 star rating](https://wordpress.org/support/plugin/zendesk-request-form/reviews/#new-post) :)

== Changelog ==

= 3.0.3 (24 Feb 2021) =
* Allow uppercase file types when attachments are enabled.

= 3.0.2 (11 Feb 2021) =
* Change API key input field type to "password" on settings page.

= 3.0.1 (11 Jan 2021) =
* Make sure WP_Query() is reset after gathering custom fields.

= 3.0.0 (21 December 2020) =
* Add Google reCAPTCHA v3 support. Find this in the new "Show Advanced Options" section of the [Settings page](https://i.imgur.com/sjOk1zM.png).
* Remove plugin settings on uninstall.

= 2.9.8 (13 April 2020) =
* Scroll to confirmation message on form submission.

= 2.9.7 (21 July 2019) =
* Ability to convert submit [button HTML type](https://wordpress.org/support/topic/submit-button-66/).

= 2.9.6 (06 June 2019) =
* Fix an accessibility issue (Thanks [@jgarciarlc](https://wordpress.org/support/topic/web-accessibility-issue/))

= 2.9.5 (08 April 2019) =
* Update information for Zendesk Inc trademarks.

= 2.9.4 (15 Aug 2018) =
* Increase timeout limit for file uploads to 10 seconds.
* Check for WP_Error on file upload request.

= 2.9.3 (27 Jun 2018) =
* Include WP_Error message in email when WP_Error occurs.

= 2.9.1 (20 Feb 2018) =
* Enhancement: Better validation of URL inputs. Force the user to add "http://" to the front if not already.
* Fix: Prevent duplicate form submissions from double clicks.

= 2.9.0 (11 Feb 2018) =
* New: Set the Description field as optional by adding description="optional" to the shortcode ([example](https://pastebin.com/raw/xRwa181s)).
* New: Option to split the "Your Name" field into two separate fields: "First name" and "Last name" ([example](https://pastebin.com/raw/6XJKUN7R)).
* New: Option to move Custom Fields below the main "Description" field ([example](https://pastebin.com/raw/RdTQ5mq0)).

= 2.8.0 (13 Jan 2018) =
* New: Option to [CC an email address](https://wordpress.org/support/topic/automatic-cc-to-another-address-when-creating-ticket/) when ticket is sent.
* New: Option to [redirect user on fail](https://i.imgur.com/Mktmw9h.png).
* Enhancement: Auto-capitalize first letter of name.

= 2.7.6 (17 Nov 2017) =
* Sanitize 'Custom Field ID' using `sanitize_text_field` rather than `absint`.
* Fix issue with redirecting form in WP 4.9.

= 2.7.4 (26 Oct 2017) =
* Option to open page in new window when submitting form. Add `new_window="yes"` to your shortcode to do so.
* Option to force user to attach a file using `attachments_required="yes"`

= 2.7.2 (11 Oct 2017) =
* Make attachment field text [translatable](https://translate.wordpress.org/projects/wp-plugins/zendesk-request-form/dev).

= 2.7.1 (10 Sept 2017) =
* Allow file attachments on forms. Check the shortcode generator options at 'Settings > Zendesk Form'.
* Option to override "Your Name" and "Your Email" labels. See [this post](https://wordpress.org/support/topic/want-to-change-name-and-email-field-labels/) for instructions.

= 2.6.2 (6 July 2017) =
* Option to set ticket Priority via shortcode.
* Forms will now work correctly even if wp-admin is blocked for the current user role.

= 2.6.1 (26 June 2017) =
* Ignore blank lines from "Dropdown" field items - Props [@jaworskimatt](https://wordpress.org/support/users/jaworskimatt/).
* Ability to set the default "Dropdown" option. [Read more](https://wordpress.org/support/topic/suggestions-for-the-dropdown-field/).
* Add CSS class to "Extra Info" span.
* Remove paranthesis from "Extra Info" text. Allow user to re-add if required.
* Remove `<br />` tags from field labels.

= 2.6.0 =
* New "Date" custom field type.

= 2.5.0 =
* Fix typo in javascript causing error with "descriptive" custom fields.
* New "textarea" custom field type.

= 2.4.2 =
* More stable redirection on form submimssion.

= 2.4.1 =
* Option to override default field labels ([more info](https://wordpress.org/support/topic/renaming-default-fields/)).
* Shortcode generator on settings page.
* Set input fields to 100% width for better styling.

= 2.3.3 =
* Fix: Missing subject field.
* Enhancement: If theme does not include form styling, make form 100% width of container.

= 2.3.2 =
* Enhancement: Option to remove User-Agent string from showing at the bottom of ticket description. Set shortcode parameter `useragent="no"` to disable.
* Minor refactoring to improve code readability.

= 2.3.1 =
* Enhancement: More efficient form action/loading time.
* Fix: Issue with Contact Form 7 plugin.

= 2.3.0 =
* New: Dropdown custom field support.
* New: [HTML5 pattern](http://www.w3schools.com/tags/att_input_pattern.asp) option for custom feilds.
* Enhancement: Prevent "double click" duplicate form submissions.
* Enhancement: Disable spam check if requester is logged in.
* Enhancement: Add [CloudFlare Geolocation](https://support.cloudflare.com/hc/en-us/articles/200168236-What-does-CloudFlare-IP-Geolocation-do) to User-Agent data.
* Enhancement: Extra spam protection.
* Enhancement: Update to match new [Zendesk branding](https://www.zendesk.com/blog/the-zendesk-rebrand/).

= 2.2.5 =
* New: Option to Pre-fill Name and Email via shortcode.
* New: Option to disable prefilling of field information from logged in user account.

= 2.2.2 =
* New: Checkbox custom field support.
* Enhancement: Set unique ID on field `<p>` tags for easier styling.

= 2.1.0 =
* New: Google Analytics Events tracking on form submit button.

= 2.0.0 =
* Custom ticket fields.
* Group specified fields into different instances of form/shortcode.
* Extra form customization options via shortcode attributes.
* Pre-fill form fields with user data for useres who are logged in to WordPress.
* Custom url to redirect user to once the form is submitted.
* Pre-fill custom fields via GET from url query.
* Add browser User-Agent string to ticket data.
* Declare WordPress 4.5 support.

= 1.0.2 =
* Improved error messages.

= 1.0.1 =
* Use WP http API instead of cURL.

= 0.9 =
Initial release.
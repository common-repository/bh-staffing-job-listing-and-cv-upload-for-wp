=== Matador Jobs Lite ===

- Contributors: jeremyescott, pbearne
- Donate Link: https://matadorjobs.com
- Tags: Bullhorn, job board, matador, google jobs search, career portal, OSCP
- Requires at least: 4.7
- Tested up to: 5.0.2
- Stable tag: 3.4.1
- Requires PHP: 5.4
- License: GPLv3 or later
- License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connect your WordPress site with your Bullhorn account. Cache job data locally and display it with style inside your
WordPress theme.

== Description ==

Connect your Bullhorn Account with your WordPress site and display your valuable jobs on your new self-hosted job board.
Matador makes this as easy as it sounds, and lets you seamlessly integrate a powerful job board--a major marketing tool
for your business--directly into your WordPress site. Everything that is great about WordPress is extended to Matador:
great out-of-the-box SEO, easy templating/theming, endless customization options, and more. Matador goes further by
listing your jobs with incredible job-specific SEO customization (optimized for Google Jobs Search), and more.

Use Matador's powerful settings to connect our "Apply Now" button for jobs to a page that will collect applications, or
look into purchasing Matador Jobs Pro to accept applications from Matador and see them turned into candidates submitted
to jobs directly in your Bullhorn Account!

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the '/wp-content/plugins/matador-jobs' directory, or install the plugin through the
   WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Configure the plugin by going to Matador Jobs > Settings.
1. Connect your site to Bullhorn by clicking on 'Bullhorn Connection Assistant' on the Settings Page, and following the.
   prompts.

== Frequently Asked Questions ==

= Does this Require a Bullhorn Account? =

You must have an active Bullhorn Account to use Matador's Bullhorn Import/Export features. It technically will function
as a stand-alone jobs board without a Bullhorn Account, but there are better options out there for that.

= How Do I Get Bullhorn API Credentials? =

You must submit a ticket to Bullhorn support. Merely informing them you will be using Matador should give them all the
info they need to help you, as we are now Bullhorn Marketplace Developer Partners and they know what a new Matador user
needs. That said, we recommend first installing the plugin, activating it, and starting the Bullhorn Connection
Assistant before you do this. Follow the prompts in the easy-to-use assistant and Matador will generate a copy-and-paste
email you can send to Bullhorn Support to get you started.

= So Matador downloads jobs from Bullhorn. Does it accept applications too? =

Yes, if your a user of Matador Jobs Pro or All-Access. Once you've connected to Bullhorn and synced your first jobs,
your visitors can apply to the jobs. Based on settings, the applications will be sent to your Bullhorn either
immediately or in the next regularly scheduled sync with Bullhorn.

If you are only right now a user of the free Matador Jobs Lite, not yet. Matador Jobs lite allows you to designate a
destination page for the "Apply" button, but you will need to handle your own applications, perhaps with a contact form
plugin.

If you'd like more information on Matador Jobs Pro or All-Access, visit <https://matadorjobs.com/>.

= How Can I Customize the Look of Matador? =

Our documentation site <https://matadorjobs.com/support/documentation/> explains how to use our template system, theme
functions, shortcodes, and actions and filters to make your site look amazing. You can also watch out for occasional
client showcases on our where we feature creative and amazing looking implementations of Matador.

= How Can I Customize the Function of Matador? =

Matador is built by WordPress users for WordPress users. We included hundreds of Actions and Filters to help you
customize how it works just like WordPress core. Some of those are documented at
<https://matadorjobs.com/support/documentation/> while others can be discovered with a quick code review.

But that requires a developer and hours of work! If you haven't already, check out our many official extensions that are
viewable at <https://matadorjobs.com/products/>. These extend Matador's core functionality in ways that can make each
site feel unique! You can use an unlimited number of these All-Access Add-Ons with any Matador Jobs All-Access plan.

If you need something and you don't see an add-on, feel free to write us. Leave a comment in the Support Forum or with
our Pro support system <http://www.matadorjobs.com/support/> (requires Matador Jobs Pro or All-Access). Simple
modifications might already be documented and we can point you to them. And if you have a more complex modification, we
may be able to take your input and idea and turn it into another All-Access Add-On.

= Where can I get support? =

Users of Matador Jobs Lite should use the plugin's WordPress.org support forum. Users of Matador Jobs Pro and All-Access
annual or lifetime plans can use our support ticket system at <http://www.matadorjobs.com/support/>.

== Upgrade Notice ==

Upgrading from 'Bullhorn 2 WordPress' 2.4 or 2.5 to Matador Jobs Lite/Pro 3.0 or later includes some breaking updates.
This will cause some sites to disconnect or look differently. Back up your site and perform the upgrade on a staging
server if possible. We have made every effort to make this smooth, but be warned, it will require extra work to make
your site function the same again.

== Screenshots ==

1. Options page - Bullhorn Import settings
2. CV/resume upload form
3. Notifications options page
4. Jobs listings in the admin

== Changelog ==

= 3.4.1 =

This release is a maintenance release to support final features included in WordPress 5.0, 5.0.1 and 5.0.2, as well as fixes bugs.

- Adjusted a few calls where wp_kses_post() was used when it wasn't necessary. Backward incompatible changes to WordPress 5.0.1 required this.
- Added filters matador_application_note_line_label and matador_application_note_line_item to modify how "note" lines are saved in the application.
- Moved JSON_LD injection from above the Job markup and into the <head>. May also have fixed some JSON LD reading/saving issues with Google.
- Added setting to allow user to define a page (ideally using shortcodes) as the jobs home page. Note: you cannot set the "Home" page as the jobs home page at this time without a possible issue around search results.
- Added tools to support handling issues some of our users were experiencing with enabling Google Indexing API.
- Update "tested up to" to 5.0.2

= 3.4.0 =

Big New Features:

Matador Jobs 3.4.0 is the biggest update to Matador yet. Its patch notes will be huge and confusing and incomplete. For this reason, please follow our blog and our help files to learn how to use the updated Matador! Here are the big deal updates:

- We now support the Google Indexing API. This matters, because it allows your site to directly notify Google whenever a new job is added or a job is removed. This is a partial support for this feature, as we'd like to soon support updates to jobs in this notification as well. To turn on Google Indexing for your jobs, you will need to get a Google Indexing API keyfile. Look in Settings -> Job Listings (tab) -> Jobs Structured Data (section) for the new settings, and a link to our help website on how to get this.
- Applications now can save the IP address of the applicant. Further, the IP as well as the date/time of the submission, can now be submitted to the candidate's record in Bullhorn as part of the candidate save. This is useful to track the most recent acceptance of terms of service and privacy policies, for example, as well as provide data points for internal analytics. See the more detailed patch notes on how to use this.
- Jobs now have a default "information" header, aka the "meta header" after the title and before the description. This will present (by default) the Job's location, type of job (ie: contract, permanent), and the Bullhorn Job ID. This is easy to customize with a little code, but styles are only applied for default setting. Existing users will need to enable this feature by turning on a setting in their settings.
- Jobs also now have a default set of navigation buttons at their end, after description. These are contextual, so will change based on your settings and depending on which page you're on. These are added to all jobs by default, so check your site to see you like the styles.
- A majority of the templates, template actions and hooks, and more have been simplified. Actions and hooks now have a second parameter called "context", which can be used to limit their scope. Where in the past, for example, the "jobs listing" template had two actions for 'before_jobs', 'matador_before_jobs' and 'matador_before_jobs_listing',now it just has one 'matador_before_jobs' with the passed 'context' of 'listing'. The action is the same for all templates, but it can be limited using the 'context' parameter. This will make customizing templates easier, but may cause issue with current custom templates and actions.
- Many, many new template functions were added to the global namespace for theme developers to access, making the customization much easier!
- New shortcut shortcodes `[matador_jobs_listing]`, `[matador_jobs_table]`, and `[matador_jobs_list]` to make it simpler to use them and provide more intuitive default settings.

Additional Features/Enhancements:

- When redirected to a job page after a successful application where a confirmation is shown (as opposed to a Thank You page), the application form will no longer show, which helps avoid confusion for the user.
- Applications now save the IP address of the computer that submitted the form.
- The IP address of the computer that submitted the form can now be submitted to the corresponding new or existing candidate record following a Candidate Submit routine by Matador. IP addresses are saved to both a "on submit" and "on update" field, and to tell Matador which custom fields on the Candidate record to use, assign them with the matador_submit_candidate_ip_field_on_create and matador_submit_candidate_ip_field_on_update filters, introduced in this version. While we recommend you use these filters to prevent any issues caused by changing these values accidentally, a free Extension was created to make an admin setting for this also if you are unable or uncomfortable writing a filter.
- If a site has the "Require Applicant to Agree to Privacy Policy" setting turned on, when Matador sends the application to Bullhorn, you can now designate a text or date custom field to save the date and time of that submission in order to track the initial and most recent acceptance of your Privacy Policy to their candidate record. Assign which fields to use with the matador_submit_candidate_privacy_policy_field_on_create and matador_submit_candidate_privacy_policy_field_on_update filters, introduced in this version. While we recommend you use these filters to prevent any issues caused by changing these values accidentally, a free Extension was created to make an admin setting for this also if you are unable or uncomfortable writing a filter. You may additionally customize the date format by using the matador_submit_candidate_privacy_policy_field_format, but this only applies when you are using a custom text, and not a custom date, type field.
- Form validation error messages can now be customized by a developer new matador_application_validation_error_{$error} filter. See `includes/class-scripts.php` for a list of errors that can be filtered.
- Option to modify the URL slug format of each job. Options are limited to three possibilities, but a filter was added to provide users methods to make more complex URL slugs. Paired with an additional field import, a user can even set the URL slug to be generated from a job custom field, among other things.
- Previously, jobs without an end date in Bullhorn would result the in the 'validThrough' field in the structured data--what Google for Jobs uses--to be blank. We found that this causes Google for Jobs to make that job a lower priority. Matador will now set a date one year from the 'job posted' for the 'validThrough' field. Note that if your firm offers ongoing positions, it is important you set an impossibly long job end date in Bullhorn to make sure your job always has a 'validThrough' date in the future.
- Added $data parameter to the 'matador_data_source_description' filter. This allows developers to access the object and any custom fields to use to modify the source. This object is different in each context, so filter functions should check for context prior to assuming object structure.
- Adds new field to job, called `job_general_location` which is customizable with the new `matador_import_job_general_location` filter. This is the field used by the default meta header.
- When an Application is deleted, either manually or automatically, prior to its delete, associated files (resumes, etc) are now also deleted. This should free up storage space on your server and close a possible privacy or security risk should server settings allow those files be available to the internet.
- Adds empty index.php files in Matador uploads folders to provide security for files when a server has Apache directory indexes turned on.
- Adds new actions 'matador_add_job', 'matador_update_job', 'matador_save_job', 'matador_transition_job', and 'matador_delete_job' to give developers easier access to changes in job posts to hook into.

Bugfixes:

- Fixed an issue where an appended Application Confirmation message would not show properly after a job application.
- Fixed an issue where pagination was not presented for shortcode-based jobs lists.
- Fixed an issue where an error was printed to screen, instead of logged to the logs, during a rare login-related issue with Bullhorn.
- Disabled "toggle" type admin settings will now show styles that communicate its disabled status.
- The deprecation notices were not showing properly for logged in users. They now will. Pay attention to them! Update you site accordingly.

Localization:

- Extended Localization options to all form validation error messages.

Deprecation:

- The pre-3.0.0 deprecated shortcodes will be removed from Matador in our next major version in January to March 2019. Please make sure you've fully migrated to the [matador_*] shortcodes.

= 3.3.7 =

Features:

- Added a bulk Application Sync button to the applications view. Will re-try all failed applications from the issue
  addressed in 3.3.6 and any failed applications for the last two weeks.
- Added a filter matador_application_batch_sync_allowed_statuses to allow you to extend the statuses included in a batch
  sync. This may come in handy in the future when we expand the statuses assigned to applications that fail.
- Added a filter matador_application_batch_sync_duration to allow you to extend of duration applied in a batch sync. If,
  for example, you want to apply a batch sync to jobs older than two weeks, you can do this via this filter.

Bugfixes:

- Fixed spelling/grammar error in the application processing overlay.
- Added escaping functions to prevent errors encountered during an application sync when Matador checks for existing
  candidates. Candidates with names that included a single quotation mark/apostrophe caused the search to be badly
  formatted and thus present an error, which in turn caused the application sync to fail altogether.

= 3.3.6 =

Features:

- Enabled sync re-try for applications with the status of "Unable to sync".
- Updated messaging around reasons that an application sync may fail.

Bugfixes:

- Updated the way a Bullhorn request was being made that was causing it to fail after the September 2018 Bullhorn ATS
  software upgrades.
- We are observing an issue on some Bullhorn users' accounts where a Bullhorn resume parse may return a badly structured
  candidate object that later results in a failure when Matador tries to create a candidate with that object. We have
  included a temporary work-around until we can help our partners at Bullhorn resolve this issue.
- Fixed an issue where a "Re-try Sync" routine on an application would result in an HTTP 430 error under certain caching
  circumstances.

Localization:

- Updated included Dutch (Netherlands) localization files.

= 3.3.5 =

Features:

- Added filters matador_import_job_description and matador_import_job_title to potentially override, append, or prepend
  a job title as it is imported from Bullhorn.
- Added filters matador_import_job_description_field and matador_import_job_title_field to use a non-standard field for
  job title or description. Standard fields are "title" and "description" or "public description".
- Updated the HTML tag filter on import so that job descriptions with images and videos will now import properly.

Bugfixes:

- Various changes were made around user-initiated syncs that fixed a few issues. Now, only one sync can occur at a time,
  which prevents a rare problem caused by two syncs running concurrently and creating duplicate jobs that Matador is not
  able to automatically expire. Also, now, each sync is performed fully in the background, which both allows an admin to
  continue other work on their site while the sync processes and also makes it so they're never presented with a browser
  timeout. Various notifications were added to explain these features so admins understand what is going on.
- Various display issues were fixed in the Application CSS to provide a better, more reliable base user experience.
- Fixed an issue that caused links to application pages, when linking to a custom page, to not work as intended (most
  often used in Lite installations).

UI:

- Added a toggle-type on/off switch to the UI for settings. Changed several settings where it was appropriate to this
  new style with the hope that it will make the experience better for users.

= 3.3.4 =

Bugfixes:

- Fixed an issue where the an anti-spam honeypot related function was named a protected function name causing issues in
  some versions of PHP, resulting in a failure when it should be valid.
- Improved Matador's error handling for invalid candidate object submissions to Bullhorn. At this time, there appears to
  be an error with Bullhorn's resume processor returning invalid candidate objects, which caused Matador to log an
  error. Unfortunately, Matador determined this was a recoverable error, which meant that it retried a failing process
  on each sync, a waste of system resources.
- Fixed a minor issue where the site name wasn't being accessed properly in Application email builder, which was used to
  generate Email Subjects when applicants applied in a form with no job assigned.
- Fixed an issue where a data attribute on the Apply button was being filtered by security features.
- Fixed an issue where an undefined privacy policy in WP 4.9.6 would result in a link being generated that had no url.
  Now the link isn't included if a privacy policy page is not set on WP 4.9.6+.
- Fixed an issue where rewrite rules were not being refreshed on upgrade/install of Matador Jobs.
- Fixed a minor issue where a hidden field in the application had two fields with the same ID attribute, which is
  improper HTML syntax, which may have caused some custom JS to function incorrectly.
- Fixed an issue where the the privacy_policy_opt_in was being appended to the "message" of the applicant confirmation
  email and in the "notes" of the Bullhorn Candidate record.

Developer:

- Filter 'matador_recruiter_email_header', introduced in 3.0.0, was deprecated and replaced with
  'matador_application_confirmation_recruiter_from' to best match the new naming conventions.
- Filter 'matador_recruiter_email_recipients', introduced in 3.0.0, was deprecated and replaced with
  'matador_application_confirmation_recruiter_recipients', to best match the new naming conventions and now accepts
  additional variable, $local_post_data.
- Filters 'matador_recruiter_email_subject' and 'matador_recruiter_email_subject_no_title', introduced in 3.0.0, were
  deprecated and replaced with 'matador_application_confirmation_recruiter_subject', to best match the new naming
  conventions and now accepts three inputs, $subject, $local_post_data, & $job_title.
- Filter 'matador_applicant_email_header', introduced in 3.0.0, was deprecated and replaced with
  'matador_application_confirmation_candidate_from' to best match the new naming conventions.
- Filter 'matador_applicant_email_recipients', introduced in 3.0.0, was deprecated and replaced with
  'matador_application_confirmation_candidate_recipients' to best match the new naming conventions and now accepts
  additional variable, $local_post_data.
- Filters 'matador_applicant_email_subject' and 'matador_applicant_email_subject_no_title', introduced in 3.0.0, were
  deprecated and replaced with 'matador_application_confirmation_candidate_subject', to best match the new naming
  conventions and now accepts three inputs, $subject, $local_post_data, & $job_title.

= 3.3.3 =

Bugfixes:

- Fixed an issue where the 'Sync Now' button on the Admin Job Listings Page didn't show if there were no jobs in the
  database, which is when we want and need that button the most.
- Fixed an issue where [matador_search] shortcode and matador_search() functions that included taxonomy fields were not
  working because the all option was passing a value of '_all' when the args were not set to ignore them.

Developer:

- Settings Sanitizer 'number_list' extracted from the Import by Client Extension and added to core.

= 3.3.2 =

Bugfixes:

- Fixed an issue where [matador_taxonomy] shortcode (and its shortcuts) or matador_taxonomy() function with the
  parameter 'method' is set to 'list' (which is the default) had poorly generated links that did not work.
- Fixed an issue where multiple versions of Matador, ie: Lite & Pro, would conflict with each other when both active in
  the same WordPress instance. Now, all versions can be activated, but whichever loads first in WordPress will be loaded
  until unneeded versions are deactivated.

= 3.3.1 =

Bugfixes:

- Fixed an issue where a site sending application notifications without having "AssignedUsers" or "Owners" checked could
  have applicants presented with an error.

= 3.3.0 =

Features:

- Added an anti-spam behavior to the Application form. You may need to update your settings by visiting Matador Jobs >
  Settings in your WordPress Admin, clicking on the Applications tab, scrolling down to "Use Anti-Spam Honey Pot", and
  setting it to "On".
- Added two features to prevent duplicate Application forms submissions.
  - First, applications will now have their submit button disabled after a user clicks the button and the client-side
    validation passes, allowing only one submission per click.
  - An overlay with a loading "spinner" will also be added over the form to give a strong visual indication to the user
    the form is processing. This may be styled with CSS.
- Added a 'hide_empty' and 'orderby' parameter for the [matador_taxonomy] shortcode and matador_taxonomy() function. Now
  you can choose to list categories, locations, etc even if they don't have jobs and you can choose how to order them,
  like, our favorite, by number of jobs in the taxonomy. This option now works on the alias versions of shortcode too:
  [matador_categories], [matador_locations], and [matador_types].
- Added a new allowed value for the [matador_search] shortcode and matador_search() function parameter 'fields'. It now
  accepts 'reset', and when passed, will add a Reset Search button to the output.

Bugfixes:

- Fixed an issue where the "show_all_option" parameter of the [matador_taxonomies] and matador_taxonomies() function was
  not always resulting in an "All Categories" like link added to the list.
- Fixed an issue where translatable strings were used in dynamic filter names and filter arguments in class
  Job_Taxonomies, which may have caused some sites using non-US English difficulty in customizing output.
- Fixed issue where templates moved/renamed in 3.2.0 were not given proper backward compatibility handling concerns that
  should've been in place before 3.2.0 was released. Apologies to all affected sites, and thanks to Andre, who reported
  the bug.
- Fixed an issue where if a site chose to leave the "Additional Emails to Notify" option blank for Application
  Notifications, the site Admin Email would be included. In the past, this behavior was default and correct, but not
  since 3.2.0's recruiter-based email notifications feature was released. Thanks to Jason and Rich for reporting the
  bug.
- Added a class .matador-screen-reader-text in CSS and applied it to all places where we add previously used the global
  class .screen-reader-text. This wasn't a per se 'bug', but it caused a lot of issues for sites whose themes did not
  implement the WordPress recommended class in the theme, resulting in sites that had extra text in awkward places and
  confused users trying to figure out how to remove it. That said, your theme should implement and use classes like
  .screen-reader-text to help make your sites more welcoming to users who are blind or hard of hearing, just from now
  on, we'll assume you don't.

UI:

- Matador developers are developers, so sometimes we don't explain things very well. We got feedback that the new
  settings around structured data in 3.1.0 were confusing and didn't make sense. We changed the order of the settings,
  revised or rewrote their descriptions, and hopefully made everything much easier to understand.

Accessibility:

- Added Screen Reader content to the [matador_search] and [matador_taxonomy] shortcodes and the matador_search() and
  matador_taxonomy() functions to improve readability.

Developer:

- Added a filter named 'matador_rewrites_taxonomy_has_front', which when returned false will disable the inclusion of
  the jobs slug before the taxonomy slug.
- Added filters named 'matador_rewrites_taxonomy' and 'matador_rewrites_taxonomy_$key', which allows modification of the
  'rewrites' array in the taxonomy declaration. Replaces deprecated filter 'matador_taxonomies_rewrites_$key'.
- Added a filter named 'matador_taxonomy_labels', which allows for manipulation of the 'labels' array in a taxonomy
  declaration. Does not replace 'matador_taxonomy_labels_$key'.
- Added a filter named 'matador_taxonomy_args', which allows modification of the whole $args array in a taxonomy
  declaration. Does not replace 'matador_taxonomy_args_$key'.
- Filter 'matador_bullhorn_source_description', introduced in 3.2.1, was renamed to 'matador_data_source_description'
  and is now applied to three variables, up from the original 1. A 2nd argument for the filter, which is optional,
  was clarified in documentation as the 'entity'. Warning: There is no deprecation handling for this change.
- Added an action and filter that run before the Application Handler begins processing raw data. The action,
  'matador_application_handler_start' fires immediately after nonce is verified and can be used to do further before
  processing verifications, like check a captcha or an anti-spam honeypot. The new filter,
  'matador_application_handler_start_ignored_fields' lets developers add fields that might have been added to the form
  for processing in the the aforementioned action be added to the ignored fields list, which ensures the processor's
  catch-all at the end of the form processor doesn't pick them up and include them in the job submission as a 'note'.
- Added a filter named 'matador_get_template_print_name_comment', which, when passed a true value, prints an HTML
  comment before the output of the template with the template's path. This will help developers determine which template
  is being loaded by Matador for easier template overrides.
- Added a filter named 'matador_locate_template_additional_directories'. This allows extension developers to add their
  template directories to be checked by Matador's template loader. The template loader checks these directories after it
  failed to find a template in core, so this won't override a core template.
- Added a filter named 'matador_locate_template_replace_default'. This allows extension developers to replace a core
  template with their own without taking away the important ability to override a template in the user's theme.
- Rewrote and simplified actions, filters, and the templates for the [matador_taxonomy] shortcode and matador_taxonomy()
  function. Templates were broken into parts for easier use and customization. Some actions/filters were deprecated in
  favor of new, more robust options. Deprecation handling was added for sites that implemented these old actions and
  filters.
- Rewrote and simplified actions, filters, and the templates for the [matador_search] shortcode and matador_search()
  function. Template was broken into parts for easier use and customization. Actions/filters were added to allow for
  easier customization.

Misc:

- Based on feedback acquired from a Bullhorn support ticket, modified the text of the Bullhorn Support email generator
  in the connection assistant "Callback URI" step to now include the ClientID. This is in case a Bullhorn account has
  more than one set of API credentials.

= 3.2.1 =

Features:

- Modified how "Notes" items are labeled when saved from an Application. Now should use the registered field's label
  instead of a label generated from the form field key.

Bugfixes:

- Fixed an issue discovered that caused "Messages" and custom fields to not be saved to the Bullhorn "Notes" for the
  candidate.

= 3.2.0 =

Features:

- New Template Helper matador_get_job_terms for getting a array of terms from 1 or all Matador taxonomies.
- New Template Helper matador_get_job_terms_list for getting a formatted string of terms from a job's taxonomy in
  various formats. Uses a new template 'job-terms-list.php' and introduces 6 new actions (matador_job_terms_list_before,
  matador_job_terms_list_before_terms, matador_job_terms_list_before_each, matador_job_terms_list_after_each,
  matador_job_terms_list_after_terms, and matador_job_terms_list_after) plus one new filter
  (matador_job_terms_list_separator ) to support customization.
- Added filter 'matador_bullhorn_source_description' to allow users to customize how Matador lists the "Source" of the
  job submission.
- Added filter 'matador_recruiter_email_subject_no_title' to allow for adjusting of subject sent to recruiter if not
  linked to a job.
- Improved the behavior of matador_is_current_term() to also return true when on a taxonomy archive page.
- New Template file for Admin Notifications when Bullhorn Connection needs user intervention.

Bugfixes:

- Fixed a major issue where certain candidate and recruiter notifications were not being sent when they should've.
- Added code to prevent conflicts when a vendor library is loaded by another plugin or theme after Matador loads. This
  changes makes existing conflict prevention methods more robust and fool-proof.
- Fixed bug where, when called directly, matador_the_job_field could throw an error if certain optional arguments were
  not passed.
- Fixed a bug where, in the default jobs-taxonomies-lists.php template, a class for the term markup was being made with
  the title and not slug, creating invalid HTML and not useful classes.
- Fixed a bug where, in Settings, a checkbox type settings wouldn't save when all checkboxes are unchecked.
- Fixed a bug where, when both a Candidate and Recruiter email were being sent the Recruiter email listed the job twice.

UI:

- Added the Bullhorn Bull icon to the "Manual Sync" button in settings, providing a visual cue to users that this action
  communicates with Bullhorn.
- Removed check marks next to checkboxes. Because that didn't make any sense.
- Revised wording around the "Classify Applicants" setting and added a long description explaining its impact on a
  Candidate and Job Submission, hoping to add further clarity to how the setting affects the workflow of various users'
  firms.

Misc:

- Added a (hopefully temporary) advisory to the Authorize step of the Connection Assistant to warn users of a known
  issue when logging in for the first time. In the future, we hope to resolve this issue with our Bullhorn partners.
- Moved templates for emails from /templates/ into /templates/emails. Renamed the templates. Some clients with
  customized email templates may want to double check if this impacted them, but given these templates were only
  available for all for less than two weeks, we feel safe making this adjustment now.
- Refined error logging messages during certain Bullhorn login attempts.
- Fixed spelling error on an Application Form Field
- Fixed spelling/grammar errors & formatting in Readme

= 3.1.0 =

Notice:

- This update will require a manual install. All Plus and Pro subscriptions and Pro Lifetime purchasers will receive a
  free manual upgrade by either Paul or Jeremy. The reason for this is that our plugin folder name will change to
  reflect a change-of-name for Matador Jobs Lite on WordPress.org. The folder name for "Lite" will now be 'matador-jobs'
  and the folder name for Matador Jobs Pro will be 'matador-jobs-pro'.

Features:

- Added new settings to support GDPR compliance, specifically the ability to require an acknowledgement of a Privacy
  Statement upon application, and the ability for admins to force the erasure of local candidate data. We will defer to
  Bullhorn's GDPR compliance tools to assist with final export of user data and erasure/anonymization.
- Jobs import now includes the email address of the "Job Owner" and "Assigned Users", and site admins can designate
  email notifications be sent to both the owner and/or the assigned users.
- Added numerous improvements and features to the Bullhorn API Assistant Wizard, including: a Client ID validator, a
  better Redirect URI validator, a fix to an issue caused by site transfers (like from staging to live) where the site
  wouldn't check for a new whitelisted Redirect URI until a cache expired up to 24 hours later, a check that determines
  if "Pretty Permalinks" were set, which is required for Authorization and caused some users confusion on fresh
  installs, a "skip assistant" option for advanced users which skips the Wizard and goes straight to the summary, and
  more.
- Added more interactive buttons in many UIs. A button now activates sync from the jobs listing page, a button now links
  to the Bullhorn job (for logged in users) from the job page, and a button links to the Bullhorn candidate (for logged
  in users) from the applicant page after the applicant is synced (and provided they are not deleted immediately).
- Added options surrounding structured data. Specifically, allows a site operator to choose whether to show base pay or
  not, and whether to use company data from Bullhorn or website data (name/url) for the Hiring Organization. The site
  operator may also now disable structured data, for instance in the case they want to run a low-profile board for
  internal uses.
- Added an admin notice when Matador cannot write its uploads folders (which prevents saving of uploaded files from
  applicants and data logging).
- Various performance improvements, including a rewrite of our settings page that speeds up Matador-related page loads
  by up to 3x faster.
- Added filter to allow for better messaging when an application sync status is null (possible in outside integrations
  like WPJM)
- Added filter 'matador_bullhorn_doing_jobs_sync' to allow for safe targeting of local and automatic post saves.
- Added action 'matador_bullhorn_import_save_new_job' to allow for actions based only on initial import of job.
- Added actions 'matador_bullhorn_before_import' and 'matador_bullhorn_after_import' to trigger behaviors around the
  import function.
- Various security hardening improvements.

Bug Fixes:

- Fixed a stubborn bug that was introduced in 3.0.3 that prevented automatic updates of Matador Jobs Pro.
- Improvements to Application Sync to prevent "Re-try" messages.
- Adjusted structured data (JSON+LD) to better match the spec.
- Fixed issue introduced in 3.0.4 where structured data (JSON+LD) saved during local changes (ie: in WordPress) was
  causing an error during remote change routines.

UI:

- A 're-check' button on Redirect URI step in Connection Assistant to reload the page and re-run the check, where
  formally users needed to reload the page.

= 3.0.5 =

Features:

- Ability to disable SSL Verify for sites with self-signed SSL.
- Added filter to modify the "WHERE" clause in Bullhorn Job Queries
- Better Errors and Logging around auto reconnect.
- Better Logging Messages around Application Sync.
- Enhancement: Resume fields, when included in the shortcode or via the default form defined in the settings will now be
  a required field with client-side validation.
- All application form fields defined in the 'matador_application_fields_structure' filter given an attribute of
  'required' will be required by client-side validation.
- Refactored the Jobs Output function into two parts to allow for alternate processing possibilities.
- Matador now processes Resumes into HTML to support new Bullhorn 'NOVO' UI

Bugfixes:

- Fixed incorrectly named actions in default Matador Taxonomies template.
- Fixed an issue for users that upgraded from a 2.x.x version of the lite plugin regarding ClientCorporation IDs.

= 3.0.4 =

UI:

- Fixed text color of status labels in connection assistant

= 3.0.3 =

Features:

- Added more allowed html tags from the job description import, including: <h1> through <h6>, and the list item tags.
- Added basic sorting functionality to the job listings, including new settings options, extendable via filters.

Bugfixes:

- Fixed an issue where the "Published - Submitted" option in the import settings wasn't working
- Fixed a persistent issue related to a 3.0.2 bugfix for template helpers related to job meta.

Internationalization:

- A partial translation of Matador to Netherlands Dutch was added to the languages directory.

= 3.0.2 =

Features:

- Added new filters into taxonomies-list
- Added argument to filter call in taxonomy labels
- Added an explicit timeout to API requests

Bugfixes:

- Matador Application form's <form> element had an empty class attribute
- Shortcodes and Template Helpers for job meta fields were not working as intended
- Fixed an issue where custom additional taxonomies formatted incorrectly caused the load to fail completely.
- Fixed issue causing some sites to fail upgrade from 2.X versions of Matador Jobs Lite
- Fixed issue where, when using the main query's template, matador search terms would not be applied to search results.
- Fixed issue where case sensitive field names in the [matador_application] shortcode caused fields to not display.
  Allow the fields attribute to be more forgiving in general to formatting.
- Fixed issue where the "show_all_option" for the taxonomy shortcode wouldn't properly work before a list.

UI:

- Improved the text label for "No Taxonomy Entries Found"

= 3.0.1 =

Features:

- Add a feature to prevent loading of deprecated shortcodes if main shortcodes are not loaded.
- Revised load order of the plugin for better extension development.

Bugfixes:

- Users exiting the connection assistant from the "Prepare" step initial screen will no longer be advanced to the next
  step upon return.
- Fixed issue where Matador Jobs Lite/Just Jobs would fail when looking for the premium versions' updater class.
- Fixed issue where All-Access and Pro extensions were not properly getting their update information.
- Fixed issue where Bullhorn Import failed if a standard taxonomy is unset by a developer (is in WPJM extension).

UI:

- Improved text prompts in Connection Assistant.

= 3.0.0 =

- While '3.0.0' in name, this update is a completely new version that rewrote the entire plugin. See matadorjobs.com
  for a full list of features. This will be a breaking change for users of the former WordPress.org plugin.

= 2.5.0 =

- Added warning for users of plugin, informing them of the 3.0+ plus release being available now for download on
  matadorjobs.com and warning that the release is breaking change and to not allow updates. Our intent is to leave this
  warning in place for a minimum of 60 days.

= 2.4.0 =
Added retries to the CV upload to re-attempt CV parsing failures (a known Bullhorn issue - "Convert failed, resume mirror status: -1 - Rex has not been initialized").
Increased the timeout for CV parsing to 2 mins
Added error email option
Added lots of extra checks for bad data back to Bullhorn API
Enabled a copy of the CV to be inserted into the description as HTML.
Numerous other bug-fixes and extra filters.

= 2.3.0 =
Added option to disable auto syncing.
Added option to not filter the jobs by isPublic location setting.
Other small fixes.
More short code support.

= 2.2.2 =
Some fixes for PHP errors.
Added more content to job post meta.
Added option select which field to show in CV form.
More short code support.

= 2.2.0 =
Fixed an error in the options name that was breaking the CV upload redirect to the thank you page.
Fixed calls to non static functions.
Added shortcodes for "b2wp_resume_form", "b2wp_application", "b2wp_shortapp" for compatibility.

= 2.1.4 =
Fixed typo.

= 2.1.2 =
Improved the messages on the settings page.
Protected the country code.

= 2.1.1 =
Fixed a bug when syncing.

= 2.1 =
Fixed the country fetch.
Handle running the plugin without being linked to bullhorn.

= 2.0 =
Merged CV upload.
Removed 3rd party file upload code and replaced with native WordPress version.
Added support to CV upload to parse skill list.
Added support to CV upload to link to a joblisting.
Added translations to all strings I could find.
Added filter to allow settings override.
Added option to select to use the CV upload form or just link to a page with an application form.
Added Microdata to the job detail pages to help with SEO ranking.
Lots of code tidy and refactoring to get the code close the WordPress Standards.
Added support for multiple URL's to one Bullhorn Account.

= 1.x.x =
As forked
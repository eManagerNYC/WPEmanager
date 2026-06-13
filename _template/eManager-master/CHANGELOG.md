# Changelog


## 3.7.1 - 2016-12-21
- Cleaned up a lot of areas. Added plugins to repo. Fixed bug in responsible person drops.

## 3.7.0 - 2016-11-07
- Small overhaul to the dcrs, some updates to letters

## 3.6.16 - 2016-07-30
- Updates to DCR Reports section

## 3.6.15 - 2015-12-23
- Fixed dashboard flip for iOs and IE

## 3.6.14 - 2015-12-23
- A lot of cleanup to get ready for launch
- Updated font awesome
- Added a check for new-post / new-user post ids on forms to keep them from pulling from the options table

## 3.6.13 - 2015-09-14
- A lot of cleanup
- Working on removing shortcodes
- Working on removing mobile detect class (just needs some ios testing to confirm)

## 3.6.12 - 2015-09-09
- Fixed problems with plugins. Some plugins were not being added. Messenger was expanded. includes/plugins.php doesn't need to be added any longer.

## 3.6.11 - 2015-08-31
- Adds permissioning for statistics screens

## 3.6.1 - 2015-08-28
- Compatible with WP 4.3
- changes theme name back to emanager
- fixes widget _construct

## 3.6.0 - 2015-07-29
- Moved plugins into TGM installer. Now plugins can be more easily updated and managed. This is the first step to a fully distributed code base to easy workflow.

## 3.5.10 - 2015-07-28
- Update to language, remoeves ME from footer
- Update engine fix

## 3.5.9 - 2015-07-05
- Added updates api and fixed bic area.

## 3.5.8 - 2015-05-18
- Fixed some small errors from plugin update. Some lingering prefix problems keeping posts from redirecting to newly created post.

## 3.5.7 - 2015-05-14
- Small errors outputs. Undefined variable, etc.

## 3.5.5 - 2015-05-12
- Fixing problems with sewn plugin
- Cleaning up email system and unsubscribe
- Updated email templates

## 3.5.5 - 2015-04-19
- changed dashboard to add em_letter
- resolve old 213
- fixed assumed linked

## 3.5.4 - 2015-04-08
- fixed letters to change bic and add proper review when closing, upgrading
- Fixed single settings, cleanup of single change.
- moved letters js to separate file
- revamping to add post type 'views' in post type folders.
- Cleaning up letters interface
- Fixing some styles. fix append/prepend issue for now.
- resolved old 203
- fixed select styles
- upgrading ACF
- resolve 220
- resolve 147
- resolve 214

## 3.5.3 - 2015-03-21
- Fixed export all
- New screenshot
- Fixed the add-new button on single templates

## 3.5.2 - 2015-03-17
- 164, fixed autosequetial NOC number issue
- Esc_url issue and file manager problems
- Fixed update function name to new format (auto update problem)
- Updated export output percentage to round numbers
- Array test problem causing small error in log
- Forcing search index table to MYISAM
- Big export all update

## 3.5.0 - 2015-02
- Launch of new Patchwerk updates



## 3.4.44 - 2014-12-11
- Fix to PDF Viewer download, use direct URL instead of transient
- Fix to modal ID recognitions, checks for double extension causing break in multiple modals

## 3.4.43 - 2014-12-10
- Adds flowcharts to some post types including em_noc, em_dcr, and em_ticket
- Removes [popup] shortcode from backup and help center to make it more reliant on bootstrap
- update to fontawesone 4.2.0

## 3.4.42 - 2014-12-09
- Adds charge status for tickets to table view and table export

## 3.4.41 - 2014-12-04
- Fixes issue with transient name length

## 3.4.40 - 2014-12-04
- Update to viewer to include download buttons and transient api

## 3.4.39 - 2014-12-03
- Replaced viewerjs with lighter pdf.js skeleton, needs zoom in feature

## 3.4.38 - 2014-12-03
- Adds edit button to pco requests if in users court
- fix for owner bic on em_letter

## 3.4.37 - 2014-12-02
- themeblvd shortcode error
- kanban loading error
- access for custom post types
- fixes em_letter view
- adds ssl to smtp

## 3.4.36 - 2014-11-20
- Adds in prelim kanban for em_activities
- Adds support for SOP Manuals in connect

## 3.4.35 - 2014-11-18
- Fixes export issue

## 3.4.34 - 2014-11-18
- Fixes company title for DCR
- Fixes issue with charge status
- Adds billed/paid buttons
- Adds track button to tickets
- Small updates to em_letter

## 3.4.33 - 2014-11-13
- Update to em_letter
- fixes sop in connect for ssl

## 3.4.32 - 2014-11-12
- Adds admin to Turner Dropdown
- Added javascript drop down to location scope selection
- Fixes location scope for items in exports
- Fixed issues with locations/scope application to items (materials, equipment, labor)
- Fixes a table placement issue in firefox.
- Adds basic em_letter framwork to handle AL/COR
- Adds SOP book library to help

## 3.4.31 - 2014-10-30
- Updates to issues

## 3.4.30 - 2014-10-28
- Updates to issues. A couple updates left
- Filter duplicate from previous by author along with existing params

## 3.4.29 - 2014-10-24
- Added "copy last entry" functionality

## 3.4.28 - 2014-10-22
- scope no line
- adds photos to daily report
- add meeting minute
- bic/action verbage

## 3.4.27 - 2014-10-16
- added SMTP support instead of PHP mail
- modified frontpage to make registration full width, removed image, added icon and link to emanager.ny
- branded to emanager.nyc
- removed icons folder from images

## 3.4.26 - 2014-10-16
- fixed company title and sort on daily report

## 3.4.25 - 2014-10-16
- Update to superintendent daily report, removed from shortcodes.php and set up full page
- Fixed issue with project directory post loop

## 3.4.24 - 2014-10-10
- Update to issues
- Added the ability to restrict forms to author or author company

## 3.4.23 - 2014-10-10
- Update to issues

## 3.4.22 - 2014-10-08
- Style Fix for weather coloring and doc centers
- Added textarea to parent connect widget for external links

## 3.4.21 - 2014-10-08
- Added in Superintendent Daily View
- Added observations to front page for super daily view
- Added Action Items Preliminary release (need to add status changer like in issues)
- Added additional (Admin only) items for future dev reference
- Updated IE language per J. Tavarez rewrite

## 3.4.20 - 2014-10-06
- Updates to issue layout to add info in the upper right. #188
- adds an additional hook for user inputted direction (not working)

## 3.4.19 - 2014-10-06
- Filter contract drop down for sub contractors on issues. ref #188
- regards #188, Updated the add issues button for owner types.
- Removed archive copy.
- Updated issues columns.
- Updated Issues from Punchlist on front page.
- Updates to get searching for pco and noc numbers working.

## 3.4.18 - 2014-09-30
- Fixes double review issue
- fixes pco export error

## 3.4.17 - 2014-09-29
- Release for issues

## 3.4.16 - 2014-09-28
- Minor bug and front page visualization fix

## 3.4.15 - 2014-09-28
- Major update to issues
- update to top right to include print and send

## 3.4.14 - 2014-09-23
- Added rate sheet upload fields to labor, material, and equipment
- unlocked companies folder for Turner users
- modified 'Change BIC' label to 'Collaborate with'

## 3.4.13 - 2014-09-15
- Added links to file manager table of contents
- Removed flipper for frontpage on iOS devices
- Added 'Send Message' link to username dropdown
- added data-toggle to username dropdown and settigns dropdown

## 3.4.12 - 2014-09-10
- Fixed spacing error with weather on front page
- started to add in stats for DCRs, need manhourr total shortcode
- added additional default labor types for Turner Construction Co.

## 3.4.11 - 2014-09-10
- Added Turner to BIC for NOCs, this gives the owner the ability to place a NOC in Turner's court

## 3.4.10 - 2014-09-09
- Turned file manager TOC into table so users can copy to excel

## 3.4.09 - 2014-09-09
- Removes index.php from table of contents in file folders

## 3.4.08 - 2014-09-09
- Removed wunderground from install
- Added table of contents to file folders

## 3.4.07 - 2014-09-09
- added permissions to settings stats
- updated weather style to be color: white
- added api key for weather in wp-admin>licenses

## 3.4.06 - 2014-09-08
- fix for consultant tab on settings page

## 3.4.05 - 2014-09-08
- added project directory into settings section
- fixed recommended ins ettings
- added revise total to settings

## 3.4.04 - 2014-09-08
- Added write permissions to roles/sub folder

## 3.4.03 - 2014-09-05
- Because Matt just learned how to gulp and sass

## 3.4.02 - 2014-09-05
- Update to Print stylesheet to include signatures, removed main-top and main-bottom

## 3.4.01 - 2014-09-04
- Added totals for PCO Inforamtion in Settings page

## 3.4.00 - 2014-09-02
- Fixes ViewerJS header error

## 3.3.99 - 2014-09-02
- Adds connect widgets into package
- Adds buttons for specialist subscription and site deactivation

## 3.3.98 - 2014-08-29
- Update to printe stylesheets
- Updated permissions to file manager
- Standardized issues to match others

## 3.3.97 - 2014-08-27
- Added no flip to front page for iOS devices experiencing issues with flipper for J. Menz

## 3.3.96 - 2014-08-25
- Replaced PDF.JS with Viewer JS - cleaner code, not made by high schoolers
- Removed viewer js admin section and hardcoded width and height
- Updated model viewer to use enqueue scripts when [model] is present
- Moved issues and locations view into partials folder, add support to single.php for cpt = issues and locations

## 3.3.95 - 2014-08-22
- Fixed issue where title shows up improperly when Turner submits a DCR

## 3.3.94 - 2014-08-20
- Updated issue taxonomy em_punchlist in emanager-settings
- updated field with_company to BIC
- small update to other fields


## 3.3.93 - 2014-08-20
- Added class for Gantt chart
- fixed issue fields: contract, location, and type

## 3.3.92 - 2014-08-19
- Fixed sewn in weather icons from loading in footer
- Removed useless jumpstart scripts, function added to functions.php
- update observations shortcode, added date picker script because I couldn't breakout the ACF one

## 3.3.91 - 2014-08-18
- Added in model and pdfjs plugin for issue #150
- removed wunderground plugin
- smaill fix to sewn-weather not recognizing path to icons

## 3.3.9 - 2014-08-18
- Fixed bug in export where init was removed.

## 3.3.8 - 2014-08-18
- Big update to clean up export and add "export all" to tables

## 3.3.7 - 2014-08-18
- Renamed todos to action items
- added in submittals, submittal packages, submittal types
- added in observations and observation shortcode (date picker not working in shortcode)
- added in datepicker time field
- added in acf_gallery field for photos
- updated wunderground api key
- removed serparate weather plugins only sewn-weather stays

## 3.3.6 - 2014-08-15
- Fixed issue in reviews with wp_current_user()

## 3.3.5 - 2014-08-15
- Fixing bug in new field value function that requires the post be passed to it.
- Simplifying table export as I fix the export all

## 3.3.4 - 2014-08-15
- Fixes issue with company names on tickets and DCRs

## 3.3.3 - 2014-08-12
- Wraps up changes to the BIC

## 3.3.0 - 2014-08-10
- Massive update to finally complete Ball In Court across the board

## 3.2.34 - 2014-08-04
- Update to ticket signature, allow workflow to be processed

## 3.2.33 - 2014-07-30
- Fixed elfinder issues with delete and move

## 3.2.32 - 2014-07-30
- new search index to expand search and keep it quicker

## 3.2.31 - 2014-07-24
- Updates to filters
- Fixed requester filter
- Fixed requester and bic dropdowns
- Added status orderby
- Made sure that null filters won't interfere with search

## 3.2.30 - 2014-07-23
- Minor change to recommended count

## 3.2.29 - 2014-07-23
- Fixed issue with NOC Recommend functionality. It wasn't updating the noc status. This was be design at one point I think, but not ideal for new BIC.

## 3.2.28 - 2014-07-17
- Sortable columns
- Fixed owner being limited to posts from their own company issue

## 3.2.26 - 2014-07-15
- Updated and turned on upgrade script

## 3.2.25 - 2014-07-15
- Fixed some issues with filters not working after last change. Fixed JS filter issue that kept previously submitted filter from submitting again. Fixed addition of company to post creation by subcontractors.

## 3.2.24 - 2014-07-14
- Fixed issue with filters overwriting query vars

## 3.2.23 - 2014-07-13
- Site urls and maintenance versioning

## 3.2.22 - 2014-07-09
- Continued cleanup of search, BIC, tables, and filters

## 3.2.21 - 2014-07-07
- Continued cleanup of BIC, tables, and filters
- Fixed bug from custom post type simplification

## 3.2.20 - 2014-07-03
- tables, filters, and post type cleanup

## 3.2.19 - 2014-07-02
- Sub permissions fix

## 3.2.18 - 2014-07-01
- update to BIC

## 3.2.17 - 2014-06-26
- initial BIC setup for NOCs

## 3.2.16 - 2014-06-25
- added pagination from server side

## 3.2.15 - 2014-06-20
- fixed inspection templates
- removed inspection questions
- added building equipment fields
- removed issue equipment systems and type stand alone equipment id field, can add javascript filter later

## 3.2.14 - 2014-06-20
- add fields for ssi and ssid

## 3.2.13 - 2014-06-19
- Fix DCr Export

## 3.2.12 - 2014-06-19
- make titles required
- add cpt and fields for future modules

## 3.2.11 - 2014-06-13
- Ajax submit

## 3.2.10 - 2014-06-09

- Updated customized title field lables on forms to work on Ajax forms.
- Fixed pagination issue.

## 3.2.8 - 2014-06-06

- Updated the new ajax add materials, equipment, etc. form. Troubleshooting.

## 3.2.7 - 2014-06-05

- Fixed problem with CSV export and subcontractors.

## 3.2.6 - 2014-06-04

- Updated signature pad. Should help with Windows and better mobile support in general.

## 3.2.5 - 2014-06-04

- Fixed PCO# autofill, so it doesn't fill in on new posts.

## 3.2.4 - 2014-05-29

- Added an https version of google charts link

## 3.2.3 - 2014-05-29

- Added pullback button

## 3.2.2 - 2014-05-29

- Updates to signature pad and fixed corrupt javascript issue.

## 3.2.1 - 2014-05-28

- Plugin updates and realignment to new Sewn system
- Many small updates by Ben
- Fixed shortcode bug
- Added ticket unique number
- Security updates

## 3.2.0 - 2014-05-22

- Initial implementation of the messenger
- Sends message for incidents on DCR

## 3.1.2 - 2014-05-22

- Fixed editing/viewing same company items by checking author's company when a post doesn't have a company assigned.
- Fixed issue with ticket approvers not gett filtered correctly.

## 3.1.1 - 2014-04-25

- Fixed hours/days updates for dcr
- Fixed bug when locations are loaded for editing

## 3.1.0 - 2014-04-25

- Updating the update process and adding support for ManageWP notifications and updates

## 3.0.21 - 2014-04-21

- Fixed IE flipper issues, flips for 10+, fallback for 9-

## 3.0.20 - 2014-04-21

- Changed PCO form "Responsible Person" to "Manager Responsible"
- Moved profile link from settings to upper right drop down
- Updated settings and user dropdowns to align text and icons better
- Changed PCO page title from NOC

## 3.0.19 - 2014-04-10

- Update to the noc/pco number retrieval, to only grab sumitted reviews and, if successful, store them in the noc to help simplify.

## 3.0.18 - 2014-04-10

- Showing locations in NOC output.

## 3.0.17 - 2014-04-10

- Fixed bug in storing and retrieving noc and pco numbers. It stopped being stored in post from review because of the change from "Approve" to "Authorize". This adds support to show the numbers in posts where that connection was broken as well.

## 3.0.16 - 2014-04-09

- Removed plugin nag from themeblvd

## 3.0.15 - 2014-04-09

- Further fixes issues with materials and equipment math problems and titles not showing up correctly in all cases. Some tweaks on the page for the markups to help make them clearer.

## 3.0.14 - 2014-04-08

- Fixes issues with materials and equipment.

## 3.0.13 - 2014-04-08

- Wrapping up phase 2, updated materials and equipment to autofill and be a little clearer and more extensive. A few small cosmetic changes: green to blue on summaries and document center info style.

## 3.0.12 - 2014-04-07

- Fixed Labor: Employee Breakdown. It calculates correctly now.

## 3.0.11 - 2014-04-07

- Added more markup types.

## 3.0.10 - 2014-04-07

- Fixed DCR and Tickets Materials dropdown to limit to user's company.

## 3.0.9 - 2014-04-07

- Updated document center to show current location and participants. Removed "company" filter for non-turner users.

## 3.0.8 - 2014-04-07

- Added ticket disclaimer for super/manager review

## 3.0.7 - 2014-04-04

- Updated permissions and created the emanager class. The permissions update will unify some disjointed checks.

## 3.0.6 - 2014-03

- Updated jumpstart and plugins.

## 3.0.2 - 2014-02-09

- Updates around how Company is handled on archive pages and DCR titles.
- Added licensing and distribution.
- MISC updates.

## 3.0.1 - 2014-01-10

- Post initial launch, some minor bug fixes.
- Updated NOC (single-noc.php), $schedule_impact needed to be replaced by $direct_to_proceed in the second content conditional.
- Added "CSI Divisions" taxonomy to contracts. This is in settings and has a separate "installed" site option since there are now production sites that will need to install this after initial install already happened.
- Tested DCR approvers, seems to be working.
- Fixed problems with Labor Types and other Settings pages showing "Company" to sub contractors in the settings list.
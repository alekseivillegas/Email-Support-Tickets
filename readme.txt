=== wpsc Support Tickets  ===
Contributors: jquindlen
Donate link: http://wpscsupporttickets.com/wordpress-support-ticket-plugin/
Tags: support,tickets,supporttickets,support-tickets,client,clients,help,helpdesk,help-desk,wpstorecart,Svenska,Norsk,Français,português,brasileiro,Deutsch,Nederlands,Suomi,Russian,ру́сский,язы́к,russkiy,yazyk,Italiano,Italian,Spanish,Español,Hebrew,עִבְרִית,српски,Serbian,Arabic,Română,Czech
Requires at least: 3.0.0
Tested up to: 3.9
Stable tag: 3.0.9

== Description ==

[wpsc Support Tickets](http://wpscsupporttickets.com/wordpress-support-ticket-plugin/ "wpsc Support Tickets") is an open source Wordpress support ticket system.  
This isn't some port of another ticket system that's been hacked to run inside Wordpress.  This is a new plugin designed from the ground up specifically for Wordpress.

Support tickets are critical to most businesses that provide a product or service, 
and is often used for clients, customers, members, authors, sponsors, pre-sale questions and more.   

For full documentation, support, addons, and related tools, visit [our site](http://wpscsupporttickets.com/wordpress-support-ticket-plugin/ "our site")

While completely optional, wpsc Support Tickets can work in unison with [wpStoreCart](http://wordpress.org/extend/plugins/wpstorecart/ "wpStoreCart"), an open source ecommerce plugin for Wordpress.
This can optionally allow you to keep your support ticket system accessible only to paying customers and/or current members.

**Hightlighted Features:**

 * Users can create support tickets and reply to their own tickets
 * Guests can use tickets as well, using just their email address.  Disabled by default.
 * Admins, and any user granted the manage_wpsc_support_tickets capability, can reply to, close, or delete any ticket
 * Front end support ticket interface is done in jQuery, and utilizes Ajax ticket loading
 * Customizable departments, email messages, and CSS for custom solutions
 * You can hide support ticket capabilities from a user who has not purchased a specific product (requires [wpStoreCart](http://wordpress.org/extend/plugins/wpstorecart/ "wpStoreCart") 2.4.9 or higher)
 * Seamless integration with open source wpStoreCart ecommerce plugin, including a shared Guest system 
 * Admin dashboard widget shows all open tickets
 * Both the admin and frontend provides a WYSIWYG HTML editor for formatting
 * Available in 16 languages
 * i18n ready and compatible (POT file included in the /languages/ directory)

**Languages Included**

 * English
 * Swedish (Svenska) by Stefan Johansson
 * Norwegian (Norsk) by Rune Kristoffersen
 * French (le Français) by Hawaien88
 * Brazilian Portuguese (português brasileiro) by Thiago Bernardi
 * German (Deutsch) by Markus Scheffknecht
 * Dutch (Nederlands) by Jos Wolbers
 * Finnish (Suomi) by Mikko Ohtonen
 * Russian (ру́сский язы́к, russkiy yazyk) by Login Roman
 * Romanian (limba Română) by Nutu Valea
 * Italian (Italiano) by Pino Cinelli
 * Spanish (Español) translation provided by Víctor Belgrano
 * Hebrew (עִבְרִית) translation provided by http://atar4u.com/
 * Serbian (српски) translation provided by WPdiscounts @ http://wpdiscounts.com
 * Arabic (العربية) translation provided by Ahmed Raslan @ http://www.nilecode.com/
 * Czech by Jan Drda

== Installation ==

The recommended way to install wpsc Support Tickets is to go into the Wordpress admin panel, and click on Add New under the 
Plugins menu.  Search for wpsc Support Tickets, and then click on Install, then click Install Now.  Once the installation 
completes, Activate the plugin

Or, if you want to install manually:

1. Download the wpsc-Support-Tickets.zip file
1. Extract the zip file to your hard drive, using a 7-zip or your archiver of choice.
1. Upload the `/wpsc-support-tickets/` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create a new page, call it something like Support Tickets
1. Inside this page, place this shortcode only: [wpscSupportTickets]
1. Visit the wpsc Support Tickets admin page and select a "mainpage" for wpsc Support Tickets to use, like the Support Tickets page we told you to create

== Frequently Asked Questions ==

= I have questions, where can I find answers? =
For full documentation, support, addons, and related tools, visit [our site](http://wpscsupporttickets.com/wordpress-support-ticket-plugin/ "our site")

== Screenshots ==
 
1. Admin panel

2. Admin dashboard widget

3. How the front end looks by default in the 2010 theme

4. Starting a new ticket

5. The menu

6. The full options

== Changelog ==

= 3.0.9 =
* Updated: Minor cosmetic update

= 3.0.8 =
* Updated: .POT language file updated to the latest version

= 3.0.7 =
* Added: Czech translation added

= 3.0.6 =
* Removed: Unused script

= 3.0.5 =
* Updated: .POT language file updated to the latest version

= 3.0.4 =
* Fixed: Patched an issue where the script was using UTC time instead of the time set in Wordpress admin panel

= 3.0.3 =
* Fixed: Patched an issue where REPLYING to a ticket without an upload was causing issues on certain servers

= 3.0.2 =
* Fixed: Patched an issue where submitting a ticket without an upload was causing issues on certain servers

= 3.0.1 =
* Fixed: Patched a couple warnings and other small errors in the 3.0 release
* Updated: .POT language file updated to the latest version
* Fixed: Tried to fix a timeout issue on sites with lots of tickets when viewing stats

= 3.0.0 =
* Added: Open ticket time added to PRO
* Added: Time it took to resolve tickets added to PRO
* Added: Stats added to PRO
* Fixed: Patched a small issue with file attachments in PRO

= 2.2.1 =
* Fixed: Patched an issue where the new ticket button would accidently submit the ticket form prematurely

= 2.2.0 =
* Fixed: Patched an issue where submitting a ticket without an upload was causing issues on certain servers

= 2.1.2 =
* Updated: Changed the unauthorized message to something more direct

= 2.1.1 =
* Fixed: Patched a bug where if all users could view all tickets, clicking on most tickets incorrectly stated they were last updated by you

= 2.1.0 =
* Fixed: Resolved a plugin conflict between this and the AG Custom Admin plugin
* Fixed: HTML markup using nicEdit in the admin panel should now work correctly
* Fixed: Patched a bug where slashes were added to HTML emails
* Fixed: Patched a bug where the Update Settings button would be unclickable at times
* Fixed: Patched a bug where if all users could view all tickets, most tickets incorrectly stated they were last updated by you

= 2.0.5 =
* Fixed: Patched an issue where backslashes were being added to HTML emails

= 2.0.4 =
* Fixed: Hopefully patched some bugs affecting some users

= 2.0.3 =
* Minor update

= 2.0.2 =
* Fixed: Patched an issue where settings were not saving correctly

= 2.0.1 =
* Updated: Language .POT file updated for changes in 2.0

= 2.0.0 =
* Added: Tons of new features for PRO users
* Added: Created options page
* Added: Added many action hooks to extend functionality
* Updated: Removed depreciated functions 

= 1.9.1 =
* Fixed: For users with session issues, guest accounts should now work properly

= 1.9.0 =
* Updated: Admin can now see the guests email address

= 1.8.12 =
* Added: Arabic (العربية) translation provided by Ahmed Raslan @ http://www.nilecode.com/

= 1.8.11 =
* Added: Serbian (српски) translation provided by WPdiscounts @ http://wpdiscounts.com
* Updated: NL translation minor update

= 1.8.10 =
* Fixed: Bug fix during activation

= 1.8.9 =
* Fixed: Incompatibilities between wpscSupportTicket and Jetpack were resolved.

= 1.8.8 =
* Added: Spanish (Español) translation provided by Víctor Belgrano url: http://www.pymessoft.com email: info@pymessoft.com
* Added: Hebrew (עִבְרִית) translation provided by http://atar4u.com/

= 1.8.7 =
* Added: Italian (Italiano) translation provided by Pino Cinelli

= 1.8.6 =
* Added: Russian (ру́сский язы́к, russkiy yazyk) translation provided by Login Roman

= 1.8.5 =
* Added: Finnish (Suomi) translation provided by Mikko Ohtonen http://twitter.com/mopetti
* Added: Dutch (Nederlands) translation provided by Jos Wolbers

= 1.8.4 =
* Added: German translation provided by Markus Scheffknecht, contact at https://twitter.com/Scheffhauser

= 1.8.3 =
* Fixed: Corrected two strings that were not properly registered for translation

= 1.8.2 =
* Added: Brazilian Portuguese translation provided by Thiago Bernardi, e-mail address: thiagobernardi@outlook.com

= 1.8.1 =
* Fixed: Fixed a javascript error regression that emerged in the 1.8.0 release.

= 1.8.0 =
* Fixed: Theme compatibility greatly improved.  Some themes break inline javascript, so I removed inline javascript function definitions, and localized them for Wordpress.
* Updated: Updated the French translation

= 1.7.7 =
* Added: Norwegian translation provided by Rune Kristoffersen
* Added: Hawaien88 for the French translate. -> Merci à Hawaien88 pour la traduction Française.

= 1.7.6 =
* Updated: Added a few missing classes from buttons
* Updated: Updated the Swedish translation

= 1.7.5 =
* Updated: Updated the POT file in the /languages/ directory
* Updated: Updated the Swedish translation

= 1.7.4 =
* Updated: A few missed translation strings were caught and are now translatable 

= 1.7.3 =
* Updated: Updated the POT file in the /languages/ directory
* Updated: Updated the Swedish translation

= 1.7.2 =
* Updated: A few missed translation strings were caught and are now translatable 

= 1.7.1 =
* Updated: Added the POT file into the /languages/ directory
* Updated: Updated the Swedish translation

= 1.7.0 =
* Added: Swedish translation provided by Stefan Johansson
* Added: Languages folder added.
* Updated: Text domain of wpsc-support-tickets manually added to all translatable strings
* Updated: A few missed translation strings were caught and are now translatable 

= 1.6.1 =
* Fixed: Turned off error reporting

= 1.6.0 =
* Updated: Fixed issue with Wordpress 3.4 Beta 1

= 1.5.0 =
* Fixed: SSL support added.  Removed references to WP_PLUGIN_URL and replaced them with plugin_url() which supports SSL
* Updated: A few strings that were not previously translatable have now become so.  Expect a German translation soon.
* Info: Minimum required Wordpress version changed to 3.0

= 1.4.0 =
* Added: You can now see who posted last on a ticket, either a staff member or the ticket creator.  This is in the front end, the admin panel, and the admin dashboard widget.  Note that staff replies that were made before you updated to this version will not work correctly.
* Fixed: Patched issue where the register URL was not always correctly formatted when certain conditions were met.
* Updated: More of the admin is now ready for language translations.
* Fixed: Patched a few issues were the word Guest was not showing up for guests

= 1.3.0 =
* Fixed: Patched the issue with improperly escaped URLs and quotes in the plugin.  Thanks for the help Bren!

= 1.2.0 =
* Fixed: Patched an issue with the admin dashboard widget declaring all ticket openers as Guest

= 1.1.0 =
* Fixed: Proactively insures that only one instance of the "Create a New Ticket" is displayed
* Added: Option to allow guests to use the ticket system by simply entering in their email address.

= 1.0.0 =
* Fixed: Removed the ability for non-approved user to see the admin dashboard widget

= 0.9.5 =
* Fixed: Removed reference to a non-used method that was causing an error on some dashboards

= 0.9.4 =
* Added: New option available to disable the inline styles which are by default put into the elements

= 0.9.3 =
* Added: Admin dashboard widget will now display a "No open tickets" message when there are no open tickets

= 0.9.2 =
* Updated: the readme.txt

= 0.9.1 =
* Updated: the description to remove references to this being an arcade plugin

= 0.9.0 =
* Added: Everything has been finished for the first public beta

= 0.5.0 =
* Added: Initial release

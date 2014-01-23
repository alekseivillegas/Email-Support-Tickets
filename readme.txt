=== Email Support Tickets  ===
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=me%40isabelcastillo%2ecom
Tags: support,tickets,support tickets,email,helpdesk,help-desk, email support,support ticket
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: 0.0.1

== Description ==

Email Support Tickets is a support ticket system for WordPress that also sends the message body via email. This plugin was forked from  the [wpsc Support Tickets](http://wordpress.org/plugins/wpsc-support-tickets/) plugin by wpStoreCart, LLC. While that plugin is excellent, this one is modified to meat my goals.

**Main Differences?**

 * Email Support Tickets includes the message body inside the email notifications, both for admin and ticket creator.
 * Limits unnecessary emails:
  * Does not send email to customer when they reply to their own ticket. 
  * Does not send email to admin when he, himself, replies. Logic: when admin replies to a ticket, if this admin's user_email is the same as the plugin's "send-to email" setting, then do not send email notification to this admin for this reply which he, himself, wrote. 
 * Email messages also include a note at the bottom to "See the entire support ticket and give your reply" with a link back to the main support ticket page. This is to encourage them to post their reply on the main support ticket page, since this is the only way to keep a record of it in the ticket system. If the customer replies directly via email (email reply), then there will be no record of this reply in the ticket system. But I find that this has not been a problem, since my customers have always obeyed this note to reply back at the main page.
 * Option to enter a Custom Registration URL. The plugin's 'Please log in or register' message links to the default WP registration page. If you have disabled registration in favor of a custom/manual registration page, this gives you option to send customers to that better registration page. Prevents visitors from getting upset by the "User registration is currently not allowed" notice.


**Original Features From the [wpsc Support Tickets](http://wordpress.org/plugins/wpsc-support-tickets/) Plugin**

 * Users can create support tickets and reply to their own tickets.
 * Guests can use tickets as well, using just their email address. Disabled by default.
 * Admins, and any user granted the manage_wpsc_support_tickets capability, can reply to, close, or delete any ticket.
 * Front end support ticket interface is done in jQuery, and utilizes Ajax ticket loading.
 * Customizable departments, email messages, and CSS for custom solutions.
 * Admin dashboard widget shows all open tickets
 * Both the admin and frontend provides a WYSIWYG HTML editor for formatting


== Installation ==

The recommended way to install wpsc Support Tickets is to go into the Wordpress admin panel, and click on Add New under the 
Plugins menu.  Search for wpsc Support Tickets, and then click on Install, then click Install Now.  Once the installation 
completes, Activate the plugin

Or, if you want to install manually:

1. Download the email-support-tickets.#.#.#.zip file
2. Extract the zip file to your hard drive, using a 7-zip or your archiver of choice.
3. Upload the `/email-support-tickets/` directory to the `/wp-content/plugins/` directory
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Create a new page which will be your main support ticket page. Give it a title like "Support Tickets", or so.
6. Inside this page, place this shortcode only: [wpscSupportTickets]
7. Visit the Email Support Tickets admin page and select that page as the "mainpage" for Email Support Tickets to use.

== Frequently Asked Questions ==

None yet

== Changelog ==

= 0.0.1 =
* Added: Initial release

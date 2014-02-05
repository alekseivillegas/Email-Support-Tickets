Email Support Tickets
=====================

Email Support Tickets is a support ticket system for WordPress that also sends the message body via email. This plugin was forked from  the [wpsc Support Tickets](http://wordpress.org/plugins/wpsc-support-tickets/) plugin by wpStoreCart, LLC and is an alternative for it. This Email Support Tickets plugin is modified to meet my needs.

Main Differences?
-----------------

 * Email Support Tickets includes the message body inside the email notifications, both for admin and ticket creator.
 * Limits unecessary emails:
  * Does not send email to customer when they reply to their own ticket. 
  * Does not send email to admin when he, himself, replies. Logic: when admin replies to a ticket, if this admin's user_email is the same as the plugin's "send-to email" setting, then do not send email notification to this admin for this reply which he, himself, wrote. 
 * Email messages also include a note at the bottom to "See the entire support ticket and give your reply" with a link back to the main support ticket page. This is to encourage them to post their reply on the main support ticket page, since this is the only way to keep a record of it in the ticket system. If the customer replies directly via email (email reply), then there will be no record of this reply in the ticket system. But I find that this has not been a problem, since my customers have always obeyed this note to reply back at the main page.
 * Option to enter a Custom Registration URL. The plugin's 'Please log in or register' message links to the default WP registration page. If you have disabled registration in favor of a custom/manual registration page, this gives you option to send customers to that better registration page. Prevents visitors from getting upset by the "User registration is currently not allowed" notice.
 * In "wpsc Support Tickets", a support ticket thread will show the Initial Message as "Posted by: {author **display_name**}, but all replies show "Posted by "{author ""nicename""}. This was confusing. I changed 'nicename' to 'display name' for consistency, and to give customers control over their display name.
 * Option to allow uploading of attachments into tickets.



Original Features From the [wpsc Support Tickets](http://wordpress.org/plugins/wpsc-support-tickets/) Plugin That Are Kept
--------------------------------------------------------------------------------------------------------------------------

 * Users can create support tickets and reply to their own tickets.
 * Guests can use tickets as well, using just their email address. Disabled by default.
 * Admins, and any user granted the manage_wpsc_support_tickets capability, can reply to, close, or delete any ticket.
 * Front end support ticket interface is done in jQuery, and utilizes Ajax ticket loading.
 * Customizable departments, email messages, and CSS for custom solutions.
 * Admin dashboard widget shows all open tickets
 * Both the admin and frontend provides a WYSIWYG HTML editor for formatting


Pending Issues and Enhancements - forking and contributing is welcome!
----------------------------------------------------------------------

*(Will get this done when I have some free time.)*


1.  This plugin still loads wp-config.php manually into several scripts. This has to be replaced by submitting the ticket forms with ajax. This has to be done for 4 forms. When this gets done, this plugin can be submitted to the WP plugin repository. Feel free to do this.

2.  Use the Settings API for the options page.

3.  Less "echoing" of HTML throughout.

4.  Localize all strings; add .pot file.


Installation
------------


**Use This Method If You Don't Want To Import Tickets from wpsc Support Tickets (or if you are just brand new to tickets)**

1.  Download the Email-Support-Tickets-master.zip file from the right side of this page.
2.  In your WordPress dashboard, go to “Plugins -> Add New“. Click “Upload“. Click “Browse” and locate the .zip file which you downloaded.
3.  Click “Install Now“.
4.  Click “Activate Plugin“.
5.  Create a new page which will be your main support ticket page. Give it a title like "Support Tickets", or so.
6.  Inside this page, place this shortcode only: `[EmailSupportTickets]`
7.  Visit the **Email Support Tickets -> Settings** page and select that page as the "mainpage" for Email Support Tickets to use.
8.  Optional: set custom options at **Email Support Tickets -> Settings**.



**Use This Method If You WANT To Import Your Tickets from wpsc Support Tickets**

1.  Download the Email-Support-Tickets-master.zip file from the right side of this page.
2.  In your WordPress dashboard, go to “Plugins -> Add New“. Click “Upload“. Click “Browse” and locate the .zip file which you downloaded.
3.  Click “Install Now“.
4.  Click “Activate Plugin“.
5.  To bring in your tickets from "wpsc Support Tickets" plugin, you must run this script now before you receive any new tickets:
[https://gist.github.com/isabelc/8829632](https://gist.github.com/isabelc/8829632)
  Follow the steps on that link to import your tickets.

6.  Edit your main support ticket page that you were using for wpsc Support Tickets. Change the shortcode to: `[EmailSupportTickets]`
7.  Visit the **Email Support Tickets -> Settings** page and select that page as the "mainpage" for Email Support Tickets to use.
8.  Set your custom settings at **Email Support Tickets -> Settings**. They have changed, so if you don't like the defaults, you will have to set your desired options now.

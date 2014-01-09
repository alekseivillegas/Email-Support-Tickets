Email Support Tickets
---------------------

Email Support Tickets is a support ticket system for WordPress that also sends the message body via email. This plugin was forked from  the [wpsc Support Tickets](http://wordpress.org/plugins/wpsc-support-tickets/) plugin by wpStoreCart, LLC. While that plugin is excellent, this one is modified to meet my goals.

**Main Differences?**

 * Email Support Tickets includes the message body inside the email notifications, both for admin and ticket creator.
 * Limits unecessary emails:
  * Does not send email to customer when they reply to their own ticket. 
  * Does not send email to admin when he, himself, replies. Logic: when admin replies to a ticket, if this admin's user_email is the same as the plugin's "send-to email" setting, then do not send email notification to this admin for this reply which he, himself, wrote. 
 * Email messages also include a note at the bottom to "See the entire support ticket and give your reply" with a link back to the main support ticket page. This is to encourage them to post their reply on the main support ticket page, since this is the only way to keep a record of it in the ticket system. If the customer replies directly via email (email reply), then there will be no record of this reply in the ticket system. But I find that this has not been a problem, since my customers have always obeyed this note to reply back at the main page.


**Original Features From the [wpsc Support Tickets](http://wordpress.org/plugins/wpsc-support-tickets/) Plugin**

 * Users can create support tickets and reply to their own tickets.
 * Guests can use tickets as well, using just their email address. Disabled by default.
 * Admins, and any user granted the manage_wpsc_support_tickets capability, can reply to, close, or delete any ticket.
 * Front end support ticket interface is done in jQuery, and utilizes Ajax ticket loading.
 * Customizable departments, email messages, and CSS for custom solutions.
 * Admin dashboard widget shows all open tickets
 * Both the admin and frontend provides a WYSIWYG HTML editor for formatting

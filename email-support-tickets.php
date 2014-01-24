<?php
/*
Plugin Name: Email Support Tickets
Plugin URI: https://github.com/isabelc/Email-Support-Tickets
Description: Support Ticket system that also sends message body via email.
Version: 0.0.2
Author: Isabel Castillo
Author URI: http://isabelcastillo.com
License: GPL2
Text Domain: email-support-tickets
Domain Path: languages

Copyright 2013 Isabel Castillo (email : me@isabelcastillo.com)

Email Support Tickets is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

Email Support Tickets is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Email Support Tickets; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if (file_exists(ABSPATH . 'wp-includes/pluggable.php')) {
	require_once(ABSPATH . 'wp-includes/pluggable.php');
}


//Global variables:
global $EmailSupportTickets, $EmailSupportTickets_version, $EmailSupportTickets_db_version, $APjavascriptQueue, $wpsc_error_reporting;
$EmailSupportTickets_version = 3.0;
$EmailSupportTickets_db_version = 3.0;
$APjavascriptQueue = NULL;
$wpsc_error_reporting = false;

// Create the proper directory structure if it is not already created
if (!is_dir(WP_CONTENT_DIR . '/uploads/')) {
	mkdir(WP_CONTENT_DIR . '/uploads/', 0777, true);
}
if (!is_dir(WP_CONTENT_DIR . '/uploads/EmailSupportTickets/')) {
	mkdir(WP_CONTENT_DIR . '/uploads/EmailSupportTickets/', 0777, true);
}

/**
 * Action definitions 
 */
function EmailSupportTickets_settings() {
	do_action('EmailSupportTickets_settings');// @todo del
}

function EmailSupportTickets_saveSettings() {
	do_action('EmailSupportTickets_saveSettings');
}

function EmailSupportTickets_extraTabsIndex() {
	do_action('EmailSupportTickets_extraTabsIndex');
}

function EmailSupportTickets_extraTabsContents() {
    do_action('EmailSupportTickets_extraTabsContents');
}

/**
 * ===============================================================================================================
 * Main EmailSupportTickets Class
 */
if (!class_exists("EmailSupportTickets")) {

	class EmailSupportTickets {

		var $adminOptionsName = "EmailSupportTicketsAdminOptions";
		var $wpscstSettings = null;
		var $hasDisplayed = false;
		var $hasDisplayedCompat = false; // hack for Jetpack compatibility
		var $hasDisplayedCompat2 = false; // hack for Jetpack compatibility

		function EmailSupportTickets() { //constructor
			// Let's make sure the admin is always in charge
			if (is_user_logged_in()) {
				if (is_super_admin() || is_admin()) {
					global $wp_roles;
					add_role('wpsc_support_ticket_manager', 'Support Ticket Manager', array('manage_wpsc_support_tickets', 'read', 'upload_files', 'publish_posts', 'edit_published_posts', 'publish_pages', 'edit_published_pages'));
					$wp_roles->add_cap('wpsc_support_ticket_manager', 'read');
					$wp_roles->add_cap('wpsc_support_ticket_manager', 'upload_files');
					$wp_roles->add_cap('wpsc_support_ticket_manager', 'publish_pages');
					$wp_roles->add_cap('wpsc_support_ticket_manager', 'publish_posts');
					$wp_roles->add_cap('wpsc_support_ticket_manager', 'edit_published_posts');
					$wp_roles->add_cap('wpsc_support_ticket_manager', 'edit_published_pages');
					$wp_roles->add_cap('wpsc_support_ticket_manager', 'manage_wpsc_support_tickets');
					$wp_roles->add_cap('administrator', 'manage_wpsc_support_tickets');
				}
			}
		}

		function init() {
			$this->getAdminOptions();
		}

		//Returns an array of admin options
		function getAdminOptions() {

			$apAdminOptions = array('mainpage' => '',
				'turnon_EmailSupportTickets' => 'true',
				'departments' => __('Support', 'email-support-tickets' ) . '||' . __('Billing', 'email-support-tickets' ),
				'email' => get_bloginfo('admin_email'),
				'email_new_ticket_subject' => __('Your support ticket was received.', 'email-support-tickets' ),
				'email_new_ticket_body' => __('Thank you for opening a new support ticket.  We will look into your issue and respond as soon as possible.', 'email-support-tickets' ),
				'email_new_reply_subject' => __('Your support ticket has a new reply.', 'email-support-tickets' ),
				'email_new_reply_body' => __('A reply was posted to one of your support tickets.', 'email-support-tickets' ),
				'registration' => '',
				'disable_inline_styles' => 'false',
				'allowguests' => 'false',
				'allow_all_tickets_to_be_replied' => 'false',
				'allow_all_tickets_to_be_viewed' => 'false',
				'allow_html' => 'false',
				'allow_closing_ticket' => 'false',
				'allow_uploads' => 'false'
			);

            if ($this->wpscstSettings != NULL) {
                $devOptions = $this->wpscstSettings;
            } else {
                $devOptions = get_option($this->adminOptionsName);
            }
            if (!empty($devOptions)) {
                foreach ($devOptions as $key => $option) {
                    $apAdminOptions[$key] = $option;
                }
            }
            update_option($this->adminOptionsName, $apAdminOptions);
            return $apAdminOptions;
        }

        /**
         * Admin Header 
         */
        function adminHeader() {

            if (function_exists('current_user_can') && !current_user_can('manage_wpsc_support_tickets')) {
                die(__('Unable to Authenticate', 'email-support-tickets' ));
            }


            echo '
            
            <div style="padding: 20px 10px 10px 10px;">';

                echo '<div style="float:left;"><img src="' . plugin_dir_url(__FILE__) . '/images/logo.png" alt="EmailSupportTickets" /></div>';

            echo '
            </div>
            <br style="clear:both;" />
            ';
        }

		function printAdminPageSettings() {

            EmailSupportTickets_saveSettings(); // Action hook for saving

            $devOptions = $this->getAdminOptions();

            echo '<script type="text/javascript">
                jQuery(function() {setTimeout(function(){ jQuery(".updated").fadeOut(); },4000);});
            </script>
		<div class="wrap">';

            $this->adminHeader();

            if (@isset($_POST['update_EmailSupportTicketsSettings'])) {

                if (isset($_POST['EmailSupportTicketsmainpage'])) {
                    $devOptions['mainpage'] = esc_sql($_POST['EmailSupportTicketsmainpage']);
                }
                if (isset($_POST['turnEmailSupportTicketsOn'])) {
                    $devOptions['turnon_EmailSupportTickets'] = esc_sql($_POST['turnEmailSupportTicketsOn']);
                }
                if (isset($_POST['departments'])) {
                    $devOptions['departments'] = esc_sql($_POST['departments']);
                }
                if (isset($_POST['email'])) {
                    $devOptions['email'] = esc_sql($_POST['email']);
                }
                if (isset($_POST['email_new_ticket_subject'])) {
                    $devOptions['email_new_ticket_subject'] = esc_sql($_POST['email_new_ticket_subject']);
                }
                if (isset($_POST['email_new_ticket_body'])) {
                    $devOptions['email_new_ticket_body'] = stripslashes($_POST['email_new_ticket_body']);
                }
                if (isset($_POST['email_new_reply_subject'])) {
                    $devOptions['email_new_reply_subject'] = esc_sql($_POST['email_new_reply_subject']);
                }
                if (isset($_POST['email_new_reply_body'])) {
                    $devOptions['email_new_reply_body'] = stripslashes($_POST['email_new_reply_body']);
                }
		if (isset($_POST['registration'])) {
			$devOptions['registration'] = esc_sql($_POST['registration']);
		}
                if (isset($_POST['disable_inline_styles'])) {
                    $devOptions['disable_inline_styles'] = esc_sql($_POST['disable_inline_styles']);
                }
                if (isset($_POST['allow_guests'])) {
                    $devOptions['allow_guests'] = esc_sql($_POST['allow_guests']);
                }

                update_option($this->adminOptionsName, $devOptions);
			echo '<div class="updated"><p><strong>';
                _e( 'Settings Updated.', 'email-support-tickets' );
                echo '</strong></p></div>';
            } ?>

            <h2><?php _e('Settings', 'email-support-tickets' ); ?></h2>
            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php echo '<p><strong>' . __('Main Page', 'email-support-tickets' ) . ':</strong> ' . __('You need to use a Page as the base for wpsc Support Tickets.', 'email-support-tickets' ) . '  <br />
            <select name="EmailSupportTicketsmainpage">
             <option value="">';
            attribute_escape(__('Select page', 'email-support-tickets' ));
            echo '</option>';

            $pages = get_pages();
            foreach ($pages as $pagg) {
                $option = '<option value="' . $pagg->ID . '"';
                if ($pagg->ID == $devOptions['mainpage']) {
                    $option .= ' selected="selected"';
                }
                $option .='>';
                $option .= $pagg->post_title;
                $option .= '</option>';
                echo $option;
            }

            echo '
            </select>
            </p>

                <strong>' . __('Departments', 'email-support-tickets' ) . ':</strong> ' . __('Separate these values with a double pipe, like this ||', 'email-support-tickets' ) . ' <br /><input name="departments" value="' . $devOptions['departments'] . '" style="width:95%;" /><br /><br />

                <strong>' . __('Email', 'email-support-tickets' ) . ':</strong> ' . __('The admin email where all new ticket &amp; reply notification emails will be sent', 'email-support-tickets' ) . '<br /><input name="email" value="' . $devOptions['email'] . '" style="width:95%;" /><br /><br />

                <strong>' . __('New Ticket Email', 'email-support-tickets' ) . '</strong> ' . __('The subject &amp; body of the email sent to the customer when creating a new ticket.', 'email-support-tickets' ) . '<br /><input name="email_new_ticket_subject" value="' . $devOptions['email_new_ticket_subject'] . '" style="width:95%;" />
                <textarea style="width:95%;" name="email_new_ticket_body">' . $devOptions['email_new_ticket_body'] . '</textarea>
                <br /><br />

                <strong>' . __('New Reply Email', 'email-support-tickets' ) . '</strong> ' . __('The subject &amp; body of the email sent to the customer when there is a new reply.', 'email-support-tickets' ) . '<br /><input name="email_new_reply_subject" value="' . $devOptions['email_new_reply_subject'] . '" style="width:95%;" />
                <textarea style="width:95%;" name="email_new_reply_body">' . $devOptions['email_new_reply_body'] . '</textarea>
                <br /><br />
                
                 <strong>' . __('Registration Page URL', 'email-support-tickets' ) . ':</strong> ' . __('Only if you have a custom registration page. Enter entire URL.', 'email-support-tickets' ) . ' <br /><input name="registration" value="' . $devOptions['registration'] . '" style="width:95%;" /><br /><br />


                <p><strong>' . __('Disable inline styles', 'email-support-tickets' ) . ':</strong> ' . __('Set this to true if you want to disable the inline CSS styles.', 'email-support-tickets' ) . '  <br />
                <select name="disable_inline_styles">
                 ';

            $pagesX[0] = 'true';
            $pagesX[1] = 'false';
            foreach ($pagesX as $pagg) {
                $option = '<option value="' . $pagg . '"';
                if ($pagg == $devOptions['disable_inline_styles']) {
                    $option .= ' selected="selected"';
                }
                $option .='>';
                $option .= $pagg;
                $option .= '</option>';
                echo $option;
            }

            echo '
                </select>

                <p><strong>' . __('Allow Guests', 'email-support-tickets' ) . ':</strong> ' . __('Set this to true if you want Guests to be able to use the support ticket system.', 'email-support-tickets' ) . '  <br />
                <select name="allow_guests">
                 ';

            $pagesY[0] = 'true';
            $pagesY[1] = 'false';
            foreach ($pagesY as $pagg) {
                $option = '<option value="' . $pagg . '"';
                if ($pagg == $devOptions['allow_guests']) {
                    $option .= ' selected="selected"';
                }
                $option .='>';
                $option .= $pagg;
                $option .= '</option>';
                echo $option;
            }

            echo '
                </select>
                </p>
                

            <input type="hidden" name="update_EmailSupportTicketsSettings" value="update" />
            <div> <input class="button-primary" style="position:relative;z-index:999999;" type="submit" name="update_EmailSupportTicketsSettings_submit" value="';
            _e('Update Settings', 'email-support-tickets' );
            echo'" /></div>
            

            </form>
            
            </div><!-- .wrap -->

        ';

            
        }


        //Prints out the admin page ================================================================================
        function printAdminPage() {
            global $wpdb;

            $devOptions = $this->getAdminOptions();
            if (function_exists('current_user_can') && !current_user_can('manage_wpsc_support_tickets')) {
                die(__('Unable to Authenticate', 'email-support-tickets' ));
            }



            echo '
                        <script type="text/javascript">
                            jQuery(function() {
                                jQuery( "#wst_tabs" ).tabs();
                            });
                        </script>                            
                        <div class="wrap">';

            $this->adminHeader();

            echo '
                        <div id="wst_tabs" style="padding:5px 5px 0px 5px;font-size:1.1em;border-color:#DDD;border-radius:6px;">
                            <ul>
                                <li><a href="#wst_tabs-1">' . __('Open', 'email-support-tickets' ) . '</a></li>
                                <li><a href="#wst_tabs-2">' . __('Closed', 'email-support-tickets' ) . '</a></li>';

            EmailSupportTickets_extraTabsIndex();
            echo '
                        </ul>                             

                        ';

            $resolution = 'Open';
            $output .= '<div id="wst_tabs-1">';
            $table_name = $wpdb->prefix . "emailst_tickets";
            $sql = "SELECT * FROM `{$table_name}` WHERE `resolution`='{$resolution}' ORDER BY `last_updated` DESC;";
            $results = $wpdb->get_results($sql, ARRAY_A);
            if (isset($results) && isset($results[0]['primkey'])) {
                if ($resolution == 'Open') {
                    $output .= '<h3>' . __('View Open Tickets:', 'email-support-tickets' ) . '</h3>';
                } elseif ($resolution == 'Closed') {
                    $output .= '<h3>' . __('View Closed Tickets:', 'email-support-tickets' ) . '</h3>';
                }
                $output .= '<table class="widefat" style="width:100%"><thead><tr><th>' . __('Ticket', 'email-support-tickets' ) . '</th><th>' . __('Status', 'email-support-tickets' ) . '</th><th>' . __('User', 'email-support-tickets' ) . '</th><th>' . __('Last Reply', 'email-support-tickets' ) . '</th></tr></thead><tbody>';
                foreach ($results as $result) {
                    if ($result['user_id'] != 0) {
                        @$user = get_userdata($result['user_id']);
                        $theusersname = $user->user_nicename;
                    } else {
                        $user = false; // Guest
                        $theusersname = __('Guest', 'email-support-tickets' );
                    }
                    if (trim($result['last_staff_reply']) == '') {
                        $last_staff_reply = __('ticket creator', 'email-support-tickets' );
                    } else {
                        if ($result['last_updated'] > $result['last_staff_reply']) {
                            $last_staff_reply = __('ticket creator', 'email-support-tickets' );
                        } else {
                            $last_staff_reply = '<strong>' . __('Staff Member', 'email-support-tickets' ) . '</strong>';
                        }
                    }
                    $output .= '<tr><td><a href="admin.php?page=EmailSupportTickets-edit&primkey=' . $result['primkey'] . '" style="border:none;text-decoration:none;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . base64_decode($result['title']) . '</a></td><td>' . $result['resolution'] . '</td><td><a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=EmailSupportTickets-admin') . '">' . $theusersname . '</a></td><td>' . date('Y-m-d g:i A', $result['last_updated']) . ' ' . __('by', 'email-support-tickets' ) . ' ' . $last_staff_reply . '</td></tr>';
                }
                $output .= '</tbody></table>';
            }
            $output .= '</div>';
            echo $output;

            $resolution = 'Closed';
            $output = '<div id="wst_tabs-2">';
            $table_name = $wpdb->prefix . "emailst_tickets";
            $sql = "SELECT * FROM `{$table_name}` WHERE `resolution`='{$resolution}' ORDER BY `last_updated` DESC;";
            $results = $wpdb->get_results($sql, ARRAY_A);
            if (isset($results) && isset($results[0]['primkey'])) {
                if ($resolution == 'Open') {
                    $output .= '<h3>' . __('View Open Tickets:', 'email-support-tickets' ) . '</h3>';
                } elseif ($resolution == 'Closed') {
                    $output .= '<h3>' . __('View Closed Tickets:', 'email-support-tickets' ) . '</h3>';
                }
                $output .= '<table class="widefat" style="width:100%"><thead><tr><th>' . __('Ticket', 'email-support-tickets' ) . '</th><th>' . __('Status', 'email-support-tickets' ) . '</th><th>' . __('User', 'email-support-tickets' ) . '</th><th>' . __('Last Reply', 'email-support-tickets' ) . '</th></tr></thead><tbody>';
                foreach ($results as $result) {
                    if ($result['user_id'] != 0) {
                        @$user = get_userdata($result['user_id']);
                        $theusersname = $user->user_nicename;
                    } else {
                        $user = false; // Guest
                        $theusersname = __('Guest', 'email-support-tickets' );
                    }
                    if (trim($result['last_staff_reply']) == '') {
                        $last_staff_reply = __('ticket creator', 'email-support-tickets' );
                    } else {
                        if ($result['last_updated'] > $result['last_staff_reply']) {
                            $last_staff_reply = __('ticket creator', 'email-support-tickets' );
                        } else {
                            $last_staff_reply = '<strong>' . __('Staff Member', 'email-support-tickets' ) . '</strong>';
                        }
                    }
                    $output .= '<tr><td><a href="admin.php?page=EmailSupportTickets-edit&primkey=' . $result['primkey'] . '" style="border:none;text-decoration:none;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . base64_decode($result['title']) . '</a></td><td>' . $result['resolution'] . '</td><td><a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=EmailSupportTickets-admin') . '">' . $theusersname . '</a></td><td>' . date('Y-m-d g:i A', $result['last_updated']) . ' ' . __('by', 'email-support-tickets' ) . ' ' . $last_staff_reply . '</td></tr>';
                }
                $output .= '</tbody></table>';
            }
            $output .= '</div>';
            echo $output;



            EmailSupportTickets_extraTabsContents();

            echo '
			</div></div>';
        }

        //END Prints out the admin page ================================================================================		

        function printAdminPageEdit() {
            global $wpdb;

            $devOptions = $this->getAdminOptions();
            if (function_exists('current_user_can') && !current_user_can('manage_wpsc_support_tickets') && is_numeric($_GET['primkey'])) {
                die(__('Unable to Authenticate', 'email-support-tickets' ));
            }
            echo '<div class="wrap">';

            $this->adminHeader();

            echo '<br style="clear:both;" /><br />';





            $primkey = intval($_GET['primkey']);

            $sql = "SELECT * FROM `{$wpdb->prefix}emailst_tickets` WHERE `primkey`='{$primkey}' LIMIT 0, 1;";
            $results = $wpdb->get_results($sql, ARRAY_A);
            if (isset($results[0])) {
                echo '<table class="widefat"><tr><td>';
                if ($results[0]['user_id'] != 0) {
                    @$user = get_userdata($results[0]['user_id']);
                    $theusersname = '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $results[0]['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=EmailSupportTickets-admin') . '">' . $user->user_nicename . ' </a>';
                } else {
                    $user = false; // Guest
                    $theusersname = __('Guest', 'email-support-tickets' ) . ' - <strong>' . $results[0]['email'] . '</strong>';
                }
                echo '<div id="emailst_meta"><h1>' . base64_decode($results[0]['title']) . '</h1> (' . $results[0]['resolution'] . ' - ' . base64_decode($results[0]['type']) . ')</div>';
                echo '<table class="widefat" style="width:100%;">';
                echo '<thead><tr><th id="emailst_results_posted_by">' . __('Posted by', 'email-support-tickets' ) . ' ' . $theusersname . ' (<span id="emailst_results_time_posted">' . date('Y-m-d g:i A', $results[0]['time_posted']) . '</span>)</th></tr></thead>';

                $messageData = strip_tags(base64_decode($results[0]['initial_message']), '<p><br><a><br><strong><b><u><ul><li><strike><sub><sup><img><font>');
                $messageData = explode('\\', $messageData);
                $messageWhole = '';
                foreach ($messageData as $messagePart) {
                    $messageWhole .= $messagePart;
                }
                echo '<tbody><tr><td id="emailst_results_initial_message"><br />' . $messageWhole;
                echo '</tbody></table>';


                $sql = "SELECT * FROM `{$wpdb->prefix}emailst_replies` WHERE `ticket_id`='{$primkey}' ORDER BY `timestamp` ASC;";
                $result2 = $wpdb->get_results($sql, ARRAY_A);
                if (isset($result2)) {
                    foreach ($result2 as $resultsX) {
                        $styleModifier1 = NULL;
                        $styleModifier2 = NULL;
                        if ($resultsX['user_id'] != 0) {
                            @$user = get_userdata($resultsX['user_id']);
                            @$userdata = new WP_User($resultsX['user_id']);
                            if ($userdata->has_cap('manage_wpsc_support_tickets')) {
                                $styleModifier1 = 'background:#FFF;';
                                $styleModifier2 = 'background:#e5e7fa;" ';
                            }
                            $theusersname = $user->user_nicename;
                        } else {
                            $user = false; // Guest
                            $theusersname = __('Guest', 'email-support-tickets' );
                        }

                        echo '<br /><table class="widefat" style="width:100%;' . $styleModifier1 . '">';
                        echo '<thead><tr><th class="emailst_results_posted_by" style="' . $styleModifier2 . '">' . __('Posted by', 'email-support-tickets' ) . ' <a href="' . get_admin_url() . 'user-edit.php?user_id=' . $resultsX['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=EmailSupportTickets-admin') . '">' . $theusersname . '</a> (<span class="emailst_results_timestamp">' . date('Y-m-d g:i A', $resultsX['timestamp']) . '</span>)<div style="float:right;"><a onclick="if(confirm(\'' . __('Are you sure you want to delete this reply?', 'email-support-tickets' ) . '\')){return true;}return false;" href="' . plugins_url('/php/delete_ticket.php', __FILE__) . '?replyid=' . $resultsX['primkey'] . '&ticketid=' . $primkey . '"><img src="' . plugins_url('/images/delete.png', __FILE__) . '" alt="delete" /> ' . __('Delete Reply', 'email-support-tickets' ) . '</a></div></th></tr></thead>';
                        $messageData = strip_tags(base64_decode($resultsX['message']), '<p><br><a><br><strong><b><u><ul><li><strike><sub><sup><img><font>');
                        $messageData = explode('\\', $messageData);
                        $messageWhole = '';
                        foreach ($messageData as $messagePart) {
                            $messageWhole .= $messagePart;
                        }
                        echo '<tbody><tr><td class="emailst_results_message"><br />' . $messageWhole . '</td></tr>';
                        echo '</tbody></table>';
                    }
                }
                echo '</td></tr></table>';
            }
            $output .= '
                            <script>
                                jQuery(document).ready(function(){
                                    jQuery(".nicEdit-main").width("100%");
                                    jQuery(".nicEdit-main").parent().width("100%");
                                    jQuery(".nicEdit-main").height("270px");
                                    jQuery(".nicEdit-main").parent().height("270px");                                    
                                    jQuery(".nicEdit-main").parent().css( "background-color", "white" );
                                });
                            </script>
                            ';
            $output .= '<form action="' . plugins_url('/php/reply_ticket.php', __FILE__) . '" method="post" enctype="multipart/form-data"><input type="hidden" name="emailst_is_staff_reply" value="yes" /><input type="hidden" name="emailst_edit_primkey" value="' . $primkey . '" /><input type="hidden" name="emailst_goback" value="yes" /> ';
            $output .= '<table class="emailst-table" style="width:100%;display:none;">';
            $output .= '<tr><td><h3>' . __('Your message', 'email-support-tickets' ) . '</h3><div id="emailst_nic_panel2" style="display:block;width:100%;"></div> <textarea name="email_st_reply" id="email_st_reply" style="display:block;width:100%;margin:0 auto 0 auto;background-color:#FFF;" rows="5" columns="6"></textarea>';
            $output .= '</td></tr>';
            $exploder = explode('||', $devOptions['departments']);

            $output .= '<tr><td><div style="float:left;"><h3>' . __('Department', 'email-support-tickets' ) . '</h3><select name="emailst_department" id="emailst_department">';
            if (isset($exploder[0])) {
                foreach ($exploder as $exploded) {
                    $output .= '<option value="' . $exploded . '"';
                    if (base64_decode($results[0]['type']) == $exploded) {
                        $output.= ' selected="selected" ';
                    } $output.='>' . $exploded . '</option>';
                }
            }
            $output .= '</select></div>
                        <div style="float:left;margin-left:20px;"><h3>' . __('Status', 'email-support-tickets' ) . '</h3><select name="emailst_status">
                                <option value="Open"';
            if ($results[0]['resolution'] == 'Open') {
                $output.= ' selected="selected" ';
            } $output.='>' . __('Open', 'email-support-tickets' ) . '</option>
                                <option value="Closed"';
            if ($results[0]['resolution'] == 'Closed') {
                $output.= ' selected="selected" ';
            } $output.='>' . __('Closed', 'email-support-tickets' ) . '</option>
                        </select></div>
                        <div style="float:left;margin-left:20px;"><h3>' . __('Actions', 'email-support-tickets' ) . '</h3>
                            <a onclick="if(confirm(\'' . __('Are you sure you want to delete this ticket?', 'email-support-tickets' ) . '\')){return true;}return false;" href="' . plugins_url('/php/delete_ticket.php', __FILE__) . '?ticketid=' . $primkey . '"><img src="' . plugins_url('/images/delete.png', __FILE__) . '" alt="delete" /> ' . __('Delete Ticket', 'email-support-tickets' ) . '</a>
                        </div>';
            if ( $devOptions['allow_uploads'] == 'true' ) {
                $output .= '<div style="float:left;margin-left:20px;"><h3>' . __('Attach a file', 'email-support-tickets' ) . '</h3> <input type="file" name="emailst_file" id="emailst_file"></div>';
            }
            $output .='         
                        <button class="button-secondary" onclick="if(confirm(\'' . __('Are you sure you want to cancel?', 'email-support-tickets' ) . '\')){window.location = \'' . get_admin_url() . 'admin.php?page=EmailSupportTickets-admin\';}return false;"  style="float:right;" ><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/stop.png', __FILE__) . '" alt="' . __('Cancel', 'email-support-tickets' ) . '" /> ' . __('Cancel', 'email-support-tickets' ) . '</button> <button class="button-primary" type="submit" name="emailst_submit" id="emailst_submit" style="float:right;margin:0 5px 0 5px;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_white_text.png', __FILE__) . '" alt="' . __('Update Ticket', 'email-support-tickets' ) . '" /> ' . __('Update Ticket', 'email-support-tickets' ) . '</button></td></tr>';


            $output .= '</table></form>';
            echo $output;

            echo '
			</div>';
        }

        // Dashboard widget code=======================================================================
        function EmailSupportTickets_main_dashboard_widget_function() {
            global $wpdb;

            $devOptions = $this->getAdminOptions();

            $table_name = $wpdb->prefix . "emailst_tickets";
            $sql = "SELECT * FROM `{$table_name}` WHERE `resolution`='Open' ORDER BY `last_updated` DESC;";
            $results = $wpdb->get_results($sql, ARRAY_A);
            if (isset($results) && isset($results[0]['primkey'])) {
                $output .= '<table class="widefat" style="width:100%"><thead><tr><th>' . __('Ticket', 'email-support-tickets' ) . '</th><th>' . __('Status', 'email-support-tickets' ) . '</th><th>' . __('Last Reply', 'email-support-tickets' ) . '</th></tr></thead><tbody>';
                foreach ($results as $result) {
                    if ($result['user_id'] != 0) {
                        @$user = get_userdata($result['user_id']);
                        $theusersname = $user->user_nicename;
                    } else {
                        $user = false; // Guest
                        $theusersname = __('Guest', 'email-support-tickets' );
                    }
                    if (trim($result['last_staff_reply']) == '') {
                        $last_staff_reply = __('ticket creator', 'email-support-tickets' ) . ' <a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=EmailSupportTickets-admin') . '">' . $theusersname . '</a>';
                    } else {
                        if ($result['last_updated'] > $result['last_staff_reply']) {
                            $last_staff_reply = __('ticket creator', 'email-support-tickets' ) . ' <a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=EmailSupportTickets-admin') . '">' . $theusersname . '</a>';
                        } else {
                            $last_staff_reply = '<strong>' . __('Staff Member', 'email-support-tickets' ) . '</strong>';
                        }
                    }

                    $output .= '<tr><td><a href="admin.php?page=EmailSupportTickets-edit&primkey=' . $result['primkey'] . '" style="border:none;text-decoration:none;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . base64_decode($result['title']) . '</a></td><td>' . $result['resolution'] . '</td><td>' . $last_staff_reply . '</td></tr>';
                }
                $output .= '</tbody></table>';
            } else {
                $output .= '<tr><td><i>' . __('No open tickets!', 'email-support-tickets' ) . '</i></td><td></td><td></td></tr>';
            }
            echo $output;
        }

        // Create the function use in the action hook
        function EmailSupportTickets_main_add_dashboard_widgets() {
            if (function_exists('current_user_can') && current_user_can('manage_wpsc_support_tickets')) {
                wp_add_dashboard_widget('EmailSupportTickets_main_dashboard_widgets', __('EmailSupportTickets Overview', 'email-support-tickets' ), array(&$this, 'EmailSupportTickets_main_dashboard_widget_function'));
            }
        }

        function addHeaderCode() {
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-tabs');
            if (@!class_exists('AGCA')) {
                wp_enqueue_script('wpscstniceditor', plugins_url('/js/nicedit/nicEdit.js', __FILE__), array('jquery'), '1.3.2');
            }
            wp_enqueue_style('plugin_name-admin-ui-css', plugins_url('/css/custom-theme/jquery-ui-1.10.3.custom.css', __FILE__), false, 2, false);
        }

        // Installation ==============================================================================================		
		function EmailSupportTickets_install() {
			global $wpdb;
			global $EmailSupportTickets_db_version;

			$table_name = $wpdb->prefix . "emailst_tickets";
			if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

				$sql = "
				CREATE TABLE `{$table_name}` (
				`primkey` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				`title` VARCHAR(512) NOT NULL, `initial_message` TEXT NOT NULL, 
				`user_id` INT NOT NULL, `email` VARCHAR(256) NOT NULL, 
				`assigned_to` INT NOT NULL DEFAULT '0', 
				`severity` VARCHAR(64) NOT NULL, 
				`resolution` VARCHAR(64) NOT NULL, 
				`time_posted` VARCHAR(128) NOT NULL, 
				`last_updated` VARCHAR(128) NOT NULL, 
				`last_staff_reply` VARCHAR(128) NOT NULL, 
				`target_response_time` VARCHAR(128) NOT NULL,
				`type` VARCHAR( 255 ) NOT NULL
				);				
				";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
			}

			$table_name = $wpdb->prefix . "emailst_replies";
			if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

				$sql = "
				CREATE TABLE `{$table_name}` (
				`primkey` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`ticket_id` INT NOT NULL ,
				`user_id` INT NOT NULL ,
				`timestamp` VARCHAR( 128 ) NOT NULL ,
				`message` TEXT NOT NULL
				);				
				";
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
			}

/*
@test remove
            $table_name = $wpdb->prefix . "wpstorecart_meta";

            if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

                $sql = "
                                    CREATE TABLE {$table_name} (
                                    `primkey` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                    `value` TEXT NOT NULL,
                                    `type` VARCHAR(32) NOT NULL,
                                    `foreignkey` INT NOT NULL
                                    );
                                    ";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
*/
			add_option( 'EmailSupportTickets_db_version', $EmailSupportTickets_db_version );


			add_action( 'init', array( $EmailSupportTickets, 'init' ) ); // Create options on activation // @test		

		}

        // END Installation ==============================================================================================
        // Shortcode =========================================
        function EmailSupportTickets_mainshortcode($atts) {
            global $wpdb;

            $table_name = $wpdb->prefix . "emailst_tickets";

            $devOptions = $this->getAdminOptions();

            extract(shortcode_atts(array(
                        'display' => 'tickets'
                            ), $atts));

            if (session_id() == "") {
                @session_start();
            };

            if ($display == null || trim($display) == '') {
                $display = 'tickets';
            }

            $output = '';
            switch ($display) {
                case 'tickets': // =========================================================
                    if ($devOptions['allow_guests'] == 'true' && !is_user_logged_in() && !$this->hasDisplayed) {
                        if (@isset($_POST['guest_email'])) {
                            $_SESSION['isaest_email'] = esc_sql($_POST['guest_email']);
                        }

                        $output .= '<br />
                                                <form name="emailst-guestform" id="emailst-guestcheckoutform" action="#" method="post">
                                                    <table>
                                                    <tr><td>' . __('Enter your email address', 'email-support-tickets' ) . ': </td><td><input type="text" name="guest_email" value="' . $_SESSION['isaest_email'] . '" /></td></tr>
                                                    <tr><td></td><td><input type="submit" value="' . __('Submit', 'email-support-tickets' ) . '" class="wpsc-button wpsc-checkout" /></td></tr>
                                                    </table>
                                                </form>
                                                <br />
                                                ';
                    }
                    if (is_user_logged_in() || @isset($_SESSION['isaest_email']) || @isset($_POST['guest_email'])) {
                        if (!$this->hasDisplayed) {
                            global $current_user;

                            $output .= '<div id="emailst_top_page" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="display:inline;"';
                            } $output.='></div><button class="emailst-button" id="emailst-new" onclick="jQuery(\'.emailst-table\').fadeIn(\'slow\');jQuery(\'#emailst-new\').fadeOut(\'slow\');jQuery(\'#emailst_edit_div\').fadeOut(\'slow\');jQuery(\'html, body\').animate({scrollTop: jQuery(\'#emailst_top_page\').offset().top}, 2000);return false;"><img ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' src="' . plugins_url('/images/Add.png', __FILE__) . '" alt="' . __('Create a New Ticket', 'email-support-tickets' ) . '" /> ' . __('Create a New Ticket', 'email-support-tickets' ) . '</button><br /><br />';
                            $output .= '<form action="' . plugins_url('/php/submit_ticket.php', __FILE__) . '" method="post" enctype="multipart/form-data">';
                            if (@isset($_POST['guest_email'])) {
                                $output .= '<input type="hidden" name="guest_email" value="' . esc_sql($_POST['guest_email']) . '" />';
                            }
                            $output .= '<table class="emailst-table" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="width:100%"';
                            } $output .='><tr><th><img src="' . plugins_url('/images/Chat.png', __FILE__) . '" alt="' . __('Create a New Ticket', 'email-support-tickets' ) . '" /> ' . __('Create a New Ticket', 'email-support-tickets' ) . '</th></tr>';
                            $output .= '<tr><td><h3>' . __('Title', 'email-support-tickets' ) . '</h3><input type="text" name="emailst_title" id="emailst_title" value=""  ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="width:100%"';
                            } $output .=' /></td></tr>';
                            $output .= '<tr><td><h3>' . __('Your message', 'email-support-tickets' ) . '</h3><div id="emailst_nic_panel" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="display:block;width:100%;"';
                            } $output.='></div> <textarea name="emailst_initial_message" id="emailst_initial_message" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="display:inline;width:100%;margin:0 auto 0 auto;" rows="5"';
                            } $output.='></textarea></td></tr>';
                            if ($devOptions['allow_uploads'] == 'true') {
                                $output .= '<tr><td><h3>' . __('Attach a file', 'email-support-tickets' ) . '</h3> <input type="file" name="emailst_file" id="emailst_file"></td></tr>';
                            }
                            $exploder = explode('||', $devOptions['departments']);

                            $output .= '<tr><td><h3>' . __('Department', 'email-support-tickets' ) . '</h3><select name="emailst_department" id="emailst_department">';
                            if (isset($exploder[0])) {
                                foreach ($exploder as $exploded) {
                                    $output .= '<option value="' . $exploded . '">' . $exploded . '</option>';
                                }
                            }
                            $output .= '</select><button class="emailst-button" id="emailst_cancel" onclick="cancelAdd();return false;"  ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;"';
                            } $output.=' ><img ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' src="' . plugins_url('/images/stop.png', __FILE__) . '" alt="' . __('Cancel', 'email-support-tickets' ) . '" /> ' . __('Cancel', 'email-support-tickets' ) . '</button><button class="emailst-button" type="submit" name="emailst_submit" id="emailst_submit" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;"';
                            }$output.='><img ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' src="' . plugins_url('/images/page_white_text.png', __FILE__) . '" alt="' . __('Submit Ticket', 'email-support-tickets' ) . '" /> ' . __('Submit Ticket', 'email-support-tickets' ) . '</button></td></tr>';


                            $output .= '</table></form>';

                            $output .= '<form action="' . plugins_url('/php/reply_ticket.php', __FILE__) . '" method="post" enctype="multipart/form-data"><input type="hidden" value="0" id="emailst_edit_primkey" name="emailst_edit_primkey" />';
                            if (@isset($_POST['guest_email'])) {
                                $output .= '<input type="hidden" name="guest_email" value="' . esc_sql($_POST['guest_email']) . '" />';
                            }

                            $output .= '<div id="emailst_edit_ticket"><div id="emailst_edit_ticket_inner"><center><img src="' . plugins_url('/images/loading.gif', __FILE__) . '" alt="' . __('Loading', 'email-support-tickets' ) . '" /></center></div>
                                                    <table ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="width:100%"';
                            } $output.=' id="email_st_reply_editor_table"><tbody>
                                                    <tr id="email_st_reply_editor_table_tr1"><td><h3>' . __('Your reply', 'email-support-tickets' ) . '</h3><div id="emailst_nic_panel2" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="display:block;width:100%;"';
                            }$output.='></div> <textarea name="email_st_reply" id="email_st_reply" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="display:inline;width:100%;margin:0 auto 0 auto;" rows="5"';
                            } $output .='></textarea></td></tr>
                                                    <tr id="email_st_reply_editor_table_tr2"><td>';

                            if ($devOptions['allow_uploads'] == 'true') {
                                $output .= '<h3>' . __('Attach a file', 'email-support-tickets' ) . '</h3> <input type="file" name="emailst_file" id="emailst_file">';
                            }

                            if ($devOptions['allow_closing_ticket'] == 'true') {
                                $output .= '
                                                        <select name="emailst_set_status" id="emailst_set_status">
                                                                            <option value="Open">' . __('Open', 'email-support-tickets' ) . '</option>
                                                                            <option value="Closed">' . __('Closed', 'email-support-tickets' ) . '</option>
                                                                    </select>            
                                                        ';
                            }

                            $output .= '<button class="emailst-button" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;"';
                            } $output.=' onclick="cancelEdit();return false;"><img src="' . plugins_url('/images/stop.png', __FILE__) . '" alt="' . __('Cancel', 'email-support-tickets' ) . '" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' /> ' . __('Cancel', 'email-support-tickets' ) . '</button><button class="emailst-button" type="submit" name="emailst_submit2" id="emailst_submit2" ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;"';
                            } $output.='><img ';
                            if ($devOptions['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' src="' . plugins_url('/images/page_white_text.png', __FILE__) . '" alt="' . __('Submit Reply', 'email-support-tickets' ) . '" /> ' . __('Submit Reply', 'email-support-tickets' ) . '</button></td></tr>
                                                    </tbody></table>
                                                </div>';
                            $output .= '</form>';

                            // Guest additions here
                            if (is_user_logged_in()) {
                                $emailst_userid = $current_user->ID;
                                $emailst_email = $current_user->user_email;
                                $emailst_username = $current_user->display_name;
                            } else {
                                $emailst_userid = 0;
                                $emailst_email = esc_sql($_SESSION['isaest_email']);
                                $emailst_username = __('Guest', 'email-support-tickets' ) . ' (' . $emailst_email . ')';
                            }

                            $output .= '<div id="emailst_edit_div">';

                            if ($devOptions['allow_all_tickets_to_be_viewed'] == 'true') {
                                $sql = "SELECT * FROM `{$table_name}` ORDER BY `last_updated` DESC;";
                            }
                            if ($devOptions['allow_all_tickets_to_be_viewed'] == 'false') {
                                $sql = "SELECT * FROM `{$table_name}` WHERE `user_id`={$emailst_userid} AND `email`='{$emailst_email}' ORDER BY `last_updated` DESC;";
                            }

                            $results = $wpdb->get_results($sql, ARRAY_A);
                            if (isset($results) && isset($results[0]['primkey'])) {
                                $output .= '<h3>' . __('View Previous Tickets:', 'email-support-tickets' ) . '</h3>';
                                $output .= '<table class="widefat" ';
                                if ($devOptions['disable_inline_styles'] == 'false') {
                                    $output.='style="width:100%"';
                                }$output.='><tr><th>' . __('Ticket', 'email-support-tickets' ) . '</th><th>' . __('Status', 'email-support-tickets' ) . '</th><th>' . __('Last Reply', 'email-support-tickets' ) . '</th></tr>';
                                foreach ($results as $result) {
                                    if (trim($result['last_staff_reply']) == '') {
                                        if ($devOptions['allow_all_tickets_to_be_viewed'] == 'false') {
                                            $last_staff_reply = __('you', 'email-support-tickets' );
                                        } else {
                                            $last_staff_reply = $result['email'];
                                        }
                                    } else {
                                        if ($result['last_updated'] > $result['last_staff_reply']) {
                                            $last_staff_reply = __('you', 'email-support-tickets' );
                                        } else {
                                            $last_staff_reply = '<strong>' . __('Staff Member', 'email-support-tickets' ) . '</strong>';
                                        }
                                    }
                                    if ($devOptions['allow_closing_ticket'] == 'true') {
                                        if ($result['resolution'] == 'Closed') {
                                            $canReopen = 'Reopenable';
                                        } else {
                                            $canReopen = $result['resolution'];
                                        }
                                    } else {
                                        $canReopen = $result['resolution'];
                                    }
                                    $output .= '<tr><td><a href="" onclick="loadTicket(' . $result['primkey'] . ',\'' . $canReopen . '\');return false;" ';
                                    if ($devOptions['disable_inline_styles'] == 'false') {
                                        $output.='style="border:none;text-decoration:none;"';
                                    }$output.='><img';
                                    if ($devOptions['disable_inline_styles'] == 'false') {
                                        $output.=' style="float:left;border:none;margin-right:5px;"';
                                    }$output.=' src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . base64_decode($result['title']) . '</a></td><td>' . $result['resolution'] . '</td><td>' . date('Y-m-d g:i A', $result['last_updated']) . ' ' . __('by', 'email-support-tickets' ) . ' ' . $last_staff_reply . '</td></tr>';
                                }
                                $output .= '</table>';
                            }
                            $output .= '</div>';
                        }
                    } else {
                        
                        if( ! empty( $devOptions['registration'] ) )
				$register_url = $devOptions['registration'];
			else
				$register_url = site_url('/wp-login.php?action=register&redirect_to=' . get_permalink());

			$output .= __('Please', 'email-support-tickets' ) . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('log in', 'email-support-tickets' ) . '</a> ' . __('or', 'email-support-tickets' ) . ' <a href="' . $register_url . '">' . __('register', 'email-support-tickets' ) . '</a>.';

                    }



                    break;
            }

            // Jetpack incompatibilities hack
            if (@!file_exists(WP_PLUGIN_DIR . '/jetpack/jetpack.php')) {
                $this->hasDisplayed = true;
            } else {
                @include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                if (@is_plugin_active(WP_PLUGIN_DIR . '/jetpack/jetpack.php')) {

                    if ($this->hasDisplayedCompat == true) {
                        if ($this->hasDisplayedCompat2 == true) {
                            $this->hasDisplayed = true;
                        }
                        $this->hasDisplayedCompat2 = true;
                    }
                    $this->hasDisplayedCompat = true;
                } else {
                    $this->hasDisplayed = true;
                }
            }


            return $output;
        }

        // END SHORTCODE ================================================
    }

    /**
     * ===============================================================================================================
     * End Main EmailSupportTickets Class
     */
}
// end IF

/**
 * Initialize the admin panel
 */
if (!function_exists("EmailSupportTicketsAdminPanel")) {

	function EmailSupportTicketsAdminPanel() {
		global $EmailSupportTickets;
		if (!isset($EmailSupportTickets)) {
			return;
		}
		if (function_exists('add_menu_page')) {
			add_menu_page(__('wpsc Support Tickets', 'email-support-tickets' ), __('Support Tickets', 'email-support-tickets'), 'manage_wpsc_support_tickets', 'EmailSupportTickets-admin', array(&$EmailSupportTickets, 'printAdminPage'), plugin_dir_url( __FILE__ ) . '/images/controller.png');
			$settingsPage = add_submenu_page('EmailSupportTickets-admin', __('Settings', 'email-support-tickets'), __('Settings', 'email-support-tickets' ), 'manage_wpsc_support_tickets', 'EmailSupportTickets-settings', array(&$EmailSupportTickets, 'printAdminPageSettings'));
			$editPage = add_submenu_page(NULL, __('Reply to Support Ticket', 'email-support-tickets' ), __('Reply to Support Tickets', 'email-support-tickets' ), 'manage_wpsc_support_tickets', 'EmailSupportTickets-edit', array(&$EmailSupportTickets, 'printAdminPageEdit'));
			add_action("admin_print_scripts-$editPage", array(&$EmailSupportTickets, 'addHeaderCode'));
			add_action("admin_print_scripts-$settingsPage", array(&$EmailSupportTickets, 'addHeaderCode'));            
		}
	}
}

function email_st_load_init() {
	load_plugin_textdomain( 'email-support-tickets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


// use this: load_plugin_textdomain( 'email-support-tickets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 @todo notice that to get plugin folder dir name, use: 

dirname( plugin_basename( __FILE__ ) )

or append like:

dirname( plugin_basename( __FILE__ ) ) . '/languages/'

*/
// @todo change js file name
	wp_enqueue_script('email-support-tickets',  plugins_url('js/email-support-tickets.js', __FILE__), array('jquery'));
	$est_params = array(
		'estPluginUrl' => plugin_dir_url( __FILE__ ),
	);
	wp_localize_script('email-support-tickets', 'estScriptParams', $est_params);
}

/**
 * Call everything
 */
if (class_exists("EmailSupportTickets")) {
	$EmailSupportTickets = new EmailSupportTickets();
}

//Actions and Filters   
if (isset($EmailSupportTickets)) {


	register_activation_hook(__FILE__, array(&$EmailSupportTickets, 'EmailSupportTickets_install')); // Install DB schema
//@test hook to activation    add_action('wpsc-support-tickets/EmailSupportTickets.php', array(&$EmailSupportTickets, 'init')); // Create options on activation // @test

	add_action('admin_menu', 'EmailSupportTicketsAdminPanel'); // Create admin panel
	add_action('wp_dashboard_setup', array(&$EmailSupportTickets, 'EmailSupportTickets_main_add_dashboard_widgets')); // Dashboard widget
    //add_action('wp_head', array(&$EmailSupportTickets, 'addHeaderCode')); // Place EmailSupportTickets comment into header
	add_shortcode('EmailSupportTickets', array(&$EmailSupportTickets, 'EmailSupportTickets_mainshortcode'));
	add_action("wp_print_scripts", array(&$EmailSupportTickets, "addHeaderCode"));
	add_action('init', 'email_st_load_init');
}
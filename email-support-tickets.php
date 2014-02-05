<?php
/*
Plugin Name: Email Support Tickets
Plugin URI: https://github.com/isabelc/Email-Support-Tickets
Description: Support Ticket system that also sends message body via email.
Version: 0.0.5
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
MERCHANTABILITY or FITFNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Email Support Tickets; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/


/*
 *  @todo if js works, then minify with http://jscompress.com/
 * @todo change menu label icon, and admin logo.

*/

if (file_exists(ABSPATH . 'wp-includes/pluggable.php')) {
	require_once(ABSPATH . 'wp-includes/pluggable.php');
}

//Global variables:
global $Email_Support_Tickets, $email_support_tickets_version, $email_support_tickets_db_version, $APjavascriptQueue, $emailst_error_reporting;
$email_support_tickets_version = 1.0; // @todo update
$email_support_tickets_db_version = 1.0; // @todo update
$APjavascriptQueue = NULL; // @todo ??
$emailst_error_reporting = false;

// Create the proper directory structure if it is not already created
if (!is_dir(WP_CONTENT_DIR . '/uploads/')) {
	mkdir(WP_CONTENT_DIR . '/uploads/', 0777, true);
}
if (!is_dir(WP_CONTENT_DIR . '/uploads/email-support-tickets/')) {
	mkdir(WP_CONTENT_DIR . '/uploads/email-support-tickets/', 0777, true);
}

/**
 * Action definitions 
 */
function email_support_tickets_settings() {
	do_action('email_support_tickets_settings');
}

function email_support_tickets_save_settings() {
	do_action('email_support_tickets_save_settings');
}

function emailst_extra_tabs_index() {
	do_action('emailst_extra_tabs_index');
}

function email_support_tickets_extra_tabs_content() {
    do_action('email_support_tickets_extra_tabs_content');
}

/**
 * ===============================================================================================================
 * Main Email_Support_Tickets Class
 */
if (!class_exists("Email_Support_Tickets")) {

	class Email_Support_Tickets {

		var $admin_options_name = "EmailSupportTicketsAdminOptions";
		var $emailst_settings = null;
		var $has_displayed = false;
		var $has_displayed_compat = false; // hack for Jetpack compatibility
		var $has_displayed_compat2 = false; // hack for Jetpack compatibility

		function Email_Support_Tickets() { //constructor

			// Let's make sure the admin is always in charge
			if (is_user_logged_in()) {
				if (is_super_admin() || is_admin()) {
					global $wp_roles;
					add_role('emailst_support_ticket_manager', 'Support Ticket Manager', array('manage_emailst_support_tickets', 'read', 'upload_files', 'publish_posts', 'edit_published_posts', 'publish_pages', 'edit_published_pages'));
					$wp_roles->add_cap('emailst_support_ticket_manager', 'read');
					$wp_roles->add_cap('emailst_support_ticket_manager', 'upload_files');
					$wp_roles->add_cap('emailst_support_ticket_manager', 'publish_pages');
					$wp_roles->add_cap('emailst_support_ticket_manager', 'publish_posts');
					$wp_roles->add_cap('emailst_support_ticket_manager', 'edit_published_posts');
					$wp_roles->add_cap('emailst_support_ticket_manager', 'edit_published_pages');
					$wp_roles->add_cap('emailst_support_ticket_manager', 'manage_emailst_support_tickets');
					$wp_roles->add_cap('administrator', 'manage_emailst_support_tickets');
				}
			}
		}

		function init() {
			$this->get_admin_options();
		}

		//Returns an array of admin options
		function get_admin_options() {

			$ap_admin_options = array('mainpage' => '',
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
				'allow_uploads' => 'true'// @test
			);

			if ($this->emailst_settings != NULL) {
				$email_st_options = $this->emailst_settings;
			} else {
				$email_st_options = get_option($this->admin_options_name);
			}
			if (!empty($email_st_options)) {
				foreach ($email_st_options as $key => $option) {
					$ap_admin_options[$key] = $option;
				}
			}
			update_option($this->admin_options_name, $ap_admin_options);
			return $ap_admin_options;
		}


	// @test
		/**
		 * enqueue styles
		 */
		function email_support_tickets_scripts() {
			wp_register_style( 'est-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
	
			$email_st_options = $this->get_admin_options();
			if ( is_page( $email_st_options['mainpage'] ) ) {
				wp_enqueue_style( 'est-style' );
			}
		}
		

		function adminHeader() {

			if (function_exists('current_user_can') && !current_user_can('manage_emailst_support_tickets')) {
				die( __( 'Unable to Authenticate', 'email-support-tickets' ) );
			}
			echo '<div style="padding: 20px 10px 10px 10px;">';
			echo '<div style="float:left;"><img src="' . plugin_dir_url(__FILE__) . '/images/logo.png" alt="Email_Support_Tickets" /></div>';
			echo '</div><br style="clear:both;" />';
		}

		function printAdminPageSettings() {

			email_support_tickets_save_settings(); // Action hook for saving

			$email_st_options = $this->get_admin_options();

			echo '<script type="text/javascript">jQuery(function() {setTimeout(function(){ jQuery(".updated").fadeOut(); },4000);});</script>'; ?>
			<div class="wrap">

			<?php $this->adminHeader();

			if (@isset($_POST['update_EmailSupportTicketsSettings'])) {

				if (isset($_POST['EmailSupportTicketsmainpage'])) {
					$email_st_options['mainpage'] = esc_sql($_POST['EmailSupportTicketsmainpage']);
				}
				if (isset($_POST['turnEmailSupportTicketsOn'])) {
					$email_st_options['turnon_EmailSupportTickets'] = esc_sql($_POST['turnEmailSupportTicketsOn']);
				}
				if (isset($_POST['departments'])) {
					$email_st_options['departments'] = esc_sql($_POST['departments']);
				}
				if (isset($_POST['email'])) {
					$email_st_options['email'] = esc_sql($_POST['email']);
				}
				if (isset($_POST['email_new_ticket_subject'])) {
					$email_st_options['email_new_ticket_subject'] = esc_sql($_POST['email_new_ticket_subject']);
				}
				if (isset($_POST['email_new_ticket_body'])) {
					$email_st_options['email_new_ticket_body'] = stripslashes($_POST['email_new_ticket_body']);
				}
				if (isset($_POST['email_new_reply_subject'])) {
					$email_st_options['email_new_reply_subject'] = esc_sql($_POST['email_new_reply_subject']);
				}
				if (isset($_POST['email_new_reply_body'])) {
					$email_st_options['email_new_reply_body'] = stripslashes($_POST['email_new_reply_body']);
				}
				if (isset($_POST['registration'])) {
					$email_st_options['registration'] = esc_sql($_POST['registration']);
				}
				if (isset($_POST['disable_inline_styles'])) {
					$email_st_options['disable_inline_styles'] = esc_sql($_POST['disable_inline_styles']);
				}
				if (isset($_POST['allow_guests'])) {
					$email_st_options['allow_guests'] = esc_sql($_POST['allow_guests']);
				}

				update_option($this->admin_options_name, $email_st_options); ?>
				<div class="updated"><p><strong>
				<?php _e( 'Settings Updated.', 'email-support-tickets' ); ?>
				</strong></p></div>
			<?php } ?>

			<h2><?php _e( 'Settings', 'email-support-tickets' ); ?></h2>
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<p><strong><?php _e('Main Page', 'email-support-tickets' ); ?>:</strong><?php _e( 'You need to use a Page as the base for Email Support Tickets.', 'email-support-tickets' ); ?><br />
			<select name="EmailSupportTicketsmainpage">
			<option value="">
			<?php __( 'Select page', 'email-support-tickets' ); ?>
			</option>

			<?php $pages = get_pages();
			foreach ($pages as $pagg) {
				$option = '<option value="' . $pagg->ID . '"';
				if ($pagg->ID == $email_st_options['mainpage']) {
					$option .= ' selected="selected"';
				}
				$option .='>';
				$option .= $pagg->post_title;
				$option .= '</option>';
				echo $option;
			} ?>

			</select>
			</p>

                <?php echo '<strong>' . __('Departments', 'email-support-tickets' ) . ':</strong> ' . __('Separate these values with a double pipe, like this ||', 'email-support-tickets' ) . ' <br /><input name="departments" value="' . $email_st_options['departments'] . '" style="width:95%;" /><br /><br />

                <strong>' . __('Email', 'email-support-tickets' ) . ':</strong> ' . __('The admin email where all new ticket &amp; reply notification emails will be sent', 'email-support-tickets' ) . '<br /><input name="email" value="' . $email_st_options['email'] . '" style="width:95%;" /><br /><br />

                <strong>' . __('New Ticket Email', 'email-support-tickets' ) . '</strong> ' . __('The subject &amp; body of the email sent to the customer when creating a new ticket.', 'email-support-tickets' ) . '<br /><input name="email_new_ticket_subject" value="' . $email_st_options['email_new_ticket_subject'] . '" style="width:95%;" />
                <textarea style="width:95%;" name="email_new_ticket_body">' . $email_st_options['email_new_ticket_body'] . '</textarea>
                <br /><br />

                <strong>' . __('New Reply Email', 'email-support-tickets' ) . '</strong> ' . __('The subject &amp; body of the email sent to the customer when there is a new reply.', 'email-support-tickets' ) . '<br /><input name="email_new_reply_subject" value="' . $email_st_options['email_new_reply_subject'] . '" style="width:95%;" />
                <textarea style="width:95%;" name="email_new_reply_body">' . $email_st_options['email_new_reply_body'] . '</textarea>
                <br /><br />
                
                 <strong>' . __('Registration Page URL', 'email-support-tickets' ) . ':</strong> ' . __('Only if you have a custom registration page. Enter entire URL.', 'email-support-tickets' ) . ' <br /><input name="registration" value="' . $email_st_options['registration'] . '" style="width:95%;" /><br /><br />


                <p><strong>' . __('Disable inline styles', 'email-support-tickets' ) . ':</strong> ' . __('Set this to true if you want to disable the inline CSS styles.', 'email-support-tickets' ) . '  <br />
                <select name="disable_inline_styles">
                 ';

            $pagesX[0] = 'true';
            $pagesX[1] = 'false';
            foreach ($pagesX as $pagg) {
                $option = '<option value="' . $pagg . '"';
                if ($pagg == $email_st_options['disable_inline_styles']) {
                    $option .= ' selected="selected"';
                }
                $option .='>';
                $option .= $pagg;
                $option .= '</option>';
                echo $option;
            } ?>
                
			</select>

			<p><strong><?php _e( 'Allow Guests', 'email-support-tickets' ); ?>:</strong><?php _e( 'Set this to true if you want Guests to be able to use the support ticket system.', 'email-support-tickets' ); ?><br />
                <select name="allow_guests">
			<?php 
			$pagesY[0] = 'true';
			$pagesY[1] = 'false';
			foreach ($pagesY as $pagg) {
				$option = '<option value="' . $pagg . '"';
				if ($pagg == $email_st_options['allow_guests']) {
					$option .= ' selected="selected"';
				}
				$option .='>';
				$option .= $pagg;
				$option .= '</option>';
				echo $option;
			} ?>

                </select>
                </p>
			<?php email_support_tickets_settings(); ?>
            <input type="hidden" name="update_EmailSupportTicketsSettings" value="update" />
            <div> <input class="button-primary" style="position:relative;z-index:999999;" type="submit" name="update_EmailSupportTicketsSettings_submit" value="<?php _e('Update Settings', 'email-support-tickets' ); ?>" /></div>
            </form>
            </div><!-- .wrap -->
           
		<?php }


		/**
		 * Prints out the admin page
		 */
		function printAdminPage() {
			global $wpdb;

			$email_st_options = $this->get_admin_options();
			if (function_exists('current_user_can') && !current_user_can('manage_emailst_support_tickets')) {
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

            emailst_extra_tabs_index();
            echo '
                        </ul>                             

                        ';

            $resolution = 'Open';
		$output = '';
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
                    $output .= '<tr><td><a href="admin.php?page=email-support-tickets-edit&primkey=' . $result['primkey'] . '" style="border:none;text-decoration:none;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . stripslashes( base64_decode($result['title']) ) . '</a></td><td>' . $result['resolution'] . '</td><td><a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=email-support-tickets-admin') . '">' . $theusersname . '</a></td><td>' . date('Y-m-d g:i A', $result['last_updated']) . ' ' . __('by', 'email-support-tickets' ) . ' ' . $last_staff_reply . '</td></tr>';
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
                    $output .= '<tr><td><a href="admin.php?page=email-support-tickets-edit&primkey=' . $result['primkey'] . '" style="border:none;text-decoration:none;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . stripslashes( base64_decode($result['title']) ) . '</a></td><td>' . $result['resolution'] . '</td><td><a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=email-support-tickets-admin') . '">' . $theusersname . '</a></td><td>' . date('Y-m-d g:i A', $result['last_updated']) . ' ' . __('by', 'email-support-tickets' ) . ' ' . $last_staff_reply . '</td></tr>';
                }
                $output .= '</tbody></table>';
            }
            $output .= '</div>';
            echo $output;



            email_support_tickets_extra_tabs_content();

            echo '
			</div></div>';
        }

        //END Prints out the admin page ================================================================================		

        function printAdminPageEdit() {
            global $wpdb;

            $email_st_options = $this->get_admin_options();
            if (function_exists('current_user_can') && !current_user_can('manage_emailst_support_tickets') && is_numeric($_GET['primkey'])) {
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
                    $theusersname = '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $results[0]['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=email-support-tickets-admin') . '">' . $user->user_nicename . ' </a>';
                } else {
                    $user = false; // Guest
                    $theusersname = __('Guest', 'email-support-tickets' ) . ' - <strong>' . $results[0]['email'] . '</strong>';
                }
                echo '<div id="emailst_meta"><h1>' . stripslashes( base64_decode($results[0]['title']) ) . '</h1> (' . $results[0]['resolution'] . ' - ' . base64_decode($results[0]['type']) . ')</div>';
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
                            if ($userdata->has_cap('manage_emailst_support_tickets')) {
                                $styleModifier1 = 'background:#FFF;';
                                $styleModifier2 = 'background:#e5e7fa;" ';
                            }
                            $theusersname = $user->user_nicename;
                        } else {
                            $user = false; // Guest
                            $theusersname = __('Guest', 'email-support-tickets' );
                        }

                        echo '<br /><table class="widefat" style="width:100%;' . $styleModifier1 . '">';
                        echo '<thead><tr><th class="emailst_results_posted_by" style="' . $styleModifier2 . '">' . __('Posted by', 'email-support-tickets' ) . ' <a href="' . get_admin_url() . 'user-edit.php?user_id=' . $resultsX['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=email-support-tickets-admin') . '">' . $theusersname . '</a> (<span class="emailst_results_timestamp">' . date('Y-m-d g:i A', $resultsX['timestamp']) . '</span>)<div style="float:right;"><a onclick="if(confirm(\'' . __('Are you sure you want to delete this reply?', 'email-support-tickets' ) . '\')){return true;}return false;" href="' . plugins_url('/php/delete_ticket.php', __FILE__) . '?replyid=' . $resultsX['primkey'] . '&ticketid=' . $primkey . '"><img src="' . plugins_url('/images/delete.png', __FILE__) . '" alt="delete" /> ' . __('Delete Reply', 'email-support-tickets' ) . '</a></div></th></tr></thead>';
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
		$output = '';
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
            $exploder = explode('||', $email_st_options['departments']);

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
            if ( $email_st_options['allow_uploads'] == 'true' ) {
                $output .= '<div style="float:left;margin-left:20px;"><h3>' . __('Attach a file', 'email-support-tickets' ) . '</h3> <input type="file" name="emailst_file" id="emailst_file"></div>';
            }
            $output .='         
                        <button class="button-secondary" onclick="if(confirm(\'' . __('Are you sure you want to cancel?', 'email-support-tickets' ) . '\')){window.location = \'' . get_admin_url() . 'admin.php?page=email-support-tickets-admin\';}return false;"  style="float:right;" ><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/stop.png', __FILE__) . '" alt="' . __('Cancel', 'email-support-tickets' ) . '" /> ' . __('Cancel', 'email-support-tickets' ) . '</button> <button class="button-primary" type="submit" name="emailst_submit" id="emailst_submit" style="float:right;margin:0 5px 0 5px;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_white_text.png', __FILE__) . '" alt="' . __('Update Ticket', 'email-support-tickets' ) . '" /> ' . __('Update Ticket', 'email-support-tickets' ) . '</button></td></tr>';


            $output .= '</table></form>';
            echo $output;

            echo '
			</div>';
        }

        // Dashboard widget code=======================================================================
        function email_support_tickets_main_dash_widget() {
            global $wpdb;

            $email_st_options = $this->get_admin_options();

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
                        $last_staff_reply = __('ticket creator', 'email-support-tickets' ) . ' <a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=email-support-tickets-admin') . '">' . $theusersname . '</a>';
                    } else {
                        if ($result['last_updated'] > $result['last_staff_reply']) {
                            $last_staff_reply = __('ticket creator', 'email-support-tickets' ) . ' <a href="' . get_admin_url() . 'user-edit.php?user_id=' . $result['user_id'] . '&wp_http_referer=' . urlencode(get_admin_url() . 'admin.php?page=email-support-tickets-admin') . '">' . $theusersname . '</a>';
                        } else {
                            $last_staff_reply = '<strong>' . __('Staff Member', 'email-support-tickets' ) . '</strong>';
                        }
                    }

                    $output .= '<tr><td><a href="admin.php?page=email-support-tickets-edit&primkey=' . $result['primkey'] . '" style="border:none;text-decoration:none;"><img style="float:left;border:none;margin-right:5px;" src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . stripslashes( base64_decode($result['title']) ) . '</a></td><td>' . $result['resolution'] . '</td><td>' . $last_staff_reply . '</td></tr>';
                }
                $output .= '</tbody></table>';
            } else {
                $output .= '<tr><td><i>' . __('No open tickets!', 'email-support-tickets' ) . '</i></td><td></td><td></td></tr>';
            }
            echo $output;
        }

        // Create the function use in the action hook
        function email_support_tickets_dashboard_widgets() {
            if (function_exists('current_user_can') && current_user_can('manage_emailst_support_tickets')) {
                wp_add_dashboard_widget('EmailSupportTickets_main_dashboard_widgets', __('EmailSupportTickets Overview', 'email-support-tickets' ), array( $this, 'email_support_tickets_main_dash_widget' ) );
            }
        }

        function addHeaderCode() {
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-tabs');
            if (@!class_exists('AGCA')) {
                wp_enqueue_script('email-st-niceditor', plugins_url('/js/nicedit/nicEdit.js', __FILE__), array('jquery'), '1.3.2');
            }
            wp_enqueue_style('email-st-admin-ui-css', plugins_url('/css/custom-theme/jquery-ui-1.10.3.custom.css', __FILE__), false, 2, false);
        }

        // Installation ==============================================================================================		
		function email_support_tickets_install() {
			global $wpdb;
			global $email_support_tickets_db_version;

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
			add_option( 'email_support_tickets_db_version', $email_support_tickets_db_version );


			add_action( 'init', array( $Email_Support_Tickets, 'init' ) ); // Create options on activation // @test		

		}

        // END Installation ==============================================================================================
        // Shortcode =========================================
        function email_support_tickets_shortcode($atts) {
            global $wpdb;

            $table_name = $wpdb->prefix . "emailst_tickets";

            $email_st_options = $this->get_admin_options();

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
                    if ($email_st_options['allow_guests'] == 'true' && !is_user_logged_in() && !$this->has_displayed) {
                        if (@isset($_POST['guest_email'])) {
                            $_SESSION['isaest_email'] = esc_sql($_POST['guest_email']);
                        }

                        $output .= '<br />
                                                <form name="emailst-guestform" id="emailst-guestcheckoutform" action="#" method="post">
                                                    <table>
                                                    <tr><td>' . __('Enter your email address', 'email-support-tickets' ) . ': </td><td><input type="text" name="guest_email" value="' . $_SESSION['isaest_email'] . '" /></td></tr>
                                                    <tr><td></td><td><input type="submit" value="' . __('Submit', 'email-support-tickets' ) . '" class="emailst-button" /></td></tr>
                                                    </table>
                                                </form>
                                                <br />
                                                ';
                    }
                    if (is_user_logged_in() || @isset($_SESSION['isaest_email']) || @isset($_POST['guest_email'])) {
                        if (!$this->has_displayed) {
                            global $current_user;

                            $output .= '<div id="emailst_top_page" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="display:inline;"';
                            } $output.='></div><button class="emailst-button" id="emailst-new" onclick="jQuery(\'.emailst-table\').fadeIn(\'slow\');jQuery(\'#emailst-new\').fadeOut(\'slow\');jQuery(\'#emailst_edit_div\').fadeOut(\'slow\');jQuery(\'html, body\').animate({scrollTop: jQuery(\'#emailst_top_page\').offset().top}, 2000);return false;"><img ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' src="' . plugins_url('/images/Add.png', __FILE__) . '" alt="' . __('Create a New Ticket', 'email-support-tickets' ) . '" /> ' . __('Create a New Ticket', 'email-support-tickets' ) . '</button><br /><br />';
                            $output .= '<form action="' . plugins_url('/php/submit_ticket.php', __FILE__) . '" method="post" enctype="multipart/form-data">';
                            if (@isset($_POST['guest_email'])) {
                                $output .= '<input type="hidden" name="guest_email" value="' . esc_sql($_POST['guest_email']) . '" />';
                            }
                            $output .= '<table class="emailst-table" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="width:100%"';
                            } $output .='><tr><th><img src="' . plugins_url('/images/Chat.png', __FILE__) . '" alt="' . __('Create a New Ticket', 'email-support-tickets' ) . '" /> ' . __('Create a New Ticket', 'email-support-tickets' ) . '</th></tr>';
                            $output .= '<tr><td><h3>' . __('Title', 'email-support-tickets' ) . '</h3><input type="text" name="emailst_title" id="emailst_title" value=""  ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="width:100%"';
                            } $output .=' /></td></tr>';
                            $output .= '<tr><td><h3>' . __('Your message', 'email-support-tickets' ) . '</h3><div id="emailst_nic_panel" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="display:block;width:100%;"';
                            } $output.='></div> <textarea name="emailst_initial_message" id="emailst_initial_message" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="display:inline;width:100%;margin:0 auto 0 auto;" rows="5"';
                            } $output.='></textarea></td></tr>';
                            if ($email_st_options['allow_uploads'] == 'true') {
                                $output .= '<tr><td><h3>' . __('Attach a file', 'email-support-tickets' ) . '</h3> <input type="file" name="emailst_file" id="emailst_file"></td></tr>';
                            }
                            $exploder = explode('||', $email_st_options['departments']);

                            $output .= '<tr><td><h3>' . __('Department', 'email-support-tickets' ) . '</h3><select name="emailst_department" id="emailst_department">';
                            if (isset($exploder[0])) {
                                foreach ($exploder as $exploded) {
                                    $output .= '<option value="' . $exploded . '">' . $exploded . '</option>';
                                }
                            }
                            $output .= '</select><button class="emailst-button" id="emailst_cancel" onclick="cancelAdd();return false;"  ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;"';
                            } $output.=' ><img ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' src="' . plugins_url('/images/stop.png', __FILE__) . '" alt="' . __('Cancel', 'email-support-tickets' ) . '" /> ' . __('Cancel', 'email-support-tickets' ) . '</button><button class="emailst-button" type="submit" name="emailst_submit" id="emailst_submit" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;"';
                            }$output.='><img ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' src="' . plugins_url('/images/page_white_text.png', __FILE__) . '" alt="' . __('Submit Ticket', 'email-support-tickets' ) . '" /> ' . __('Submit Ticket', 'email-support-tickets' ) . '</button></td></tr>';


                            $output .= '</table></form>';

                            $output .= '<form action="' . plugins_url('/php/reply_ticket.php', __FILE__) . '" method="post" enctype="multipart/form-data"><input type="hidden" value="0" id="emailst_edit_primkey" name="emailst_edit_primkey" />';
                            if (@isset($_POST['guest_email'])) {
                                $output .= '<input type="hidden" name="guest_email" value="' . esc_sql($_POST['guest_email']) . '" />';
                            }

                            $output .= '<div id="emailst_edit_ticket"><div id="emailst_edit_ticket_inner"><center><img src="' . plugins_url('/images/loading.gif', __FILE__) . '" alt="' . __('Loading', 'email-support-tickets' ) . '" /></center></div>
                                                    <table ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="width:100%"';
                            } $output.=' id="email_st_reply_editor_table"><tbody>
                                                    <tr id="email_st_reply_editor_table_tr1"><td><h3>' . __('Your reply', 'email-support-tickets' ) . '</h3><div id="emailst_nic_panel2" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="display:block;width:100%;"';
                            }$output.='></div> <textarea name="email_st_reply" id="email_st_reply" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="display:inline;width:100%;margin:0 auto 0 auto;" rows="5"';
                            } $output .='></textarea></td></tr>
                                                    <tr id="email_st_reply_editor_table_tr2"><td>';

                            if ($email_st_options['allow_uploads'] == 'true') {
                                $output .= '<h3>' . __('Attach a file', 'email-support-tickets' ) . '</h3> <input type="file" name="emailst_file" id="emailst_file">';
                            }

                            if ($email_st_options['allow_closing_ticket'] == 'true') {
                                $output .= '
                                                        <select name="emailst_set_status" id="emailst_set_status">
                                                                            <option value="Open">' . __('Open', 'email-support-tickets' ) . '</option>
                                                                            <option value="Closed">' . __('Closed', 'email-support-tickets' ) . '</option>
                                                                    </select>            
                                                        ';
                            }

                            $output .= '<button class="emailst-button" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;margin-left:8px;"';
                            } $output.=' onclick="cancelEdit();return false;"><img src="' . plugins_url('/images/stop.png', __FILE__) . '" alt="' . __('Cancel', 'email-support-tickets' ) . '" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:left;border:none;margin-right:5px;"';
                            } $output.=' /> ' . __('Cancel', 'email-support-tickets' ) . '</button><button class="emailst-button" type="submit" name="emailst_submit2" id="emailst_submit2" ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
                                $output.='style="float:right;"';
                            } $output.='><img ';
                            if ($email_st_options['disable_inline_styles'] == 'false') {
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

                            if ($email_st_options['allow_all_tickets_to_be_viewed'] == 'true') {
                                $sql = "SELECT * FROM `{$table_name}` ORDER BY `last_updated` DESC;";
                            }
                            if ($email_st_options['allow_all_tickets_to_be_viewed'] == 'false') {
                                $sql = "SELECT * FROM `{$table_name}` WHERE `user_id`={$emailst_userid} AND `email`='{$emailst_email}' ORDER BY `last_updated` DESC;";
                            }

                            $results = $wpdb->get_results($sql, ARRAY_A);
                            if (isset($results) && isset($results[0]['primkey'])) {
                                $output .= '<h3>' . __('View Previous Tickets:', 'email-support-tickets' ) . '</h3>';
                                $output .= '<table class="widefat emailst-previous-tickets" ';
                                if ($email_st_options['disable_inline_styles'] == 'false') {
                                    $output.='style="width:100%"';
                                }$output .= '><tr><th>' . __('Ticket', 'email-support-tickets' ) . '</th><th';

							if ($email_st_options['disable_inline_styles'] == 'false') {
								$output.=' style="white-space: nowrap"';
							}
							$output .= '>' . __('Status', 'email-support-tickets' ) . '</th><th>' . __('Last Reply', 'email-support-tickets' ) . '</th></tr>';
                                foreach ($results as $result) {
                                    if (trim($result['last_staff_reply']) == '') {
                                        if ($email_st_options['allow_all_tickets_to_be_viewed'] == 'false') {
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
                                    if ($email_st_options['allow_closing_ticket'] == 'true') {
                                        if ($result['resolution'] == 'Closed') {
                                            $canReopen = 'Reopenable';
                                        } else {
                                            $canReopen = $result['resolution'];
                                        }
                                    } else {
                                        $canReopen = $result['resolution'];
                                    }
                                    $output .= '<tr><td><a href="" onclick="loadTicket(' . $result['primkey'] . ',\'' . $canReopen . '\');return false;" ';
                                    if ($email_st_options['disable_inline_styles'] == 'false') {
                                        $output.='style="border:none;text-decoration:none;"';
                                    }$output.='><img';
                                    if ($email_st_options['disable_inline_styles'] == 'false') {
                                        $output.=' style="float:left;border:none;margin-right:5px;"';
                                    }$output.=' src="' . plugins_url('/images/page_edit.png', __FILE__) . '" alt="' . __('View', 'email-support-tickets' ) . '"  /> ' . stripslashes( base64_decode($result['title']) ) . '</a></td><td>' . $result['resolution'] . '</td><td>' . date('Y-m-d g:i A', $result['last_updated']) . ' ' . __('by', 'email-support-tickets' ) . ' ' . $last_staff_reply . '</td></tr>';
                                }
                                $output .= '</table>';
                            }
                            $output .= '</div>';
                        }
                    } else {
                        
                        if( ! empty( $email_st_options['registration'] ) )
				$register_url = $email_st_options['registration'];
			else
				$register_url = site_url('/wp-login.php?action=register&redirect_to=' . get_permalink());

			$output .= __('Please', 'email-support-tickets' ) . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('log in', 'email-support-tickets' ) . '</a> ' . __('or', 'email-support-tickets' ) . ' <a href="' . $register_url . '">' . __('register', 'email-support-tickets' ) . '</a>.';

                    }



                    break;
            }

            // Jetpack incompatibilities hack
            if (@!file_exists(WP_PLUGIN_DIR . '/jetpack/jetpack.php')) {
                $this->has_displayed = true;
            } else {
                @include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                if (@is_plugin_active(WP_PLUGIN_DIR . '/jetpack/jetpack.php')) {

                    if ($this->has_displayed_compat == true) {
                        if ($this->has_displayed_compat2 == true) {
                            $this->has_displayed = true;
                        }
                        $this->has_displayed_compat2 = true;
                    }
                    $this->has_displayed_compat = true;
                } else {
                    $this->has_displayed = true;
                }
            }


            return $output;
        }

        // END SHORTCODE ================================================
    }

    /**
     * ===============================================================================================================
     * End Main Email_Support_Tickets Class
     */
}
// end IF

/**
 * Initialize the admin panel
 */
if (!function_exists("email_support_tickets_admin_panel")) {

	function email_support_tickets_admin_panel() {
		global $Email_Support_Tickets;
		if (!isset($Email_Support_Tickets)) {
			return;
		}
		if (function_exists('add_menu_page')) {
			add_menu_page( __( 'Email Support Tickets', 'email-support-tickets' ), __( 'Support Tickets', 'email-support-tickets' ), 'manage_emailst_support_tickets', 'email-support-tickets-admin', array( $Email_Support_Tickets, 'printAdminPage' ), plugin_dir_url( __FILE__ ) . '/images/controller.png' );
			$settingsPage = add_submenu_page( 'email-support-tickets-admin', __( 'Settings', 'email-support-tickets' ), __( 'Settings', 'email-support-tickets' ), 'manage_emailst_support_tickets', 'email-support-tickets-settings', array( $Email_Support_Tickets, 'printAdminPageSettings' ) );
			$editPage = add_submenu_page( NULL, __( 'Reply to Support Ticket', 'email-support-tickets' ), __( 'Reply to Support Tickets', 'email-support-tickets' ), 'manage_emailst_support_tickets', 'email-support-tickets-edit', array( $Email_Support_Tickets, 'printAdminPageEdit' ) );
			add_action( "admin_print_scripts-$editPage", array( $Email_Support_Tickets, 'addHeaderCode' ) );
			add_action( "admin_print_scripts-$settingsPage", array( $Email_Support_Tickets, 'addHeaderCode' ) );            
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
	wp_enqueue_script('email-support-tickets',  plugins_url('js/email-support-tickets.js', __FILE__), array('jquery'));
	$est_params = array(
		'estPluginUrl' => plugin_dir_url( __FILE__ ),
	);
	wp_localize_script('email-support-tickets', 'estScriptParams', $est_params);
	

}

/**
 * Call everything
 */
if (class_exists("Email_Support_Tickets")) {
	$Email_Support_Tickets = new Email_Support_Tickets();
}

//Actions and Filters   
if (isset($Email_Support_Tickets)) {


	register_activation_hook( __FILE__, array( $Email_Support_Tickets, 'email_support_tickets_install' ) ); // Install DB schema
//@test hook to activation    add_action('wpsc-support-tickets/h.php', array(&$Email_Support_Tickets, 'init')); // Create options on activation // @test

	add_action( 'admin_menu', 'email_support_tickets_admin_panel' );
	add_action( 'wp_dashboard_setup', array( $Email_Support_Tickets, 'email_support_tickets_dashboard_widgets' ) );
    //add_action('wp_head', array(&$Email_Support_Tickets, 'addHeaderCode')); // Place EmailSupportTickets comment into header
	add_shortcode( 'EmailSupportTickets', array( $Email_Support_Tickets, 'email_support_tickets_shortcode' ) );
	add_action( "wp_print_scripts", array( $Email_Support_Tickets, "addHeaderCode" ) );
	add_action( 'init', 'email_st_load_init' );
	add_action( 'wp_enqueue_scripts', array( $Email_Support_Tickets, 'email_support_tickets_scripts' ) );
}
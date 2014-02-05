<?php
global $emailst_error_reporting;
if($emailst_error_reporting==false) {
    error_reporting(0);
}
if (!function_exists('add_action'))
{
    require_once("../../../../wp-config.php");
}
global $current_user, $wpdb, $Email_Support_Tickets;
$email_st_options = $Email_Support_Tickets->get_admin_options();
if (session_id() == "") {@session_start();};

if ( current_user_can('manage_emailst_support_tickets')) { // admin edits such as closing tickets should happen here first:
    if(@isset($_POST['emailst_status']) && @isset($_POST['emailst_department']) && is_numeric($_POST['emailst_edit_primkey'])) {
        $emailst_department = base64_encode(strip_tags($_POST['emailst_department']));
        $emailst_status = esc_sql($_POST['emailst_status']);
        $primkey = intval($_POST['emailst_edit_primkey']);
        // Update the Last Updated time stamp
        $updateSQL = "UPDATE `{$wpdb->prefix}emailst_tickets` SET `last_updated` = '".current_time( 'timestamp' )."', `type`='{$emailst_department}', `resolution`='{$emailst_status}' WHERE `primkey` ='{$primkey}';";
        $wpdb->query($updateSQL);
    }
}

// Update the status if applicable
if( @isset( $_POST['emailst_set_status'] ) && $email_st_options['allow_closing_ticket']=='true' ) {
    $primkey = intval($_POST['emailst_edit_primkey']);
    $emailst_set_status = esc_sql($_POST['emailst_set_status']);
    $updateSQL = "UPDATE `{$wpdb->prefix}emailst_tickets` SET `resolution`='{$emailst_set_status}' WHERE `primkey` ='{$primkey}';";
    $wpdb->query($updateSQL);
}

// Next we return users & admins to the last page if they submitted a blank reply

$string = trim(strip_tags(str_replace(chr(173), "", $_POST['email_st_reply'])));
if($string=='') { // No blank replies allowed
    if($_POST['emailst_goback']=='yes' && is_numeric($_POST['emailst_edit_primkey']) ) {
        header("HTTP/1.1 301 Moved Permanently");
        header ('Location: '.get_admin_url().'admin.php?page=email-support-tickets-edit&primkey='.$_POST['emailst_edit_primkey']);
    } else {
        header("HTTP/1.1 301 Moved Permanently");
        header ('Location: '.get_permalink($email_st_options['mainpage']));
    }
    exit();
}

// If there is a reply and we're still executing code, now we'll add the reply
if((is_user_logged_in() || @isset($_SESSION['isaest_email'])) && is_numeric($_POST['emailst_edit_primkey'])) {

    // Guest additions here
    if(is_user_logged_in()) {
        $emailst_userid = $current_user->ID;
        $emailst_email = $current_user->user_email;
    } else {
        $emailst_userid = 0;
        $emailst_email = esc_sql($_SESSION['isaest_email']);  
        if(trim($emailst_email)=='') {
            $emailst_email = @esc_sql($_POST['guest_email']);
        }        
    }    
    
    $primkey = intval($_POST['emailst_edit_primkey']);
    if ( !current_user_can('manage_emailst_support_tickets')) {

       if($email_st_options['allow_all_tickets_to_be_replied']=='true' && $email_st_options['allow_all_tickets_to_be_viewed']=='true') {
           $sql = "SELECT * FROM `{$wpdb->prefix}emailst_tickets` WHERE `primkey`='{$primkey}' LIMIT 0, 1;";
        }                                                

       if($email_st_options['allow_all_tickets_to_be_replied']=='false' || $email_st_options['allow_all_tickets_to_be_viewed']=='false') {

           $sql = "SELECT * FROM `{$wpdb->prefix}emailst_tickets` WHERE `primkey`='{$primkey}' AND `user_id`='{$emailst_userid}' AND `email`='{$emailst_email}' LIMIT 0, 1;";
        }        
    } else {
        // This allows approved users, such as the admin, to reply to any support ticket
        $sql = "SELECT * FROM `{$wpdb->prefix}emailst_tickets` WHERE `primkey`='{$primkey}' LIMIT 0, 1;";
    }
   $results = $wpdb->get_results( $sql , ARRAY_A );

    if(isset($results[0])) {
       
           $emailst_message = '';
// @test if uploads work            if($email_st_options['allow_uploads']=='true' && @isset($_FILES["emailst_file"]) && @$_FILES["emailst_file"]["error"] != 4 ) {// @test if uploads work


            if( @isset($_FILES["emailst_file"]) && @$_FILES["emailst_file"]["error"] != 4 ) {// @test if uploads work


                /* Handles the error output. This error message will be sent to the uploadSuccess event handler.  The event handler

                will have to check for any error messages and react as needed. */

                function HandleError($message) {
                        echo '<script type="text/javascript">alert("'.$message.'");</script>'.$message.'';
                }


                // Code for Session Cookie workaround
                        if (isset($_POST["PHPSESSID"])) {
                                session_id($_POST["PHPSESSID"]);
                        } else if (isset($_GET["PHPSESSID"])) {
                                session_id($_GET["PHPSESSID"]);
                        }

// @test can i upload without                         session_start();// @todo fix PHP notice

                // Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
                        $POST_MAX_SIZE = @ini_get('post_max_size');
                        if(@$POST_MAX_SIZE == NULL || $POST_MAX_SIZE < 1) {$POST_MAX_SIZE=9999999999999;};
                        $unit = strtoupper(substr($POST_MAX_SIZE, -1));
                        $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

                        if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {

                            header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
                           _e("POST exceeded maximum allowed size.", 'email-support-tickets' );
                        }

                // Settings
                        $emailst_wordpress_upload_dir = wp_upload_dir();
                        $save_path = $emailst_wordpress_upload_dir['basedir']. '/email-support-tickets/';
                        if(!is_dir($save_path)) {
                                @mkdir($save_path);
                        }                
                        $upload_name = "emailst_file";
                        $max_file_size_in_bytes = 2147483647;				// 2GB in bytes
                        $valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				// Characters allowed in the file name (in a Regular Expression format)

                // Other variables	
                        $MAX_FILENAME_LENGTH = 260;
                        $file_name = "";
                        $file_extension = "";
                        $uploadErrors = array(
                                0=>__("There is no error, the file uploaded with success", 'email-support-tickets' ),
                                1=>__("The uploaded file exceeds the upload_max_filesize directive in php.ini", 'email-support-tickets' ),
                                2=>__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 'email-support-tickets' ),
                                3=>__("The uploaded file was only partially uploaded", 'email-support-tickets' ),
                                4=>__("No file was uploaded", 'email-support-tickets' ),
                                6=>__("Missing a temporary folder", 'email-support-tickets' )
                        );
                // Validate the upload
                        if (!isset($_FILES[$upload_name])) {

                       } else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
                                HandleError($uploadErrors[$_FILES[$upload_name]["error"]]);
                        } else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
                                HandleError(__("Upload failed is_uploaded_file test.", 'email-support-tickets' ));
                        } else if (!isset($_FILES[$upload_name]['name'])) {
                                HandleError(__("File has no name.", 'email-support-tickets' ));
                        }

                // Validate the file size (Warning: the largest files supported by this code is 2GB)
                        $file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
                        if (!$file_size || $file_size > $max_file_size_in_bytes) {
                               HandleError(__("File exceeds the maximum allowed size", 'email-support-tickets' ));
                        }

                       if ($file_size <= 0) {
                                HandleError(__("File size outside allowed lower bound", 'email-support-tickets' ));
                        }
                // Validate file name (for our purposes we'll just remove invalid characters)
                        $file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));

                        if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
                                HandleError(__("Invalid file name", 'email-support-tickets' ));
                        }

                       if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$file_name)) {
                                HandleError(__("File could not be saved.", 'email-support-tickets' ));
                        } else {
                            // SUCCESS
                            $emailst_message .= '<br /><p class="emailst-support-ticket-attachment"';
                            if($email_st_options['disable_inline_styles']=='false'){
                                $emailst_message .=  ' style="border: 1px solid #DDD;padding:8px;" ';
                            }
                            $emailst_message .= '>';
                            $emailst_message .= '<strong>'.__( 'ATTACHMENT','email-support-tickets' ).'</strong>: <a href="'.$emailst_wordpress_upload_dir['baseurl'].'/email-support-tickets/'.$file_name.'" target="_blank">'.$emailst_wordpress_upload_dir['baseurl'].'/email-support-tickets/'.$file_name.'</a></p>';
                        }       
            }        
        
            $emailst_message = base64_encode($_POST['email_st_reply'] . $emailst_message);
            $sql = "
            INSERT INTO `{$wpdb->prefix}emailst_replies` (
                `primkey` ,
                `ticket_id` ,
                `user_id` ,
                `timestamp` ,
                `message`
            )
            VALUES (
                NULL , '{$primkey}', '{$emailst_userid}', '".current_time( 'timestamp' )."', '{$emailst_message}'
            );
            ";
           $wpdb->query($sql);

		$isa_staff_reply = '';

		// Update the Last Updated time stamp

		if( isset( $_POST['emailst_is_staff_reply'] ) && $_POST['emailst_is_staff_reply'] == 'yes' && current_user_can( 'manage_emailst_support_tickets' ) ) {
                    // This is a staff reply from the admin panel
                    $updateSQL = "UPDATE `{$wpdb->prefix}emailst_tickets` SET `last_updated` = '".current_time( 'timestamp' )."', `last_staff_reply` = '".time()."' WHERE `primkey` ='{$primkey}';";

				$isa_staff_reply = 'true';
            } else {

                    // This is a reply from the front end
                    $updateSQL = "UPDATE `{$wpdb->prefix}emailst_tickets` SET `last_updated` = '".current_time( 'timestamp' )."' WHERE `primkey` ='{$primkey}';";
            }
            $wpdb->query($updateSQL);


		if( 'true' == $isa_staff_reply ) { // @isa only send email to ticket creator if reply is from admin

			$to      = $results[0]['email']; // Send this to the original ticket creator
			$subject = $email_st_options['email_new_reply_subject'];
			$message = $email_st_options['email_new_reply_body'] . '<br /><br />Here is the reply:<br /><br />' .
					stripslashes_deep(base64_decode($emailst_message)).
					'<br /><br />See the entire support ticket and give your reply at:<br /><a href="' .
					get_permalink($email_st_options['mainpage']) . '">' .
					get_permalink($email_st_options['mainpage']) . '</a><br /><br />';
			$headers = '';
				$headers .= 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";                
			$headers .= 'From: ' . $email_st_options['email'] . "\r\n" .
			'Reply-To: ' . $email_st_options['email'] .  "\r\n" .
			'X-Mailer: PHP/' . phpversion();
			@mail($to, $subject, $message, $headers);
	
		} else {

			// not a staff reply, so send email to admin

			if($email_st_options['email']!=$results[0]['email']) {

                $to      = $email_st_options['email']; // Send this to the admin
                $subject = __("Reply to a support ticket was received.", 'email-support-tickets' );
                $message = __( 'There is a new reply on support ticket: ','email-support-tickets' ).get_admin_url().'admin.php?page=email-support-tickets-edit&primkey='.$primkey.'';
			$message .= '<br /><br />Here is the reply:<br /><br />' . stripslashes_deep(base64_decode($emailst_message));
                $headers = '';
                    $headers .= 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";                
                $headers .= 'From: ' . $email_st_options['email'] . "\r\n" .
                'Reply-To: ' . $email_st_options['email'] .  "\r\n" .
                'X-Mailer: PHP/' . phpversion();
                @mail($to, $subject, $message, $headers);
            }
		}
    }
}

if ( isset( $_POST['emailst_goback'] ) && $_POST['emailst_goback'] == 'yes' ) {
    header("HTTP/1.1 301 Moved Permanently");
    header ('Location: '.get_admin_url().'admin.php?page=email-support-tickets-edit&primkey='.$primkey);
} else {
    header("HTTP/1.1 301 Moved Permanently");
    header ('Location: '.get_permalink($email_st_options['mainpage']));
}
exit(); ?>

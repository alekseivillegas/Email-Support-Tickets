<?php
global $wpsc_error_reporting;
if($wpsc_error_reporting==false) {
    error_reporting(0);
}
if (!function_exists('add_action')) {
    require_once("../../../../wp-config.php");
}
global $current_user, $wpdb, $EmailSupportTickets;
$devOptions = NULL;
$devOptions = $EmailSupportTickets->getAdminOptions();
if(!isset($devOptions['mainpage']) || $devOptions['mainpage']=='') {
    $devOptions['mainpage'] = home_url();
}

if (session_id() == "") {@session_start();};
if(is_user_logged_in() || @isset($_SESSION['isaest_email'])) {
    if(trim($_POST['emailst_initial_message'])=='' || trim($_POST['emailst_title'])=='') {// No blank messages/titles allowed
            if(!headers_sent()) {
                header("HTTP/1.1 301 Moved Permanently");
                header ('Location: '.get_permalink($devOptions['mainpage']));
                exit();
            } else {
                echo '<script type="text/javascript">
                        <!--
                        window.location = "'.get_permalink($devOptions['mainpage']).'"
                        //-->
                        </script>';
            }
        } 
    // Guest additions here
    if(is_user_logged_in()) {
        $emailst_userid = $current_user->ID;
        $emailst_email = $current_user->user_email;
    } else {
       $emailst_userid = 0;
        $emailst_email = $wpdb->escape($_SESSION['isaest_email']);     
        if(trim($emailst_email)=='') {
            $emailst_email = @$wpdb->escape($_POST['guest_email']);
        }
    }

    $emailst_initial_message = '';
    if($devOptions['allow_uploads']=='true' && @isset($_FILES["wpscst_file"]) && @$_FILES["wpscst_file"]["error"] != 4 ) {
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
		session_start();
	// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
		$POST_MAX_SIZE = @ini_get('post_max_size');
                if(@$POST_MAX_SIZE == NULL || $POST_MAX_SIZE < 1) {$POST_MAX_SIZE=9999999999999;};
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
		if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
		header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
			_e( 'POST exceeded maximum allowed size.', 'email-support-tickets' );
		}
	// Settings
                $wpsc_wordpress_upload_dir = wp_upload_dir();
		$save_path = $wpsc_wordpress_upload_dir['basedir']. '/wpsc-support-tickets/';
                if(!is_dir($save_path)) {
                        @mkdir($save_path);
                }                
		$upload_name = "wpscst_file";
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
                   $emailst_initial_message .= '<br /><p class="wpsc-support-ticket-attachment"';
                    if($devOptions['disable_inline_styles']=='false'){
                        $emailst_initial_message .=  ' style="border: 1px solid #DDD;padding:8px;" ';
                    }
                    $emailst_initial_message .= '>';
                    $emailst_initial_message .= '<img src="'.plugins_url().'/wpsc-support-tickets-pro/images/attachment.png" alt="" /> <strong>'.__( 'ATTACHMENT','email-support-tickets' ).'</strong>: <a href="'.$wpsc_wordpress_upload_dir['baseurl'].'/wpsc-support-tickets/'.$file_name.'" target="_blank">'.$wpsc_wordpress_upload_dir['baseurl'].'/wpsc-support-tickets/'.$file_name.'</a></p>';
	       }       
    }    
    $emailst_title = base64_encode(strip_tags($_POST['emailst_title']));
    $emailst_initial_message = base64_encode($_POST['emailst_initial_message'] . $emailst_initial_message);
    $emailst_department = base64_encode(strip_tags($_POST['emailst_department']));    
	$sql = "
    INSERT INTO `{$wpdb->prefix}emailst_tickets` (
        `primkey`, `title`, `initial_message`, `user_id`, `email`, `assigned_to`, `severity`, `resolution`, `time_posted`, `last_updated`, `last_staff_reply`, `target_response_time`, `type`) VALUES (
            NULL,
            '{$emailst_title}',
            '{$emailst_initial_message}',
            '{$emailst_userid}',
            '{$emailst_email}',
            '0',
            'Normal',
            'Open',
            '".current_time( 'timestamp' )."',
            '".current_time( 'timestamp' )."',
            '',
            '2 days',
            '{$emailst_department}'
        );
    ";
    $wpdb->query($sql);
    $lastID = $wpdb->insert_id;
    $to      = $emailst_email; // Send this to the ticket creator
    $subject = $devOptions['email_new_ticket_subject'];
    $message = $devOptions['email_new_ticket_body'];
    $headers = '';
    if($devOptions['allow_html']=='true') {
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";                
    }    
    $headers .= 'From: ' . $devOptions['email'] . "\r\n" .
        'Reply-To: ' . $devOptions['email'] .  "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    wp_mail($to, $subject, $message, $headers);
    $to      = $devOptions['email']; // Send this to the admin
    $subject = __("A new support ticket was received.", 'email-support-tickets' );
    $message = __( 'There is a new support ticket: ','email-support-tickets' ).get_admin_url().'admin.php?page=EmailSupportTickets-edit&primkey='.$lastID;
	$message .= '<br /><br />Here is the initial message:<br /><br />' . stripslashes_deep(base64_decode($emailst_initial_message));// @test isa
    $headers = '';
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";                
    $headers .= 'From: ' . $devOptions['email'] . "\r\n" .
    'Reply-To: ' . $devOptions['email'] .  "\r\n" .
    'X-Mailer: PHP/' . phpversion();
    wp_mail($to, $subject, $message, $headers);
}
if(!headers_sent()) {
    header("HTTP/1.1 301 Moved Permanently");
    header ('Location: '.get_permalink($devOptions['mainpage']));
} else {
    echo '<script type="text/javascript">
            <!--
            window.location = "'.get_permalink($devOptions['mainpage']).'"
            //-->
            </script>';
}
   exit();
?>

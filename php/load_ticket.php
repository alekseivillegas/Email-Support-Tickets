<?php
global $emailst_error_reporting;
if($emailst_error_reporting==false) {
    error_reporting(0);
}
if (!function_exists('add_action'))
{
    require_once("../../../../wp-config.php");// @todo remove
}

global $current_user, $wpdb;

if (session_id() == "") {@session_start();};

if((is_user_logged_in() || @isset($_SESSION['isaest_email'])) && is_numeric($_POST['primkey'])) {
    
    $devOptions = get_option('EmailSupportTicketsAdminOptions');
    
    // Guest additions here
    if(is_user_logged_in()) {
        $emailst_userid = $current_user->ID;
        $emailst_email = $current_user->user_email;
        $emailst_username = $current_user->display_name;
    } else {
        $emailst_userid = 0;
        $emailst_email = $wpdb->escape($_SESSION['isaest_email']);   
        $emailst_username = __( 'Guest', 'email-support-tickets' ).' ('.$emailst_email.')';
    }    
    
    $primkey = intval($_POST['primkey']);

    if($devOptions['allow_all_tickets_to_be_viewed']=='true') {
        $sql = "SELECT * FROM `{$wpdb->prefix}emailst_tickets` WHERE `primkey`='{$primkey}' LIMIT 0, 1;";
    }                                                
    if($devOptions['allow_all_tickets_to_be_viewed']=='false') {
        $sql = "SELECT * FROM `{$wpdb->prefix}emailst_tickets` WHERE `primkey`='{$primkey}' AND `user_id`='{$emailst_userid}' AND `email`='{$emailst_email}' LIMIT 0, 1;";
    }    
    
    $results = $wpdb->get_results( $sql , ARRAY_A );
    if(isset($results[0])) {
        if($devOptions['allow_all_tickets_to_be_viewed']=='true') {
            $emailst_username = $results[0]['email'];
        }        
        echo '<div id="emailst_meta"><strong>'.base64_decode($results[0]['title']).'</strong> ('.$results[0]['resolution'].' - '.base64_decode($results[0]['type']).')</div>';
        echo '<table style="width:100%;">';
        echo '<thead><tr><th id="emailst_results_posted_by">'.__( 'Posted by', 'email-support-tickets' ).' '.$emailst_username.' (<span id="emailst_results_time_posted">'.date('Y-m-d g:i A',$results[0]['time_posted']).'</span>)</th></tr></thead>';

        $messageData = strip_tags(base64_decode($results[0]['initial_message']),'<p><br><a><br><strong><b><u><ul><li><strike><sub><sup><img><font>');
        $messageData = explode ( '\\', $messageData);
        $messageWhole = '';
        foreach ($messageData as $messagePart){
         $messageWhole .= $messagePart;	
        }
        echo '<tbody><tr><td id="emailst_results_initial_message"><br />'.$messageWhole;        
        
        //echo '<tbody><tr><td id="emailst_results_initial_message"><br />'.strip_tags(base64_decode($results[0]['initial_message']),'<p><br><a><br><strong><b><u><ul><li><strike><sub><sup><img><font>').'</td></tr>';
        echo '</tbody></table>';

        $results = NULL;
        $sql = "SELECT * FROM `{$wpdb->prefix}emailst_replies` WHERE `ticket_id`='{$primkey}' ORDER BY `timestamp` ASC;";
        $result2 = $wpdb->get_results( $sql , ARRAY_A );
        if(isset($result2)) {
            foreach ($result2 as $results) {
                $classModifier1 = NULL;$classModifier2 = NULL;$classModifier3 = NULL;
                if($results['user_id']!=0) {
                    @$user=get_userdata($results['user_id']);
                    @$userdata = new WP_User($results['user_id']);
                    if ( $userdata->has_cap('manage_emailst_support_tickets') ) {
                        $classModifier1 = ' class="emailst_staff_reply_table" ';
                        $classModifier2 = ' class="emailst_staff_reply_thead" ';
                        $classModifier3 = ' class="emailst_staff_reply_tbody" ';
                    }
                    $theusersname = $user->display_name;
                } else {
                    $user = false; // Guest
                    $theusersname = __( 'Guest', 'email-support-tickets' );
                }

                echo '<br /><table style="width:100%;" '.$classModifier1.'>';
                echo '<thead '.$classModifier2.'><tr><th class="emailst_results_posted_by">'.__( 'Posted by', 'email-support-tickets' ).' '.$theusersname.' (<span class="emailst_results_timestamp">'.date('Y-m-d g:i A',$results['timestamp']).'</span>)</th></tr></thead>';
                $messageData = strip_tags(base64_decode($results['message']),'<p><br><a><br><strong><b><u><ul><li><strike><sub><sup><img><font>');
                $messageData = explode ( '\\', $messageData);
                $messageWhole = '';
                foreach ($messageData as $messagePart){
                $messageWhole .= $messagePart;	
                }
                echo '<tbody '.$classModifier3.'><tr><td class="emailst_results_message"><br />'.$messageWhole.'</td></tr>';
                echo '</tbody></table>';
            }
        }
        

        
    }
}

exit();

?>

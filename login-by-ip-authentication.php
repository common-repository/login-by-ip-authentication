<?php
/**
 * @package Login By IP Authentication
 * @version 0.1
 */
/*
Plugin Name: Login By IP Authentication
Plugin URI: 
Description: Login By IP Authentication allows Login from allowed IP addess(es) only.  
Author: Dotsquares
Version: 0.1
Author URI: https://www.dotsquares.com
*/

/*
Filter and Function to add extra field to allow admin to associate IPs in user's update profile page from admin panel. 
*/
add_action( 'show_user_profile', 'ds_lbia_extra_ip_fields' );
add_action( 'edit_user_profile', 'ds_lbia_extra_ip_fields' );

function ds_lbia_extra_ip_fields( $user ) { ?>
	<h3><?php _e("Profile Authentication Information", "blank"); ?></h3> 
	<table class="form-table">
		<tr>
			<th><label for="postalcode"><?php _e("Allowed IP Addresses"); ?></label></th>
			<td>
				<textarea id="next" rows="4" cols="40" placeholder="Enter IP address(es) sperated by comma(,)   Ex. ip1,Ip2,ip3 " name="allowedips" ><?php echo esc_attr( get_the_author_meta( 'allowedips', $user->ID ) ); ?></textarea>
				<br /><span class="description"><?php _e("If you want to allow multiple IP addresses, please add comma(,) seperated IPs. "); ?></span>
				<br /><span class="description"><?php _e("NOTE: If no IP is added, user will be able to login from any IP."); ?></span>
			</td>
		</tr>
	</table>
<?php 
}


/*
Filter and Function to add save associated IPs for user.
*/
add_action( 'personal_options_update', 'ds_lbia_save_extra_ip_fields' );
add_action( 'edit_user_profile_update', 'ds_lbia_save_extra_ip_fields' );

function ds_lbia_save_extra_ip_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	update_user_meta( $user_id, 'allowedips', $_POST['allowedips'] );
}
 

/*
Filter and Function to check if user is logging in from the allowed IP.
*/
add_filter('wp_authenticate_user', 'ds_lbia_check_ip_auth', 99, 2);
function ds_lbia_check_ip_auth($user,  $password){ 
	$currentIP =  ds_lbia_getuser_ip_addr();
	$usersIP = esc_attr( get_the_author_meta( 'allowedips', $user->ID ) );
	$allowed=array();
	if($usersIP!=""){
		$allowed = explode(',',$usersIP);
	}
	
	if(count($allowed)>0){	
		if (in_array($currentIP, $allowed)) {
			return $user;
		}else{
			return $user = new WP_Error('incorrect_password', __("ERROR: You are not authorised to login."));
		}
	} else {
		return $user;
	}
} 


/*
Function to get user's current IP.
*/
function ds_lbia_getuser_ip_addr(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)){
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    return $ip;
}
?>
<?php
/*
Plugin Name: Login with Instagram
Description: This plugin will add a Login with Instagram Button
Plugin URI: https://vicodemedia.com
Author: Victor Rusu
Version: 0.0.1
*/

// Don't access this file directly
defined('ABSPATH') or die();

/* Instagram App Client Id */
define('INSTAGRAM_CLIENT_ID', '');

/* Instagram App Client Secret */
define('INSTAGRAM_CLIENT_SECRET', '');

// require InstagramAuth Object
require_once 'InstagramAuth.php';

// create shortcode
add_shortcode('instagram-login', 'vm_login_with_insta');
function vm_login_with_insta(){
    // if(!is_user_logged_in()){
        if(!get_option('users_can_register')){
            return('Registration is closed!');
        }else{
            // https://api.instagram.com/oauth/authorize?client_id=881415336022771&redirect_uri=https://localhost/&scope=user_profile,user_media&response_type=code
            // $nonce = wp_create_nonce("vm_insta_login_nonce");
            $redirect_uri = 'https://localhost/';
            $data = "user_profile,user_media";
            $fullURL = 'https://api.instagram.com/oauth/authorize?client_id='.INSTAGRAM_CLIENT_ID.'&redirect_uri='.$redirect_uri.'&scope='.$data.'&response_type=code';
            return '
                <a href="'.$fullURL.'">Login With Instagram</a>
            ';
        }

        // when instagram redirected the user back
        if (isset($_GET['code'])) {
            echo "dsfsfsd";
            $insta_code = $_GET['code'];
            try {
                $instagram_C = new InstagramAuth();
                
                // Get the access token
                $access_token = $instagram_C->GetToken(INSTAGRAM_CLIENT_ID, $redirect_uri, INSTAGRAM_CLIENT_SECRET, $insta_code);
                // Get user information
                $user_info = $instagram_C->GetUserProfileInformation($access_token);
                var_dump($user_info['username']);
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
    
        }




    // }else{
    //     $current_user = wp_get_current_user();
    //     return 'Hi ' . $current_user->first_name . '! - <a href="/wp-login.php?action=logout">Log Out</a>';
    // }

}

// add action 'vicode_facebook_login' to admin-ajax.php file
// add_action('wp_ajax_vm_insta_login', 'vm_insta_login');
// function vm_insta_login(){

//     var_dump($_GET['code']);
    
    
// }

// // ALLOW LOGGED OUT users to access admin-ajax.php action
// function add_ajax_actions_insta(){
//     add_action('wp_ajax_nopriv_vm_insta_login', 'vm_insta_login');
// }
// add_action('admin_init', 'add_ajax_actions_insta');

// redirect user to home page after log out
function vm_redirect_after_logout(){
    wp_redirect(home_url());
    exit();
}
add_action('wp_logout', 'vm_redirect_after_logout');
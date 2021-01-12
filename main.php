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
define('INSTAGRAM_CLIENT_ID', '881415336022771');

/* Instagram App Client Secret */
define('INSTAGRAM_CLIENT_SECRET', 'a5b848859b4c90123e5f4ebad2fd877f');

/* Redirect URI */
define('REDIRECT_URI', 'https://localhost/vm-insta-login/');

// require InstagramAuth Object
require_once 'InstagramAuth.php';

// create shortcode
add_shortcode('instagram-login', 'vm_login_with_insta');
function vm_login_with_insta(){
    if(!is_user_logged_in()){
            // checking to see if the registration is opend
            if(!get_option('users_can_register')){
                return('Registration is closed!');
            }else{
                $data = "user_profile,user_media";
                $fullURL = 'https://api.instagram.com/oauth/authorize?client_id='.INSTAGRAM_CLIENT_ID.'&redirect_uri='.REDIRECT_URI.'&scope='.$data.'&response_type=code';
                return '
                    <a href="'.$fullURL.'">Login With Instagram</a>
                ';
            }

    }else{
        $current_user = wp_get_current_user();
        return 'Hi ' . $current_user->user_login . '! - <a href="/wp-login.php?action=logout">Log Out</a>';
    }
}



// add ajax action
add_action('wp_ajax_vm_login_insta', 'vm_login_insta');
function vm_login_insta(){
    // when instagram redirects the user back
    if (isset($_GET['code'])) {
        $insta_code = $_GET['code'];
        try {
            $instagram_C = new InstagramAuth();
            
            // Get the access token
            $access_token = $instagram_C->GetToken(INSTAGRAM_CLIENT_ID, REDIRECT_URI, INSTAGRAM_CLIENT_SECRET, $insta_code);
            // Get user information
            $user_info = $instagram_C->GetUserProfileInformation($access_token);
            // var_dump($user_info['username']);

            // check if username already registered
            if(!username_exists($user_info['username'])){

                // generate password
                $bytes = openssl_random_pseudo_bytes(2);
                $password = md5(bin2hex($bytes));
                
                $new_user_id = wp_insert_user(array(
                    'user_login'        => $user_info['username'],
                    'user_pass'         => $password,
                    // 'user_email'        => $user_email,
                    'user_registered'   => date('Y-m-d H:i:s'),
                    'role'              => 'subscriber'
                ));
        
                if($new_user_id){
                    // send an email
                    wp_new_user_notification($new_user_id);
        
                    // log the new user in
                    do_action('wp_login', $new_user_id, $user_info['username']);
                    wp_set_current_user($new_user_id);
                    wp_set_auth_cookie($new_user_id, true);
        
                    // send the newly created user to the home page
                    wp_redirect(home_url()); exit;
                }
            }else{
                // if user already registered
                $user = get_user_by('login', $user_info['username']);
                do_action('wp_login', $user->ID, $user->user_login);
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, true);
                wp_redirect(home_url()); exit;
            }



        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
}


// ALLOW LOGGED OUT users to access admin-ajax.php action
function add_insta_ajax_actions(){
    add_action('wp_ajax_nopriv_vm_login_insta', 'vm_login_insta');
}
add_action('admin_init', 'add_insta_ajax_actions');







// redirect user to home page after log out
function vm_redirect_after_logout(){
    wp_redirect(home_url());
    exit();
}
add_action('wp_logout', 'vm_redirect_after_logout');
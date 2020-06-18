<?php
/**
 * Newsletters.lk
 * WordPress Plugin
 * Version 1.4 LTS
 * 
 */

add_action( 'admin_menu', 'add_menu_admin' );
function add_menu_admin(){
    $page_title = 'Newsletters.lk - SMS';   
    $menu_title = 'Newsletters.lk - SMS';   
    $capability = 'manage_options';   
    $menu_slug  = 'newsletters.lk';   
    $function   = 'newsletters.lk';   
    $icon_url   = 'dashicons-format-status';  
    add_menu_page( $page_title,$menu_title,$capability,$menu_slug,null,$icon_url);
}

?>
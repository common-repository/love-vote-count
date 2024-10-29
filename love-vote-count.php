<?php
/*
Plugin Name: Love Vote Count
Plugin URI: http://wordpress.org/plugins/love-vote-count/
Description: A voting plugin with love ( heart ) symbol for WordPress. There are 2 level of protection algorithm against fake voters.
Author: Ravendra Patel | @pawan1085
Version: 1.0.2
Author URI: http://www.reinforcewebsol.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$domain = $_SERVER['HTTP_HOST'];
$options = array( 'admin_email' => get_option( 'admin_email' ) );
/*-----------------------------------------------------------------------------------*/
/*
/* Define the URL and DIR path */
/*
/*-----------------------------------------------------------------------------------*/

define('lvc_voting_url', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
define('lvc_voting_path', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );

$dir = dirname(__FILE__);

/*-----------------------------------------------------------------------------------*/
/*
/* Encue the Scripts for the Ajax call */
/*
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'lvc_voting_scripts' ) ): 
	
	function lvc_voting_scripts()
	{
		wp_enqueue_script('lvc_voting_scripts', lvc_voting_url . '/js/general.js', array('jquery'), '4.0.1');
		
		wp_localize_script( 'lvc_voting_scripts', 'lvc_voting_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
	add_action('wp_enqueue_scripts', 'lvc_voting_scripts');

endif;


/*-----------------------------------------------------------------------------------*/
/*
/* Encue the Styles for the LVC Voting */
/*
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'lvc_voting_styles' ) ): 
	
	function lvc_voting_styles()  
	{ 
	   
	    wp_register_style( "lvc_voting_styles",  lvc_voting_url . '/css/style.css' , "", "1.0.0");
	    wp_enqueue_style( 'lvc_voting_styles' );
	}
	add_action('wp_enqueue_scripts', 'lvc_voting_styles');	

endif;

/*-----------------------------------------------------------------------------------*/
/*
/* Require our php ajax call */
/*
/*-----------------------------------------------------------------------------------*/

require_once("$dir/functions.php");

/*-----------------------------------------------------------------------------------*/
/*
/* Add User IP Tag */  
/*
/*-----------------------------------------------------------------------------------*/

function lvc_user_ip () {
	$user_ip_output .='<meta name="ip" content="' . $_SERVER["REMOTE_ADDR"] . '">';
	echo $user_ip_output;
}

add_action('wp_head', 'lvc_user_ip');

/*-----------------------------------------------------------------------------------*/
/*
/*	 Echo vote button */
/* 	 <?=function_exists('lvc_love_button') ? lvc_love_button() : ''?> */
/*
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'lvc_love_button' ) ) :
	
	// Allows you to specify a specific POST_ID
	function lvc_love_button ( $post_ID = '' ) {
		
		$post_ID = intval( sanitize_text_field( $post_ID ) );
		
		if( $post_ID == '' ) $post_ID = get_the_ID();
		
		
		$nonce = wp_create_nonce("lvc_voting_nonce");
		$votes = get_post_meta($post_ID, "_lvc_votes", true);
		$votes = ($votes == "") ? 0 : $votes;
		
		
		$lvc_voting_link 	.= '<div id="vote_counter">';
		$lvc_voting_link 	.= '<button class="vote-button" data-post_id="' . $post_ID . '" data-nonce="'. $nonce .'"><i class="icon-heart"></i><span class="vote-count">' . $votes . '</span></button>';
		$lvc_voting_link		.= '<span class="already-voted">You have already voted.</span>';
		$lvc_voting_link 	.= '</div>';
		
		return $lvc_voting_link;
		
		
	}

endif;

/*-----------------------------------------------------------------------------------*/
/*
/* Top Votes Shortcode [lvc_show_loved_votes] */
/*
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'lvc_show_loved_votes_func' ) ): 	
	function lvc_show_loved_votes_func( $atts ) {
	
		$return = '';
	
		// Parameters accepted
		
		extract( shortcode_atts( array(
			'posts_per_page' => 5,
			'category' => '',
			'show_votes' => 'yes',
			'post_type' => 'any',
		), $atts ) );
		
		// Check wich meta_key the user wants
		$meta_key = '_lvc_votes';
		$sign = "+";
		
		// Build up the args array
	
	    $args = array (
	    	'post_type' 			 => $post_type,
			'post_status'            => 'publish',
			'cat'                    => $category,
			'pagination'             => false,
			'posts_per_page'         => $posts_per_page,
			'cache_results'          => true,
			'meta_key'	=> $meta_key,
			'orderby'	=> 'meta_value_num'
		);
		
		// Get the posts
				
		$lvc_top_votes_query = new WP_Query($args);
		
		// Build the post list
		
		if($lvc_top_votes_query->have_posts()) : 		
			
			$return .= '<ol class="lvc_voting_top_list">';
			
			while($lvc_top_votes_query->have_posts()){
			
				$lvc_top_votes_query->the_post(); 
			
				$return .= '<li>';
				
				$return .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';				
				
					// Get the votes
	
					$meta_values = get_post_meta(get_the_ID(), $meta_key);
									
					// Add the votes to the HTML				
	
						$return .= '<span>(' . $sign;
	
						if( sizeof($meta_values) > 0){
	
							$return .= $meta_values[0];	
	
						}else{
	
							$return .= "0";	
						}						
	
						$return .= ')</span>';
							
					}
				
				$return .= '</li>';					
	
			
			$return .= '</ol>';
			
			// Reset the post data or the sky will fall
			
			wp_reset_postdata();
			
		endif; 
		
		return $return;
	}
	
	add_shortcode( 'lvc_show_loved_votes', 'lvc_show_loved_votes_func' );
endif;

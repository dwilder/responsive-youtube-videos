<?php
/*
Plugin Name: Responsive Youtube Videos
Plugin URI: http://liddweaver.com
Description: Use the shortcode [youtube url="..."] or [youtube]url[/youtube] to make your embedded YouTube videos as responsive as your theme.
Version: 1.0
Author: liddweaver
Author URI: http://liddweaver.com
License: GPLv2
*/

// Look at the content to see if the shortcode is present.
add_action( 'wp', 'lidd_youtube_detect_shortcode' );

function lidd_youtube_detect_shortcode() {
	global $post;
	
	$pattern = get_shortcode_regex();
	
	// Check the content.
	if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches )
		&& array_key_exists( 2, $matches ) 
		&& ( in_array( 'youtube', $matches[2] ) || in_array( 'yt', $matches[2] ) ) ) {
		
		// The shortcode is being used, so include the stylesheet.
		wp_enqueue_style( 'lidd_youtube', plugin_dir_url( __FILE__ ) . 'css/style.css', '', '1.0', 'screen' );
		
	}
	
}

// Add the shortcode to make it happen.
add_shortcode( 'youtube', 'lidd_youtube_shortcode' );
add_shortcode( 'yt', 'lidd_youtube_shortcode' );

// Create the shortcode function.
function lidd_youtube_shortcode( $attr, $content ) {
	
	// Set the 'url' attribute
	// Set the 'rel' attribute
	
	// Possible keys for aspect ratio
	$aspectratio = array( 'aspectratio', 'ar', 'ratio' );
	// Possible keys for related 'rel'
	$related = array( 'rel', 'related', 'showrelated' );
	foreach( $attr as $k => $v ) {
		if ( in_array( $k, $aspectratio ) ) {
			$attr['aspectratio'] = $v;
		}
		if ( in_array( $k, $related ) ) {
			$attr['rel'] = $v;
		}
	}
	
	// Set some default attributes.
	$defaults = array(
		'url' => null,
		'aspectratio' => 'widescreen',
		'rel' => 1
	);
	
	// Get the attributes.
	$values = wp_parse_args( $attr, $defaults );
	
	// If there's no URL value, then look for it in the content.
	if ( !$values['url'] ) {
		$values['url'] = $content ? $content : null;
	}
	
	// Validate everything.
	$query= lidd_youtube_parse_url( $values['url'] );
	
	// If it returned false, then return nothing.
	if ( !$values ) {
		return '';
	}
	
	
	// Construct the HTML to return.
	$html = '<div class="lidd-youtube-';
	
	// Check the aspect ratio.
	switch( $values['aspectratio'] ) {
		case 'fullscreen':
		case 'full':
		case '4:3':
			$html .= 'fullscreen';
			break;
		default:
			$html .= 'widescreen';
			break;
	}
	
	$html .= '">';
	
	// Add the embed code.
	$html .= lidd_youtube_embed_code( $query, $values['rel'] );
	
	// Close the div.
	$html .= '</div>';
	
	return $html;
	
}


// Function to generate a YouTube embed tag.
function lidd_youtube_embed_code( $query, $rel ) {
	
	$input = '<iframe src="//www.youtube.com/embed/' . esc_attr( $query );
	// Possible 'rel' values.
	$rel_values = array( '0', 'no', 'hide' );
	$input .= ( in_array( $rel, $rel_values ) ) ? '?rel=0' : '';
	$input .= '" frameborder="0"></iframe>';
	
	return $input;
	
}

// A function to parse the YouTube url for the query string.
function lidd_youtube_parse_url( $url ) {
	
	// Parse the url to get the domain and query string
	$parts = parse_url( $url );
	// Assume YouTube for now
	preg_match(
		'/^v=([^\\?\\&]+)/',
		$parts['query'],
		$query
	);
	return $query[1];
}
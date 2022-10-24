<?php
/**
 * Plugin Name: MetaShortcode Plugin
 * Plugin URI: https://rsm0128.wordpress.com/
 * Description: This plugin is to render meta value on frontend
 * Version: 1.1
 * Author: rsm0128
 * Author URI: https://rsm0128.wordpress.com/
 * Text Domain: mshortcode
 *
 * @package MetaShortcode
 */

/**
 * Shortcode renderer.
 * [meta_value name="logo" type="image" index="0" ]
 *
 * @param array  $atts    Attributes.
 * @param string $content Shortcode content.
 */
function ms_shortcode( $atts, $content ) {
	$meta_val = '';
	if ( isset( $atts['name'] ) ) {
		$post_id = get_the_ID();
		$key_str = trim( $atts['name'] );
		$index   = isset( $atts['index'] ) ? $atts['index'] : 0;

		$keys     = explode( '.', $key_str );
		$meta_val = get_post_meta( $post_id, $keys[0] );
		if ( empty( $meta_val ) ) {
			return '';
		}

		$meta_val = $meta_val[ $index ];

		unset( $keys[0] );

		foreach ( $keys as $key ) {
			if ( is_array( $meta_val ) && isset( $meta_val[ $key ] ) ) {
				$meta_val = $meta_val[ $key ];
			} else {
				$meta_val = '';
				break;
			}
		}

		// Image case return full img html.
		if ( isset( $atts['type'] ) && 'image' === $atts['type'] ) {
			$meta_val = ms_get_image_from_id( $meta_val );
		}
	}

	return $meta_val;
}
add_shortcode( 'meta_value', 'ms_shortcode' );

/**
 * User shortcode renderer.
 * [user_meta user_id=1234 name="first"]
 *
 * @param array  $atts    Attributes.
 * @param string $content Shortcode content.
 */
function ms_user_shortcode( $atts, $content ) {
	$meta_val = '';
	$user_id  = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();

	if ( $user_id && isset( $atts['name'] ) ) {
		switch ( $atts['name'] ) {
			case 'id':
				$user_data = get_user_by( 'id', $user_id );
				$meta_val  = $user_data->ID;
				break;
			case 'email':
				$user_data = get_user_by( 'id', $user_id );
				$meta_val  = $user_data->user_email;
				break;
			default:
				$key_str  = trim( $atts['name'] );
				$keys     = explode( '.', $key_str );
				$meta_val = get_user_meta( $user_id, $keys[0], true );
				unset( $keys[0] );

				foreach ( $keys as $key ) {
					if ( is_array( $meta_val ) && isset( $meta_val[ $key ] ) ) {
						$meta_val = $meta_val[ $key ];
					} else {
						$meta_val = '';
						break;
					}
				}
		}
	}

	return $meta_val;
}
add_shortcode( 'user_meta', 'ms_user_shortcode' );

/**
 * Get image html from image id.
 *
 * @param array $ids  ID.
 * @param array $atts Image attributes.
 * @return string
 */
function ms_get_image_from_id( $ids, $atts = array() ) {
	if ( ! is_array( $ids ) ) {
		$ids = array( $ids );
	}

	$image_size = isset( $atts['size'] ) ? $atts['size'] : 'full';
	$image_html = array();
	foreach ( $ids as $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, $image_size );
		if ( $url ) {
			$image_html[] = sprintf( '<img src="%s">', $url );
		}
	}

	return implode( $image_html );
}

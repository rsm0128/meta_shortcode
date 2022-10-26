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
	if ( ! isset( $atts['name'] ) ) {
		return '';
	}

	$return_val = array();
	$separator  = ', ';

	$post_id    = get_the_ID();
	$key_str    = trim( $atts['name'] );
	$field_type = isset( $atts['type'] ) ? $atts['type'] : '';
	$index      = isset( $atts['index'] ) ? $atts['index'] : false;

	$keys     = explode( '.', $key_str );
	$meta_arr = get_post_meta( $post_id, $keys[0] );
	if ( empty( $meta_arr ) ) {
		return '';
	}
	unset( $keys[0] );

	foreach ( $meta_arr as $meta_arr_val ) {
		$tmp = $meta_arr_val;
		foreach ( $keys as $key ) {
			if ( is_array( $tmp ) && isset( $tmp[ $key ] ) ) {
				$tmp = $tmp[ $key ];
			} else {
				$tmp = '';
				break;
			}
		}

		$tmp = ms_filter_value_by_type( $tmp, $field_type );

		$return_val[] = $tmp;
	}

	if ( $field_type === 'image' ) {
		$separator = '';
	}

	if ( count( $return_val ) > 1 && $index ) {
		$return_val = $return_val[ $index - 1 ];
	} else {
		$return_val = join( $separator, $return_val );
	}

	return $return_val;
}
add_shortcode( 'meta_value', 'ms_shortcode' );

/**
 * Filter value by filed type.
 *
 * @param any    $value Value to filter.
 * @param string $type  Field type.
 * @return any
 */
function ms_filter_value_by_type( $value, $type ) {
	switch ( $type ) {
		case 'image':
		case 'gallery':
			$value = sprintf( '<img src="%s">', wp_get_attachment_image_url( $value, 'full' ) );
			break;
		case 'terms':
			$term = get_term( $value );
			if ( $term && ! is_wp_error( $term ) ) {
				$value = $term->name;
			} else {
				$value = 'Wrong term id';
			}
			break;
		case 'map':
			break;
		case 'file':
			break;

	}

	return $value;
}

/**
 * User shortcode renderer.
 * [user_meta user_id=1234 name="first"]
 *
 * @param array  $atts    Attributes.
 * @param string $content Shortcode content.
 */
function ms_user_shortcode( $atts, $content ) {
	$return_val = '';
	$user_id    = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();

	if ( $user_id && isset( $atts['name'] ) ) {
		switch ( $atts['name'] ) {
			case 'id':
				$user_data  = get_user_by( 'id', $user_id );
				$return_val = $user_data->ID;
				break;
			case 'email':
				$user_data  = get_user_by( 'id', $user_id );
				$return_val = $user_data->user_email;
				break;
			default:
				$key_str    = trim( $atts['name'] );
				$keys       = explode( '.', $key_str );
				$return_val = get_user_meta( $user_id, $keys[0], true );
				unset( $keys[0] );

				foreach ( $keys as $key ) {
					if ( is_array( $return_val ) && isset( $return_val[ $key ] ) ) {
						$return_val = $return_val[ $key ];
					} else {
						$return_val = '';
						break;
					}
				}
		}
	}

	return $return_val;
}
add_shortcode( 'user_meta', 'ms_user_shortcode' );

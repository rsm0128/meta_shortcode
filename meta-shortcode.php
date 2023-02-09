<?php
/**
 * Plugin Name: Meta Shortcode Plugin
 * Plugin URI: https://github.com/rsm0128/rcp-user-listing/
 * Description: This plugin is to render meta value on frontend
 * Version: 1.3
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
	$return_str = '';
	$separator  = ', ';

	$post_id    = isset( $atts['post_id'] ) ? $atts['post_id'] : get_the_ID();
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

		$tmp = ms_filter_value_by_type( $tmp, $field_type, $atts );

		$return_val[] = $tmp;
	}

	if ( 'image' === $field_type ) {
		$separator = '';
	}

	if ( count( $return_val ) > 1 && $index ) {
		$return_str = $return_val[ $index - 1 ];
	} else {
		$return_str = join( $separator, $return_val );
	}

	return $return_str;
}
add_shortcode( 'meta_value', 'ms_shortcode' );

/**
 * Filter value by filed type.
 *
 * @param string $value Value to filter.
 * @param string $type  Field type.
 * @param array  $args  Arguments.
 * @return string
 */
function ms_filter_value_by_type( $value, $type, $args = array() ) {
	if ( empty( $value ) ) {
		return $value;
	}

	switch ( $type ) {
		case 'image':
			if ( is_numeric( $value ) ) {
				$value = sprintf( '<img src="%s">', wp_get_attachment_image_url( $value, 'full' ) );
			}
			break;
		case 'terms':
			$terms = get_terms(
				array(
					'include'    => array_map( 'intval', explode( ',', $value ) ),
					'hide_empty' => false,
					'fields'     => 'names',
				)
			);
			if ( $terms && ! is_wp_error( $terms ) ) {
				$value = implode( ', ', $terms );
			} else {
				$value = 'Wrong term id';
			}
			break;
		case 'map':
			break;
		case 'file':
			if ( is_numeric( $value ) ) {
				$value = wp_get_attachment_url( $value );
			}
			break;
		case 'pdf':
			if ( is_numeric( $value ) ) {
				$value = wp_get_attachment_url( $value );

				$attribute_str = '';
				if ( isset( $args['width'] ) ) {
					$attribute_str .= ' width=' . $args['width'];
				}
				if ( isset( $args['height'] ) ) {
					$attribute_str .= ' height=' . $args['height'];
				}
				return sprintf( '<iframe %s src="%s"  frameborder="0"></iframe>', $attribute_str, $value );
			}
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
	$return_val = array();
	$return_str = '';
	$user_id    = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();
	$key_str    = trim( $atts['name'] );
	$field_type = isset( $atts['type'] ) ? $atts['type'] : '';
	$index      = isset( $atts['index'] ) ? $atts['index'] : false;

	if ( empty( $user_id ) || ! isset( $atts['name'] ) ) {
		return '';
	}

	switch ( $key_str ) {
		case 'id':
			$user_data  = get_user_by( 'id', $user_id );
			$return_str = $user_data->ID;
			break;
		case 'email':
			$user_data  = get_user_by( 'id', $user_id );
			$return_str = $user_data->user_email;
			break;
		default:
			$keys     = explode( '.', $key_str );
			$meta_arr = get_user_meta( $user_id, $keys[0] );
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

				$tmp = ms_filter_value_by_type( $tmp, $field_type, $atts );

				$return_val[] = $tmp;
			}

			if ( 'image' === $field_type ) {
				$separator = '';
			}

			if ( count( $return_val ) > 1 && $index ) {
				$return_str = $return_val[ $index - 1 ];
			} else {
				$return_str = join( $separator, $return_val );
			}
	}

	return $return_str;
}
add_shortcode( 'user_meta', 'ms_user_shortcode' );

/**
 * Author meta shortcode renderer.
 * [author_meta name="first"]
 *
 * @param array  $atts    Attributes.
 * @param string $content Shortcode content.
 */
function ms_author_meta_shortcode( $atts, $content ) {
	$return_val = array();
	$return_str = '';
	global $authordata;
	$key_str    = trim( $atts['name'] );
	$field_type = isset( $atts['type'] ) ? $atts['type'] : '';
	$index      = isset( $atts['index'] ) ? $atts['index'] : false;

	$author = $authordata ? $authordata : get_the_author();

	if ( empty( $author ) || empty( $author->ID ) || ! isset( $atts['name'] ) ) {
		return '';
	}

	switch ( $key_str ) {
		case 'id':
			$return_str = $author->ID;
			break;
		case 'email':
			$return_str = $author->user_email;
			break;
		default:
			$keys     = explode( '.', $key_str );
			$meta_arr = get_user_meta( $author->ID, $keys[0] );
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

				$tmp = ms_filter_value_by_type( $tmp, $field_type, $atts );

				$return_val[] = $tmp;
			}

			if ( 'image' === $field_type ) {
				$separator = '';
			} else {
				$separator = ', ';
			}

			if ( count( $return_val ) > 1 && $index ) {
				$return_str = $return_val[ $index - 1 ];
			} else {
				$return_str = join( $separator, $return_val );
			}
	}

	return $return_str;
}
add_shortcode( 'author_meta', 'ms_author_meta_shortcode' );

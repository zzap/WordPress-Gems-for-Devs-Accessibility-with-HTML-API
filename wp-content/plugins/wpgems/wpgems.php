<?php
/**
 * Plugin Name:     WPGems
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Fix inaccessible maktup.
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     wpgems
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wpgems
 */

// Your code starts here.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'render_block', 'wpgems_render_block', 10, 2 );

function wpgems_render_block( string $block_content, array $block ): string {

	/**
	 * Inaccessible image block
	 * 
	 * Finds image in block's markup, 
	 * and adds an empty 'alt' attribute.
	 */
	if ( 'create-block/inaccessible-image' === $block['blockName'] ) {
		$processor = new WP_HTML_Tag_Processor( $block_content );

		// We know there's only one image in this block.
		// If we didn't know that, we would use 'while' loop here.
		if ( $processor->next_tag( 'img' ) ) {
			$processor->set_attribute( 'alt', '' );
		}

		return $processor->get_updated_html();
	}

	/**
	 * Inaccessible form block
	 * 
	 * Finds form fields in block's markup, and:
	 * 1. adds 'id' attribute if it's not present (uses 'name' attribute value)
	 * 2. finds labels without 'for' attribute, get's their closest form
	 *    field's 'id' attribute and sets its value as label's 'for' attribute.
	 */
	if ( 'create-block/inaccessible-form' === $block['blockName'] ) {
		// List all the form fields in inaccessible block we want to work with.
		$form_fields = [ 'INPUT', 'SELECT' ];

		// Start the first pass.
		$processor = new WP_HTML_Tag_Processor( $block_content );

		// Make sure all form fields have ID attribute.
		while ( $processor->next_tag() ) {
			$tag = $processor->get_tag(); 

			// If the tag is <input> or <select>.
			if ( in_array( $tag, $form_fields, true ) ) {
				$id = $processor->get_attribute( 'id' );

				if ( ! $id ) {
					// Set tag's 'name' attribute's value as its 'id' attribute value.
					// It's worth checking if the name exists here, also sanitize it.
					$processor->set_attribute( 'id', $processor->get_attribute( 'name' ) );
				}
			}
			// Make sure we get this updated HTML BEFORE
			// we start looking for labels below.
			$block_content = $processor->get_updated_html();
		}

		// Get a new instance with updated HTML so we are sure
		// that all form fields have 'id' attribute.
		$processor = new WP_HTML_Tag_Processor( $block_content );
		// Start labels count.
		$count = 0;

		while ( $processor->next_tag( 'LABEL' ) ) {
			$for = $processor->get_attribute( 'for' );

			if ( $for ) {
				continue;
			}
			// Remember the place where label is, so we can come back
			// and add 'for' attribute to it.
			$bookmark_name = 'bookmark-' . $count++;
			$bookmark      = $processor->set_bookmark( $bookmark_name );
			
			while ( $processor->next_tag() ) {
				$next_tag = $processor->get_tag(); 

				// If the closest next tag is one of our form fields.
				if ( in_array( $next_tag, $form_fields, true ) ) {
					$id = $processor->get_attribute( 'id' );
					
					if ( $id ) {
						// Find that bookmarked label and set
						// 'for' attribute to it.
						$processor->seek( $bookmark_name );
						$processor->set_attribute( 'for', $id );
					}
					// The job for this label is done.
					break;                
				}
			}
		}

		return $processor->get_updated_html();
	}

	return $block_content;
}
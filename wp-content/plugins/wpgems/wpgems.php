<?php
/**
 * Plugin Name:     WPGems
 * Plugin URI:      https://github.com/zzap/WordPress-Gems-for-Devs-Accessibility-with-HTML-API/
 * Description:     Fix inaccessible markup.
 * Author:          Milana Cap
 * Author URI:      https://developerka.org
 * Text Domain:     wpgems
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WPGems
 */

// Your code starts here.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

<?php
/**
 * Plugin Name:     WPGems
 * Plugin URI:      https://github.com/zzap/WordPress-Gems-for-Devs-Accessibility-with-HTML-API/
 * Description:     Fix inaccessible markup.
 * Author:          Milana Cap
 * Author URI:      https://developerka.org
 * Text Domain:     wpgems
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WPGems
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
     * THE LONG WAY WITH DUPLICATED CODE
     * 
     * Inaccessible form block 
     * 
     * Finds form fields in block's markup, and:
     * 1. adds 'id' attribute if it's not present (uses 'name' attribute value)
     * 2. finds labels without 'for' attribute, get's their closest form
     *    field's 'id' attribute and sets its value as label's 'for' attribute.
    */
    if ( 'create-block/inaccessible-form' === $block['blockName'] ) {
        // define form fields we want to work with
        $form_fields = ['INPUT', 'SELECT'];

        // initialise the processor and pass the block content to it
        $processor = WP_HTML_Processor::create_fragment( $block_content );

        // if nothing is returned by processor, return block content
        if ( ! $processor ) {
            return $block_content;
        }

        // loop through block content and look into every tag
        while ( $processor->next_tag() ) {
            // in HTML_Processor, every tag means closing tags as well, and we want to skip those
            if ( $processor->is_tag_closer() ) {
                continue;
            }
            // get the tag name
            $tag = $processor->get_tag();
            // if the found tag is not one of our defined form fields, continue
            if ( ! in_array( $tag, $form_fields, true) ) {
                continue;
            }

            // here we have all the INPUTs and SELECTs, 
            // and we are looking at their ID attributes         
            $id = $processor->get_attribute( 'id' );
            
            // if they have ID attributes, and they are not empty, continue
            if ( is_string( $id ) && '' !== $id ) {
                continue;
            }

            // here we have all the INPUTs and SELECTs without ID attributes, 
            // and we are looking at their NAME attributes
            $name = $processor->get_attribute( 'name' );
            
            // if the NAME attribute is found
            if ( $name ) {
                // we set the NAME attribute value as the ID values 
                $processor->set_attribute( 'id', $name );       
            }
        }

        // when we get out of the loop, 
        // we check if there are any errors in modified HTML,
        if ( $processor->get_last_error() ) {
            // in which case we return the block content
            return $block_content;
        }
        // we set the updated HTML to be the block content
        $block_content = $processor->get_updated_html();

        // start the second pass
        $processor = WP_HTML_Processor::create_fragment( $block_content );
        
        // if nothing is returned by processor, return block content
        if ( ! $processor ) {
            return $block_content;
        }
        
        // loop through updated block content and look into every LABEL tag
        while ( $processor->next_tag( 'LABEL' ) ) {
            // in HTML_Processor, every tag means closing tags as well, and we want to skip those
            if ( $processor->is_tag_closer() ) {
                continue;
            }
            // here we have all opening LABEL tags
            // and we are looking at their FOR attributes
            $for = $processor->get_attribute( 'for' );
            
            // if they have FOR attributes, and they are not empty, continue
            if ( is_string( $for ) && '' !== $for ) {
                continue;
            }

            // here we have our LABEL without FOR attribute, 
            // and we want to set the bookmark
            $processor->set_bookmark( 'label' );
                // here you can check the depth with get_current_depth() method, but we're going to skip it

            // initiate temp var for storing ID values
            $target_id = null;

            // now we loop through all the next tags, looking for form fields, closest to the LABEL
            while ( $processor->next_tag() ) {
                // in HTML_Processor, every tag means closing tags as well, and we want to skip those
                if ( $processor->is_tag_closer() ) {
                    continue;
                }

                // get the tag name
                $tag = $processor->get_tag();

                // if the found tag is not one of our defined form fields, continue
                if ( in_array( $tag, $form_fields, true) ) {

                    // here we are at the INPUT or SELECT tag that is closest to the LABEL without FOR attr
                    // and we want its ID attribute
                    $id = $processor->get_attribute( 'id' );

                    // if they have ID attributes, and they are not empty, 
                    // save its value into temp var 
                    if ( is_string( $id ) && '' !== $id ) {
                        $target_id = $id;
                    }
                    break;

                }
            }

            // when we get out of the loop, check if we have any string value set for temp var
            if ( $target_id ) {
                // here we are inside INPUT or SELECT tag which we will connect to prepended LABEL
                // and we want to set another bookmark here, to resume processing after matching with the LABEL
                $processor->set_bookmark( 'resume' );

                // now we want to go back to previous bookmark
                $processor->seek( 'label' );
                // and set the value for LABEL's FOR attribute from the temp var
                $processor->set_attribute( 'for', $target_id );

                // now we want to jump back to the INPUT or SELECT tag from where we already took the ID
                // and continue the search for next label
                $processor->seek( 'resume' );
            }
        }

        // when we get out of the LABELs loop, 
        // we check if there are any errors in modified HTML,
        if ( $processor->get_last_error() ) {
            // in which case we return the block content
            return $block_content;
        }
        // we set the updated HTML to be the block content
        $block_content = $processor->get_updated_html();
    }
    
	return $block_content;
}
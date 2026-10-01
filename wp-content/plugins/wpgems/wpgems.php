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

    if ( $block['blockName'] === 'create-block/inaccessible-image' ) {
        $tags = new WP_HTML_Tag_Processor( $block_content );

        if ( $tags->next_tag( 'IMG' ) ) {
            $tags->set_attribute( 'alt', '' );
        }

        $block_content = $tags->get_updated_html();
    }

    if ( $block['blockName'] === 'create-block/inaccessible-form' ) {
        $tags = new WP_HTML_Tag_Processor( $block_content );
        $form_fields = [ 'INPUT', 'SELECT' ];
        $count = 0;
        while ( $tags->next_tag( 'LABEL' ) ) {
            $for = $tags->get_attribute( 'for' );

            if ( $for ) {
                continue;
            }
            // LABELS without 'for' attr
            $bookmark_name = 'label-' . $count++;
            $tags->set_bookmark( $bookmark_name );

            while ( $tags->next_tag() ) {
                $tag = $tags->get_tag();

                if ( in_array( $tag, $form_fields, true ) ) {
                    // INPUT or SELECT
                    $name = $tags->get_attribute( 'name' );

                    if ( $name ) {
                        $tags->set_attribute( 'id', $name );
                        $tags->seek( $bookmark_name );
                        $tags->set_attribute( 'for', $name );
                    }
                    break;
                }
            }  
        }
        $block_content = $tags->get_updated_html();
    }

    return $block_content;
}
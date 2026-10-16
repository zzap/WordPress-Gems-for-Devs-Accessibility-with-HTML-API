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

function wpgems_render_block( string $block_content, array $block ):string {

    if ( $block['blockName'] === 'create-block/inaccessible-image' ) {
        $tags = new WP_HTML_Tag_Processor( $block_content );
        if ( $tags->next_tag( 'IMG' ) ) {
            $tags->set_attribute( 'alt', '' );
        }
        return $tags->get_updated_html();
    }
    
    if ( $block['blockName'] === 'create-block/inaccessible-form' )  {
        $form_fields = [ 'INPUT', 'SELECT' ];

        // $processor = WP_HTML_Processor::create_fragment( $block_content );

        // if ( ! $processor ) {
        //     return $block_content;
        // }

        // while ( $processor->next_tag() ) {
        //     if ( $processor->is_tag_closer() ) {
        //         continue;
        //     }

        //     $tag = $processor->get_tag();
        //     if ( ! in_array( $tag, $form_fields, true ) ) {
        //         continue;
        //     }
        //     // opening INPUT or SELECT
        //     $id = $processor->get_attribute( 'id' );
        //     if ( is_string( $id ) && '' !== $id ) {
        //         continue;
        //     }
        //     // opening INPUT or SELECT without ID
        //     $name = $processor->get_attribute( 'name' );
        //     if ( $name ) {
        //         $processor->set_attribute( 'id', $name );
        //     }            
        // } // while

        // if ( $processor->get_last_error() ) {
        //     return $block_content;
        // }

        // $block_content = $processor->get_updated_html();
        
        $processor = WP_HTML_Processor::create_fragment( $block_content );

        if ( ! $processor ) {
            return $block_content;
        }

        
        while ( $processor->next_tag( 'LABEL' ) ) {
            if ( $processor->is_tag_closer() ) {
                continue;
            }
            // opening LABEL
            $for = $processor->get_attribute( 'for' );
            if ( is_string( $for ) && '' !== $for ) {
                continue;
            }
            // opening LABEL without FOR
            $processor->set_bookmark( 'label' );
            $field_id = null;

            while ( $processor->next_tag() ) {
                if ( $processor->is_tag_closer() ) {
                    continue;
                }

                $tag = $processor->get_tag();
                if ( ! in_array( $tag, $form_fields, true ) ) {
                    continue;
                }
                // opening INPUT or SELECT
                $id = $processor->get_attribute( 'id' );
                if ( is_string( $id ) && '' !== $id ) {
                    $field_id = $id;
                } elseif ( ! is_string( $id ) || '' === $id ) {
                    $name = $processor->get_attribute( 'name' );
                    if ( $name ) {
                        $processor->set_attribute( 'id', $name );
                        $field_id = $name;
                    } 
                }
                break;  
            } // while

            if ( $field_id ) {
                $processor->set_bookmark( 'resume' );
                $processor->seek( 'label' );
                $processor->set_attribute( 'for', $field_id );
                $processor->seek( 'resume' );
            }
        } // while

        if ( $processor->get_last_error() ) {
            return $block_content;
        }

        $block_content = $processor->get_updated_html();

    }
    return $block_content;
}
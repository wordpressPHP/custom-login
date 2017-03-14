<?php

namespace PassyCo\CustomLogin;

/**
 * Class Templates
 *
 * @package PassyCo\CustomLogin
 */
class Templates extends AbstractStaticLogin {

    const TEMPLATES_DIR = 'templates';

    /**
     * Returns the path to the CL templates directory
     *
     * @since 2.0
     *
     * @return string
     */
    public static function getTemplatesDir() {
        return self::getDir() . self::TEMPLATES_DIR;
    }

    /**
     * Returns the URL to the CL templates directory
     *
     * @since 2.0
     *
     * @return string
     */
    public static function getTemplatesUrl() {
        return self::getUrl() . self::TEMPLATES_DIR;
    }

    /**
     * Retrieves a template part
     *
     * @since 2.0
     *
     * @param string $slug
     * @param string $name Optional. Default null
     * @param bool $load
     *
     * @uses custom_login_locate_template()
     * @uses load_template()
     * @uses get_template_part()
     *
     * @return string
     */
    public static function getTemplatePart( $slug, $name = null, $load = true ) {
        // Execute code for this part
        do_action( 'get_template_part_' . $slug, $slug, $name );

        // Setup possible parts
        $templates = [];
        if ( isset( $name ) ) {
            $templates[] = $slug . '-' . $name . '.php';
        }
        $templates[] = $slug . '.php';

        // Allow template parts to be filtered
        $templates = apply_filters( 'custom_login_get_template_part', $templates, $slug, $name );

        // Return the part that is found
        return self::locateTemplate( $templates, $load, false );
    }

    /**
     * Retrieve the name of the highest priority template file that exists.
     *
     * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
     * inherit from a parent theme can just overload one file. If the template is
     * not found in either of those, it looks in the theme-compat folder last.
     *
     * @since 2.0
     *
     * @param string|array $template_names Template file(s) to search for, in order.
     * @param bool $load If true the template file will be loaded if it is found.
     * @param bool $require_once Whether to require_once or require. Default true.
     *                            Has no effect if $load is false.
     *
     * @return string The template filename if one is located.
     */
    public static function locateTemplate( $template_names, $load = false, $require_once = true ) {
        // No file found yet
        $located = false;

        // Try to find a template file
        foreach ( (array) $template_names as $template_name ) {

            // Continue if template is empty
            if ( empty( $template_name ) ) {
                continue;
            }

            // Trim off any slashes from the template name
            $template_name = ltrim( $template_name, '/' );

            // Check child theme first
            if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'custom_login_templates/' . $template_name ) ) {
                $located = trailingslashit( get_stylesheet_directory() ) . 'custom_login_templates/' . $template_name;
                break;
                // Check parent theme next
            } elseif ( file_exists( trailingslashit( get_template_directory() ) . 'custom_login_templates/' . $template_name ) ) {
                $located = trailingslashit( get_template_directory() ) . 'custom_login_templates/' . $template_name;
                break;
                // Check plugin compatibility last
            } elseif ( file_exists( trailingslashit( self::getTemplatesDir() ) . $template_name ) ) {
                $located = trailingslashit( self::getTemplatesDir() ) . $template_name;
                break;
            }
        }

        if ( ( $load === true ) && ! empty( $located ) ) {
            load_template( $located, $require_once );
        }

        return $located;
    }
}

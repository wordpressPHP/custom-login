<?php

namespace PassyCo\CustomLogin\Api;

use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\HooksTrait;
use PassyCo\CustomLogin\Templates;
use PassyCo\CustomLogin\WpHooksInterface;
use PassyCo\CustomLogin\WpLogin;

/**
 * Class WpRest
 *
 * @package PassyCo\CustomLogin\Api
 */
class WpRest implements WpHooksInterface {

    use HooksTrait;

    public function addHooks() {
        $this->addAction( 'rest_api_init', [ $this, 'registerRestRoute' ] );
    }

    protected function registerRestRoute() {
        register_rest_route(
            sprintf( '%s/v1', CustomLogin::DOMAIN ),
            'style',
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [ $this, 'callback' ],
                'show_in_index' => false,
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error The response for the request.
     */
    public function callback( \WP_REST_Request $request ) {
        WpLogin::$css_atts = [
            'version' => 99,
            'trans_key' => Common::getTransientKey( 'style' ),
        ];
        WpLogin::$css_atts = wp_parse_args( Common::getOptions( 'design' ), WpLogin::$css_atts );

        $data = "<style type=\"text/css\">\n";
        ob_start();
        include Templates::getTemplatePart( 'wp-login', 'style' );
        $data .= ob_get_clean();
        $data .= "\n</style>\n";

        return new \WP_REST_Response( [
            'css' => $data,
        ], \WP_Http::OK );
    }

}
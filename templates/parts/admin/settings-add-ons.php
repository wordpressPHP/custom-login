<?php

use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\Extensions;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof Extensions ) ) {
    return;
}

$status_text = __( 'Not Installed', CustomLogin::DOMAIN ); ?>

    <div class="wrap">
        <h2><?php esc_attr_e( 'Custom Login Add-on Plugins', CustomLogin::DOMAIN ); ?></h2>
        <br class="clear">
        <form method="post">
            <div class="wp-list-table widefat plugin-install"><?php

                if ( ! empty( $object->extensions ) ) {
                    foreach ( $object->extensions as $key => $plugin ) { ?>
                        <div class="plugin-card plugin-card-<?php echo sanitize_html_class( $plugin['title'] ); ?>">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a href="<?php echo esc_url( add_query_arg( [
                                            'utm_source' => 'wordpressorg',
                                            'utm_medium' => 'custom-login',
                                            'utm_campaign' => 'eddri',
                                        ], $plugin['url'] ) ); ?>">
                                            <?php echo esc_attr( $plugin['title'] ); ?>
                                            <img src="<?php echo esc_url( $plugin['image'] ) ?>"
                                                 class="plugin-icon"
                                                 alt="<?php echo esc_attr( $plugin['title'] ); ?>">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a class="button"
                                               data-edd-install="<?php echo esc_attr( $plugin['title'] ); ?>"
                                               data-edd-plugin-basename="<?php echo esc_attr( $plugin['basename'] ); ?>">
                                                <?php _e( 'Install', CustomLogin::DOMAIN ); ?></a>
                                        </li>
                                        <li><a class="button show-if-not-purchased"
                                               data-toggle="purchase-links-<?php echo absint( $key ); ?>"
                                               style="display:none">
                                                <?php _e( 'Purchase', CustomLogin::DOMAIN ); ?></a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p><?php echo $plugin['description']; ?></p>
                                </div>
                                <br class="clear">
                            </div>
                            <div class="plugin-card-bottom">
                                <div id="purchase-links-<?php echo absint( $key ); ?>"
                                     style="display:none">
                                    <ul>
                                        <?php foreach ( $plugin['links'] as $link ) {
                                            echo '<li>' . $link['description'] . ' (' . $link['price'] . '): <a href="' .
                                                 esc_url( add_query_arg( [
                                                     'edd_action' => 'straight_to_gateway',
                                                     'download_id' => $link['download_id'],
                                                     'edd_options[price_id]' => $link['price_id'],
                                                 ], $object->api_url . 'checkout' ) ) . '">' .
                                                 __( 'PayPal', CustomLogin::DOMAIN ) . '</a>' .
                                                 ' | <a href="' . esc_url( add_query_arg( [
                                                    'edd_action' => 'add_to_cart',
                                                    'download_id' => $link['download_id'],
                                                    'edd_options[price_id]' => $link['price_id'],
                                                ], $object->api_url . 'checkout' ) ) . '">' .
                                                 __( 'Credit Card', CustomLogin::DOMAIN ) . '</a></li>';
                                        } ?>
                                    </ul>
                                </div><!-- #purchase-links -->
                                <div id="progress-container-<?php echo absint( $key ); ?>"
                                     class="eddri-addon">
                                    <div class="eddri-addon-container">
                                        <span class="eddri-status">Not Installed</span>
                                    </div>
                                </div>
                            </div><!-- .plugin-card-bottom -->
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </form>
    </div>
    <script type="text/javascript">
      jQuery(document).ready(function ($) {

        var status_text = '<?php echo $status_text; ?>';

        setTimeout(function () {
          // Remote API helper
          $('a[data-toggle]').on('click', function (e) {
            e.preventDefault();
            $('#' + $(this).data('toggle')).toggle();
          });

          // Show Purchase button
          $('a[data-edd-install]').each(function () {
            var $this = $(this);
            setTimeout(function () {
              if ($this.parents('.plugin-action-buttons').find('.eddri-status').text() === status_text) {
//							$this.parents('.plugin-action-buttons').find('a.button').hide();
                $this.parents('.plugin-action-buttons').find('a.button.show-if-not-purchased').show();
              }
            }, 500);
          });

        }, 1000);
      });
    </script>
<?php

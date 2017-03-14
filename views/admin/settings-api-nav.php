<?php

use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\Api\SettingsApi;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof SettingsApi ) ) {
    return;
}

?>
    <ul class="CustomLogin__sections-menu">
        <?php
        foreach ( $object->getSettingsSections() as $section ) {

            $using_customizer = Common::usingCustomizer();

            printf( '<li><a href="%s" data-tab-id="%s">%s</a></li>',
                isset( $section['href'] ) ? $using_customizer ? $section['href'] : 'javascript:;' :
                    'javascript:;',
                $section['id'],
                $section['title']
            );
        } ?>
    </ul>
<?php

<?php

namespace PassyCo\CustomLogin;

/**
 * Class Init
 *
 * @package PassyCo\CustomLogin
 */
class Init implements \IteratorAggregate {

    /**
     * A container for objects that implement CL_WordPress_Hooks interface
     *
     * @var array
     */
    public $plugin_components = [];

    /**
     * Adds an object to $container property
     *
     * @param WpHooksInterface $object
     *
     * @return $this
     */
    public function add( WpHooksInterface $object ) {
        $this->plugin_components[] = $object;

        return $this;
    }

    /**
     * All the methods that need to be performed upon plugin initialization should
     * be done here.
     */
    public function initialize() {
        foreach ( $this as $container_object ) {
            if ( $container_object instanceof WpHooksInterface ) {
                $container_object->addHooks();
            }
        }
    }

    /**
     * Provides an iterator over the $container property
     *
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator( $this->plugin_components );
    }
}

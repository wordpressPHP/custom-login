<?php

use CL_Interface_WordPress_Hooks as WordPress_Hooks;

/**
 * Class Init
 */
class CL_Init implements IteratorAggregate {

    /**
     * A container for objects that implement CL_WordPress_Hooks interface
     *
     * @var array
     */
    public $plugin_components = array();

    /**
     * Adds an object to $container property
     *
     * @param \CL_Interface_WordPress_Hooks $object
     *
     * @return $this
     */
    public function add( WordPress_Hooks $object ) {
        $this->plugin_components[] = $object;

        return $this;
    }

    /**
     * All the methods that need to be performed upon plugin initialization should
     * be done here.
     */
    public function initialize() {

        foreach ( $this as $container_object ) {
            if ( $container_object instanceof WordPress_Hooks ) {
                $container_object->add_hooks();
            }
        }
    }

    /**
     * Provides an iterator over the $container property
     *
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator( $this->plugin_components );
    }
}

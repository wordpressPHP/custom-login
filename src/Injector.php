<?php

namespace PassyCo\CustomLogin;

/**
 * Class Injector
 *
 * @package PassyCo\CustomLogin
 */
class Injector {

    /** @var DependencyCheck $dependency_check */
    private $dependency_check;

    /** @var Hookup $hookup */
    private $hookup;

    /**
     * Injector constructor.
     *
     * @param DependencyCheck $dependency_Check
     * @param Hookup $hookup
     */
    public function __construct( DependencyCheck $dependency_Check, Hookup $hookup ) {
        $this->setDependencyCheck( $dependency_Check );
        $this->setHookup( $hookup );
    }

    /**
     * @return DependencyCheck
     */
    public function getDependencyCheck() {
        return $this->dependency_check;
    }

    /**
     * @param DependencyCheck $dependency_Check
     */
    protected function setDependencyCheck( DependencyCheck $dependency_Check ) {
        $this->dependency_check = $dependency_Check;
    }

    /**
     * @return Hookup
     */
    public function getHookup() {
        return $this->hookup;
    }

    /**
     * @param Hookup $hookup
     */
    protected function setHookup( Hookup $hookup ) {
        $this->hookup = $hookup;
    }
}

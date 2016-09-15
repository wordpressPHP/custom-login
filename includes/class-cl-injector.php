<?php

/**
 * Class CL_Injector
 */
class CL_Injector {

    /**
     * @var CL_Dependency_Check $dependency_check
     */
    private $cl_dependency_check;

    /**
     * @var CL_Hookup $cl_hookup
     */
    private $cl_hookup;

    public function __construct( CL_Dependency_Check $dependency_Check, CL_Hookup $hookup ) {
        $this->set_cl_dependency_check( $dependency_Check );
        $this->set_cl_hookup( $hookup );
    }

    /**
     * @param \CL_Dependency_Check $dependency_Check
     */
    protected function set_cl_dependency_check( CL_Dependency_Check $dependency_Check ) {
        $this->cl_dependency_check = $dependency_Check;
    }

    /**
     * @return \CL_Dependency_Check
     */
    public function get_cl_dependency_check() {
        return $this->cl_dependency_check;
    }

    /**
     * @param \CL_Hookup $hookup
     */
    protected function set_cl_hookup( CL_Hookup $hookup ) {
        $this->cl_hookup = $hookup;
    }

    /**
     * @return \CL_Hookup
     */
    public function get_cl_hookup() {
        return $this->cl_hookup;
    }
}

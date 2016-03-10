<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class CL_Admin_Add_On_List_Table
 * @link http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 */
class CL_Admin_Add_On_List_Table extends WP_List_Table {

	protected $action;

	/**
	 * CL_Admin_Add_On_List_Table constructor.
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'notice', Custom_Login_Bootstrap::DOMAIN ),
			'plural'   => __( 'notices', Custom_Login_Bootstrap::DOMAIN ),
			'ajax'     => true,
		) );
	}

	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return mixed|string
	 */
	function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'image':
				return sprintf( '<img src="%s">', $item['image'] );

			case 'plugin':
				return $item['title'];

			case 'desc':
				return $item['description'];

			case 'license':
				return '';

			default:
				return print_r( $item, true );
		}
	}

	/**
	 * @return array
	 */
	function get_columns() {

		$columns = array(
			'image'  => __( 'Image', Custom_Login_Bootstrap::DOMAIN ),
			'plugin' => __( 'Plugin', Custom_Login_Bootstrap::DOMAIN ),
			'desc'   => __( 'Description', Custom_Login_Bootstrap::DOMAIN ),
			'license' => __( 'License', Custom_Login_Bootstrap::DOMAIN ),
		);

		return $columns;
	}

	/**
	 * @param int $per_page
	 * @param bool $pageination
	 */
	function prepare_items( $per_page = 10, $pageination = false ) {

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// First let's sort the data
		if ( is_array( $this->items ) ) {
			arsort( $this->items ); // Sort in reverse order and maintain index
		}

		$current_page = $this->get_pagenum();
		$total_items  = count( $this->items );

		if ( ! $pageination ) {
			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			) );
		}
	}
}

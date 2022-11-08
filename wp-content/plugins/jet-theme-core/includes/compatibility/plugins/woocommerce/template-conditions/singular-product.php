<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo_Singular_Product extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'woo-singular-product';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Single Product', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'woocommerce';
	}

	/**
	 * @return string
	 */
	public function get_sub_group() {
		return 'woocommerce-single';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 27;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_single_product';
	}

	/**
	 * [get_control description]
	 * @return [type] [description]
	 */
	public function get_control() {
		return [
			'type'        => 'f-select',
			'placeholder' => __( 'Select product', 'jet-theme-core' ),
		];
	}

	/**
	 * [ajax_action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return 'get-products';
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg ) {

		if ( empty( $arg ) ) {
			return is_singular( [ 'product' ] );
		}

		if ( in_array( 'all', $arg ) ) {
			return is_product();
		}

		foreach ( $arg as $id ) {
			$is_single = is_single( $id );

			if ( $is_single ) {
				return true;
			}
		}

		return false;

	}

}

<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Get_CPT_Categories extends Base {

	/**
	 * [get_method description]
	 * @return [type] [description]
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'get-cpt-categories';
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		//$post_types = \Jet_Theme_Core\Utils::get_post_types_options();
		$post_types = [
			[
				'label' => 'Group 1',
				'options' => [
					[
						'label' => 'Label 1',
						'value' => 'value-1',
					],
					[
						'label' => 'Label 2',
						'value' => 'value-2',
					],
				],
			],
			[
				'label' => 'Group 2',
				'options' => [
					[
						'label' => 'Label 1',
						'value' => 'value-1',
					],
					[
						'label' => 'Label 2',
						'value' => 'value-2',
					],
				],
			],
		];

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Success', 'jet-theme-core' ),
			'data'    => $post_types,
		] );
	}

}

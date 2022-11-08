<?php
namespace Jet_Theme_Core\Compatibility;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Compatibility Manager
 */
class Manager {

	/**
	 * [$registered_subpage_modules description]
	 * @var array
	 */
	private $registered_plugins = array();

	/**
	 * [__construct description]
	 */
	public function __construct() {

		$this->load_files();

		$this->registered_plugins = apply_filters( 'jet-theme-core/compatibility-manager/registered-plugins', array(
			'jet-style-manager' => array(
				'class'    => '\\Jet_Theme_Core\\Compatibility\\Jet_Style_Manager',
				'instance' => false,
			),
			'polylang' => array(
				'class'    => '\\Jet_Theme_Core\\Compatibility\\Polylang',
				'instance' => false,
			),
			'wpml' => array(
				'class'    => '\\Jet_Theme_Core\\Compatibility\\WPML',
				'instance' => false,
			),
			'woocommerce' => array(
				'class'    => '\\Jet_Theme_Core\\Compatibility\\Woocommerce',
				'instance' => false,
			),
			'jet-woo-builder' => array(
				'class'    => '\\Jet_Theme_Core\\Compatibility\\Jet_Woo_Builder',
				'instance' => false,
			),
		) );


		$this->load_plugin_modules();
	}

	/**
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {
		require jet_theme_core()->plugin_path( 'includes/compatibility/plugins/jet-style-manager/manager.php' );
		require jet_theme_core()->plugin_path( 'includes/compatibility/plugins/polylang/manager.php' );
		require jet_theme_core()->plugin_path( 'includes/compatibility/plugins/wpml/manager.php' );
		require jet_theme_core()->plugin_path( 'includes/compatibility/plugins/woocommerce/manager.php' );
		require jet_theme_core()->plugin_path( 'includes/compatibility/plugins/jet-woo-builder/manager.php' );

	}

	/**
	 * [maybe_load_theme_module description]
	 * @return [type] [description]
	 */
	public function load_plugin_modules() {

		$this->registered_plugins = array_map( function( $plugin_data ) {
			$class = $plugin_data['class'];

			if ( ! $plugin_data['instance'] && class_exists( $class ) ) {
				$plugin_data['instance'] = new $class();
			}

			return $plugin_data;
		}, $this->registered_plugins );

	}

}

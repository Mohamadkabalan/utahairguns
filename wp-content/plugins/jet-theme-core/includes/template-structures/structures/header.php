<?php
namespace Jet_Theme_Core\Structures;

class Header extends Base {

	public function get_id() {
		return 'jet_header';
	}

	public function get_single_label() {
		return esc_html__( 'Header', 'jet-theme-core' );
	}

	public function get_plural_label() {
		return esc_html__( 'Headers', 'jet-theme-core' );
	}

	public function get_sources() {
		return array( 'jet-theme', 'jet-api' );
	}

	/**
	 * @return array
	 */
	public function get_elementor_document_type() {
		return array(
			'class' => 'Jet_Header_Document',
			'file'  => jet_theme_core()->plugin_path( 'includes/elementor/document-types/header.php' ),
		);
	}

	/**
	 * Is current structure could be outputed as location
	 *
	 * @return boolean
	 */
	public function is_location() {
		return true;
	}

	/**
	 * Location name
	 *
	 * @return boolean
	 */
	public function location_name() {
		return 'header';
	}

	/**
	 * Aproprite location name from Elementor Pro
	 * @return [type] [description]
	 */
	public function pro_location_mapping() {
		return 'header';
	}

	/**
	 * Library settings for current structure
	 *
	 * @return void
	 */
	public function library_settings() {

		return array(
			'show_title'    => false,
			'show_keywords' => true,
		);

	}

	public function get_admin_bar_priority() {
		return 0;
	}

}

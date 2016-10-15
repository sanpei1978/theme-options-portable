<?php

namespace SettingStore;

abstract class Wp_Settings_Abstract {

	const FORM_ACTION = 'options.php';

	public function add_option( $option, $value, $deprecated = '', $autoload = 'yes' ) {
		return add_option( $option, $value, $autoload, $autoload );
	}

	public function update_option( $option, $newvalue, $autoload = null ) {
		return update_option( $option, $newvalue, $autoload );
	}

	public function get_option( $option, $default = false ) {
		return get_option( $option, $default );
	}

	public function delete_option( $option ) {
		return delete_option( $option );
	}

	abstract public function sanitize_callback( $input );
	abstract public function section_callback( array $args );
	abstract public function write_input_field( array $args );

}

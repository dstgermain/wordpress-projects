<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 4/20/15
 * Time: 6:01 PM
 */

/**
 * Class for adding a new field to the options-general.php page
 */

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

class Add_Settings_Field {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
	}

	/**
	 * Add new fields to wp-admin/options-general.php page
	 */
	public function register_fields() {
		register_setting( 'general', 'street_address', 'esc_attr' );
		register_setting( 'general', 'city_address', 'esc_attr' );
		register_setting( 'general', 'state_address', 'esc_attr' );
		register_setting( 'general', 'zip_address', 'esc_attr' );

		register_setting( 'general', 'phone_1', 'esc_attr' );
		register_setting( 'general', 'phone_2', 'esc_attr' );

		add_settings_field(
			'street_address',
			'<label for="street_address">' . __( 'Street Address' , 'street_address' ) . '</label>',
			array( &$this, 'street_html' ),
			'general'
		);
		add_settings_field(
			'city_address',
			'<label for="city_address">' . __( 'City' , 'city_address' ) . '</label>',
			array( &$this, 'city_html' ),
			'general'
		);
		add_settings_field(
			'state_address',
			'<label for="state_address">' . __( 'State' , 'state_address' ) . '</label>',
			array( &$this, 'state_html' ),
			'general'
		);
		add_settings_field(
			'zip_address',
			'<label for="zip_address">' . __( 'ZIP CODE' , 'zip_address' ) . '</label>',
			array( &$this, 'zip_html' ),
			'general'
		);

		add_settings_field(
			'phone_1',
			'<label for="phone_1">' . __( 'Phone Number' , 'phone_1' ) . '</label>',
			array( &$this, 'phone1_html' ),
			'general'
		);
		add_settings_field(
			'phone_2',
			'<label for="phone_2">' . __( 'Secondary Phone Number' , 'phone_2' ) . '</label>',
			array( &$this, 'phone2_html' ),
			'general'
		);


		register_setting( 'general', 'pp_use_sandbox', 'esc_attr' );

		add_settings_field(
			'pp_use_sandbox',
			'<label for="pp_use_sandbox">' . __( 'Use Paypal Sandbox (Test Environment)' , 'pp_use_sandbox' ) . '</label>',
			array( &$this, 'pp_use_sandbox_html' ),
			'general'
		);

		register_setting( 'general', 'pp_standard_email', 'esc_attr' );

		add_settings_field(
			'pp_standard_email',
			'<label for="pp_standard_email">' . __( 'Paypal Standard Email' , 'pp_standard_email' ) . '</label>',
			array( &$this, 'pp_standard_email_html' ),
			'general'
		);

		register_setting( 'general', 'pp_email', 'esc_attr' );

		add_settings_field(
			'pp_email',
			'<label for="pp_email">' . __( 'Paypal Express Email' , 'pp_email' ) . '</label>',
			array( &$this, 'pp_email_html' ),
			'general'
		);

		register_setting( 'general', 'pp_password', 'esc_attr' );

		add_settings_field(
			'pp_password',
			'<label for="pp_password">' . __( 'Paypal Express Password' , 'pp_password' ) . '</label>',
			array( &$this, 'pp_password_html' ),
			'general'
		);

		register_setting( 'general', 'pp_api_key', 'esc_attr' );

		add_settings_field(
			'pp_api_key',
			'<label for="pp_api_key">' . __( 'Paypal Express API Key' , 'pp_api_key' ) . '</label>',
			array( &$this, 'pp_api_key_html' ),
			'general'
		);
	}

	/**
	 * HTML for extra settings
	 */
	public function street_html() {
		$street_address = get_option( 'street_address', '' );
		echo '<input type="text" id="street_address" name="street_address" value="' . esc_attr( $street_address ) . '" />';
	}

	public function city_html() {
		$city_address = get_option( 'city_address', '' );
		echo '<input type="text" id="city_address" name="city_address" value="' . esc_attr( $city_address ) . '" />';
	}

	public function state_html() {
		$state_address = get_option( 'state_address', '' );
		echo '<input type="text" id="state_address" name="state_address" value="' . esc_attr( $state_address ) . '" />';
	}

	public function zip_html() {
		$zip_address = get_option( 'zip_address', '' );
		echo '<input type="text" id="zip_address" name="zip_address" value="' . esc_attr( $zip_address ) . '" />';
	}

	public function phone1_html() {
		$phone_1 = get_option( 'phone_1', '' );
		echo '<input type="text" id="phone_1" name="phone_1" value="' . esc_attr( $phone_1 ) . '" />';
	}

	public function phone2_html() {
		$phone_2 = get_option( 'phone_2', '' );
		echo '<input type="text" id="phone_2" name="phone_2" value="' . esc_attr( $phone_2 ) . '" />';
	}

	public function pp_use_sandbox_html() {
		$pp_use_sandbox = get_option( 'pp_use_sandbox', false );
		echo '<input type="checkbox" id="pp_use_sandbox" name="pp_use_sandbox" ' . (esc_attr( $pp_use_sandbox ) ? 'checked' : '') . ' />';
	}

	public function pp_standard_email_html() {
		$pp_standard_email = get_option( 'pp_standard_email', false );
		echo '<input type="text" id="pp_standard_email" name="pp_standard_email" value="' . esc_attr( $pp_standard_email ) . '" />';
	}

	public function pp_email_html() {
		$pp_email = get_option( 'pp_email', false );
		echo '<input type="text" id="pp_email" name="pp_email" value="' . esc_attr( $pp_email ) . '" />';
	}

	public function pp_password_html() {
		$pp_password = get_option( 'pp_password', false );
		echo '<input type="password" id="pp_password" name="pp_password" value="' . esc_attr( $pp_password ) . '" />';
	}

	public function pp_api_key_html() {
		$pp_api_key = get_option( 'pp_api_key', false );
		echo '<input type="text" id="pp_api_key" name="pp_api_key" value="' . esc_attr( $pp_api_key ) . '" />';
	}

}
new Add_Settings_Field();

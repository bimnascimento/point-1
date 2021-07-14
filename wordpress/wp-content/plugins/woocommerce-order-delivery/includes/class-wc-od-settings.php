<?php
/**
 * Class to handle the plugin settings
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_OD_Settings' ) ) {
	/**
	 * WC_OD_Settings Class.
	 */
	class WC_OD_Settings extends WC_OD_Singleton {

		/**
		 * Stores the plugin settings.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var array
		 */
		private $settings = array();


		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function __construct() {
			parent::__construct();

			$this->settings = array();
			$defaults = $this->get_defaults();

			foreach ( $defaults as $name => $default ) {
				$this->settings[ $name ] = get_option( wc_od_maybe_prefix( $name ), $default );
			}

			add_action( 'added_option', array( $this, 'updated_setting' ), 10, 2 );
			add_action( 'updated_option', array( $this, 'updated_setting' ), 10, 3 );
		}

		/**
		 * Gets the default values for the plugin settings.
		 *
		 * @since 1.0.0
		 *
		 * @return array The default settings.
		 */
		public function get_defaults() {
			$defaults = array(
				'min_working_days' => 0,
				'shipping_days' => array(
					array( 'enabled' => '0', 'time' => '' ), // Sunday
					array( 'enabled' => '1', 'time' => '' ), // Monday
					array( 'enabled' => '1', 'time' => '' ), // Tuesday
					array( 'enabled' => '1', 'time' => '' ), // Wednesday
					array( 'enabled' => '1', 'time' => '' ), // Thursday
					array( 'enabled' => '1', 'time' => '' ), // Friday
					array( 'enabled' => '0', 'time' => '' ), // Saturday
				),
				'delivery_range' => array(
					'min' => 1,
					'max' => 10,
				),
				'checkout_delivery_option' => 'calendar',
				'delivery_days' => array(
					array( 'enabled' => '0' ), // Sunday
					array( 'enabled' => '1' ), // Monday
					array( 'enabled' => '1' ), // Tuesday
					array( 'enabled' => '1' ), // Wednesday
					array( 'enabled' => '1' ), // Thursday
					array( 'enabled' => '1' ), // Friday
					array( 'enabled' => '1' ), // Saturday
				),
				'max_delivery_days'     => 90,
				'shipping_events_index' => 1,
				'shipping_events'       => array(),
				'delivery_events_index' => 1,
				'delivery_events'       => array(),
				'delivery_date_field'   => 'optional',
			);

			/**
			 * Filters the default values for the settings.
			 *
			 * @since 1.0.0
			 *
			 * @param array $defaults The default settings.
			 */
			return apply_filters( 'wc_od_defaults', $defaults );
		}

		/**
		 * Gets the default value for a setting.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name The setting name.
		 * @return mixed The default setting value. Null otherwise.
		 */
		public function get_default( $name ) {
			$defaults = $this->get_defaults();
			$setting_name = wc_od_no_prefix( $name );

			return ( isset( $defaults[ $setting_name ] ) ? $defaults[ $setting_name ] : null );
		}

		/**
		 * Gets a setting value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name   The setting name.
		 * @param mixed $default Optional. The default value.
		 * @return mixed The setting value.
		 */
		public function get_setting( $name, $default = null ) {
			$setting_name = wc_od_no_prefix( $name );

			$value = ( isset( $this->settings[ $setting_name ] ) ? $this->settings[ $setting_name ] : $default );

			/**
			 * Filter the setting value.
			 *
			 * Note: Use carefully, especially in admin pages.
			 *
			 * @since 1.1.0
			 *
			 * @param mixed $value The setting value.
			 */
			return apply_filters( "wc_od_get_setting_{$setting_name}", $value );
		}

		/**
		 * Updates a setting.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $name  The setting name.
		 * @param mixed $value The setting value.
		 * @return boolean Gets if the setting was updated or not.
		 */
		public function update_setting( $name, $value ) {
			$settings = $this->get_defaults();
			$setting_name = wc_od_no_prefix( $name );

			if ( isset( $settings[ $setting_name ] ) ) {
				return update_option( wc_od_maybe_prefix( $setting_name ), $value );
			}

			return false;
		}

		/**
		 * Fires after an option has been successfully added or updated.
		 *
		 * We use this method to update the $this->settings property with the new value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option    Name of the updated setting.
		 * @param mixed  $old_value The old option value.
		 * @param mixed  $new_value Optional. The new option value. Only on updated_option hook.
		 */
		public function updated_setting( $option, $old_value, $new_value = null ) {
			$settings = $this->get_defaults();
			$setting_name = wc_od_no_prefix( $option );
			if ( isset( $settings[ $setting_name ] ) ) {
				$value = ( 'updated_option' === current_filter() ? $new_value : $old_value );
				// Updates the setting value.
				$this->settings[ $setting_name ] = $value;
			}
		}

	}
}
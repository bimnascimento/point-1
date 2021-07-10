<?php
/**
 * Plugin Name: Disable User Registration
 * Version: 1.0.1
 * Plugin URI: http://websiter.ro
 * Description: Stops bots from registering accounts when <code>Anyone can register</code> is unchecked in Dashboard > Settings > General > Membership.
 * Tags: disable user registration, disable registration, disable sign-up, security
 * Author: Andrei Gheorghiu
 * Author URI: mailto:mail@websiter.ro?Subject=DUR%20plugin
 * License: GPL2
 * Text Domain: disable-user-registration
 * Domain Path: /languages
 *//*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2017 | websiter.ro
*/

class Disable_User_Registration {

    private static $instance = null;
    public $slug = 'disable-user-registration';

    /**
     * Singleton. Plugin should only have one instance up at any given time.
     * since: 1.0.1
     *
     * @return Disable_User_Registration
     */
    public static function instance() {
        is_null(self::$instance) && self::$instance = new self;

        return self::$instance;
    }

    /**
     *
     * Initiates class on `plugins_loaded` action hook.
     *
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * since: 1.0.1
     */
    public static function init() {
        add_action('plugins_loaded', array(self::instance(), '_act'));
        register_activation_hook( __FILE__, array( self::instance(), 'activation_hook' ) );
    }

    /**
     *
     * Attributes class methods to action hooks
     *
     * since: 1.0.1
     *
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     *
     */
    public function _act() {
        $this->load_textdomain();
        add_action(
            'login_init',
            array($this, 'redirect')
        );
        add_action(
            'signup_header',
            array($this, 'redirect')
        );
        add_action(
            'registration_errors',
            array($this, 'registration_errors'),
            10,
            1
        );
        add_filter(
            'user_register',
            array($this, 'gotcha'),
            1,
            1
        );
        add_action(
            'admin_notices',
            array($this, 'install_notice')
        );
        add_filter(
            'plugin_action_links_' . plugin_basename(__FILE__),
            array($this, 'add_settings_link'),
            10,
            1
        );
    }

    /**
     *
     * Redirects to `wp-login.php?registration=disabled` or custom URL.
     * Runs on `login_init`, `signup_header` action hooks.
     *
     * since: 1.0.1
     *
     * @uses wp_redirect()
     * @uses site_url()
     * @see  https://codex.wordpress.org/Plugin_API/Action_Reference Plugin API/Action Reference
     *
     */
    public function redirect() {
        if ($this->should_block() && (isset($_GET['action']) && $_GET['action'] == 'register')) {
            wp_redirect(site_url('wp-login.php?registration=disabled'));
            exit();
        }
    }

    /**
     *
     * Check current users for `create_users` capability.
     * Runs on `registration_errors` action hook.
     *
     * since: 1.0.1
     *
     * @param $errors WP_Error
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @return WP_Error
     *
     */
    public function registration_errors($errors) {


        if ($this->should_block()) {

            $errors->add('registerdisabled', __('User registration is currently not allowed.', 'disable-user-registration'));
        }

        return $errors;
    }

    /**
     *
     * Perhaps paranoid: When a script manages to register a user without being logged in
     * or having `create_users` capability, this deletes the user right after being created.
     * Runs on `user_register` action hook.
     *
     * since: 1.0.1
     *
     * @param $user_id int
     *
     */
    public function gotcha($user_id) {
        if ($this->should_block()) {
            wp_delete_user($user_id);
        }
    }

    /**
     *
     * Enables i18n.
     *
     * since: 1.0.1
     *
     * @uses $this->slug
     *
     */
    private function load_textdomain() {
        load_plugin_textdomain(
            $this->slug,
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Returns true if current user can `create_users` or if user registration is allowed.
     *
     * since: 1.0.1
     *
     * @return bool
     *
     */
    private function should_block() {
        return !(user_can(get_current_user(), 'create_users') || $this->get_option());
    }

    /**
     * Triggers display of installation dismissible notice.
     *
     */
    static function activation_hook() {
        set_transient( 'dur-admin-install-notice', true, 5 );
    }

    /**
     * Displays installation dismissible notice.
     * Runs only once, on `admin_notices` action hook.
     *
     * since: 1.0.1
     *
     */
    public function install_notice() {
        if( get_transient( 'dur-admin-install-notice' ) ) {
            $settings_link = sprintf(
                '<a href="%s">%s > %s > %s</a>',
                esc_url( get_admin_url(null, 'options-general.php') ),
                __('Settings'),
                __('General'),
                __('Membership')
            );
            $anyone_can_register = '<code>"' . __("Anyone can register") . '"</code>';
            $notice = $this->get_option() ?
                array(
                    'class' => 'warning',
                    'message' => sprintf(
                        __('Don\'t forget to un-check %s in %s (and <strong>save changes</strong>) to stop all user registrations.', 'disable-user-registration' ),
                        $anyone_can_register,
                        $settings_link
                    )
                ) :
                array(
                    'class' => 'success',
                    'message' => __('Congratulations! Robots are no longer able to register accounts on your website!', 'disable-user-registration' )
                );

            printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', $notice['class'], $notice['message'] );
            delete_transient('dur-admin-install-notice');
        }
    }

    /**
     *
     * Return `users_can_register` WP_Option
     *
     * since: 1.0.1
     *
     * @uses get_option()
     *
     * @return mixed|void
     *
     */
    private function get_option(){
        return get_option('users_can_register');
    }

    /**
     *
     * Adds link to `Settings > General` page for plugin
     *
     * since: 1.0.1
     *
     * @param $links
     *
     * @return array
     */
    public function add_settings_link($links) {
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php')).'">' .
            ( $this->get_option() ?
                __('Disable user registration', 'disable-user-registration') :
                __('Enable user registration', 'disable-user-registration')
            ) . '</a>';

        return $links;

    }
}

Disable_User_Registration::init();
<?php
/**
 * Plugin Name: WP Refresh
 * Plugin URI: http://wprefresh.frebsite.nl
 * Description: Automatically refresh CSS, JS and PHP files without having to refresh the browser.
 * Version: 1.1.0
 * Author: Fred Heusschen
 * Author URI: http://www.frebsite.nl
 */


require_once( dirname( __FILE__ ) . '/php/wpr_adminpage.php' );



class WprBackend extends WprAdminPage {
	
	protected $name			= 'wprefresh';
	protected $screen_id 	= 'tools_page_wpr';

	protected $options = array(
		'wpr_options' => array(
			'activate',
			'responsive',
			'monitor_css',
			'monitor_js',
			'monitor_php'
		)
	);

	protected function get_default_option_values()
	{
		return array(
			'monitor_css' 	=> 'yes',
			'monitor_js' 	=> 'yes',
			'monitor_php' 	=> 'yes'
		);
	}

	public function add_menu_page()
	{
		add_management_page( 
			'WP Refresh',
			'WP Refresh',
			'manage_options',
			'wpr',
			array( $this, 'create_admin_page' )
		);
	}

	public function create_admin_page()
	{

	    parent::create_admin_page();

		echo '
		<div class="wrap">';

		$options = get_option( 'wpr_options', false );
		if ( !$options )
		{
			$options = $this->get_default_option_values();
		}

		$this->echo_title( 'WP Refresh' );
		$this->echo_form_opener();
		$this->echo_form_table_opener();

		$this->echo_form_table_row(
			'Activate the plugin:',
				$this->html_select( array( $options, 'wpr_options', 'activate' ),
					array(
						'loggedin'	=> 'When logged in',
						'adminbar'	=> 'In the admin bar',
						'always' 	=> 'Always',
						'never'		=> 'Never'
					)
				) . 
				'<p class="msg warning">Do not use the "Always" option on a live server! This plugin is meant to be used for development only!</p>'
		);

		$this->echo_form_table_row(
			'Is the website responsive?',
				$this->html_select( array( $options, 'wpr_options', 'responsive' ),
					array(
						'yes'	=> 'Yes it is.',
						'no'	=> 'No it is not.'
					)
				) .
				'<p class="msg message">While monitoring a responsive theme, a second (smaller) screen is opened that automatically refreshes along with the main screen.</p>'
		);

		$this->echo_form_table_row(
			'Monitor these files:',
				$this->html_checkbox( array( $options, 'wpr_options', 'monitor_css' ) ) .
				'<label for="wpr_options_monitor_css">CSS files.</label>' . '<br />' .
				$this->html_checkbox( array( $options, 'wpr_options', 'monitor_js' ) ) .
				'<label for="wpr_options_monitor_js">JS files.</label>' . '<br />' .
				$this->html_checkbox( array( $options, 'wpr_options', 'monitor_php' ) ) .
				'<label for="wpr_options_monitor_php">PHP files.</label>'
		);

		$this->echo_form_table_row(
			'',
			'<p class="submit">
				<input type="submit" name="submit" value="Save settings" class="button button-primary" />
			</p>'
		);

		$this->echo_form_table_row(
			'Description',
			'<p>This plugin aims to make developing a WordPress theme as efficient as possible in two ways:</p>
			<ol>
				<li>It monitors your (child) theme in the wp-content/themes directory.<br />
					As soon as you save a CSS, JS or PHP file, the changes are automatigally implemented. You don\'t need to refresh your browser.</li>
				<li>It optionally opens a second (smaller) screen that refreshes along with the main screen.<br />
					This way you can see the changes apply to the mobile and desktop website simultaneously.</li>
			</ol>'
		);

		$this->echo_form_table_closer();
		$this->echo_form_closer();

		echo '
		</div>';
	}


	// public function admin_css_js( $screen_id )
	// {
	// 	parent::admin_css_js( $screen_id );

	// 	if ( $screen_id == $this->screen_id )
	// 	{
	// 		// wp_enqueue_style( 	'mmenu', plugin_dir_url( __FILE__ ) . '/dist/core/css/jquery.mmenu.all.css' );
	// 		// wp_enqueue_script(	'mmenu', plugin_dir_url( __FILE__ ) . '/dist/core/js/jquery.mmenu.min.all.js', array( 'jquery' ) );
	// 	}
	// }

}



class WprFrontend {

	protected $options;

	public function __construct()
	{
		$this->options = get_option( 'wpr_options', array() );
		if ( !isset( $this->options[ 'activate' ] ) )
		{
			return;
		}

		$activate = $this->options[ 'activate' ];

		if ( $activate == 'never' )
		{
			return;
		}

		$this->options[ 'filetypes' ] = array();
    	foreach( array( 'css', 'js', 'php' ) as $ext )
    	{
    		if ( isset( $this->options[ 'monitor_' . $ext ] ) )
	    	{
	    		$this->options[ 'filetypes' ][] = $ext;
	    	}	
    	}

    	if ( count( $this->options[ 'filetypes' ] ) == 0 )
    	{
    		return;
    	}

		if ( $activate == 'adminbar' )
		{
			add_action('admin_bar_menu', array( $this, 'add_admin_bar_link' ), 900 );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'inline_scripts' ) );
	}

	public function add_admin_bar_link()
    {
        global $wp_admin_bar;
        $wp_admin_bar->add_menu( array(
            'id' 	=> 'wprefresh',
            'title' => '<span class="ab-icon"></span><span class="ab-label">Refresh</span>',
            'href' 	=> '#',
            'meta'  => array(
                'title' => __( 'Enable WP Refresh monitoring' )
            )
        ));
    }

	public function enqueue_scripts()
	{
		$activate = $this->options[ 'activate' ];

		if ( $activate == 'always' ||
			current_user_can( 'manage_options' )
		) {
			wp_enqueue_script( 	'wpr', plugin_dir_url( __FILE__ ) . 'js/wpr_frontend.js', array( 'jquery' ) );
	   		wp_enqueue_style( 	'wpr', plugin_dir_url( __FILE__ ) . 'css/wpr_frontend.css' );
		}
    }
    public function inline_scripts()
    {
    	$activate = $this->options[ 'activate' ];

		echo '
<script type="text/javascript">
	if ( window.name != "wpr-mobile-window" )
	{
		if ( typeof WPR != "undefined" )
		{
			WPR.scripturl  = "' . plugin_dir_url( __FILE__ ) . 'php/wpr_monitor.php' . '";
			WPR.themepath  = "' . get_stylesheet_directory() . '";
			WPR.responsive = '  . ( $this->options[ 'responsive' ] == 'yes' ? 'true' : 'false' ) . ';
			WPR.filetypes  = ["' . implode( '", "', $this->options[ 'filetypes' ] ) . '"];';

    	if ( $activate == 'always' ||
    		( current_user_can( 'manage_options' ) && $activate == 'loggedin' )
    	) {
			echo '
			WPR.autostart  = true;';
		}

		echo '
		}
	}
</script>';
    }
}



if ( is_admin() )
{
	new WprBackend();
}
else
{
	new WprFrontend();
}
<?php
include_once 'charts.php';

add_action( 'admin_menu', 'mobi_redirect_menu' );
function mobi_redirect_menu(){
	add_options_page( 'Mobile Redirect Plus', 'Mobile Redirect +', 'manage_options', 
						'mobile-redirect-plus', 'redirect_options_page' );
	function redirect_options_page(){
		?>
		<div class="wrap">
			<h2>Mobile Redirect Plus Options</h2>
			<form action="options.php" method="POST">
				<?php settings_fields( 'mobi-setting-group' ); ?>
				<?php do_settings_sections( 'mobi-redirect-plus' ); ?>
				<?php submit_button( ); ?>
			</form>
			<hr/>
			<h2>Till now, Number of Redirection in different device</h2>
			<?php include_once 'showcharts.php'; ?>
		</div>
		<?php
	}
}
add_action( 'admin_init', 'mobi_redirect_init' );
function mobi_redirect_init(){
	register_setting( 'mobi-setting-group', 'mobi-setting' );
	add_settings_section( 'section-main', 'Main Settings', 'main_setting_callback', 'mobi-redirect-plus' );
	add_settings_field( 'mobi-plus', 'Redirect To Mobile', 'redirect_mobile_callback', 'mobi-redirect-plus', 'section-main' );
	add_settings_field( 'mobi-link', 'Mobile Website Link', 'mobile_link_callback', 'mobi-redirect-plus', 'section-main' );
	add_settings_field( 'mobi-tablet', 'Exclude Tablets Redirect', 'redirect_tablet_callback', 'mobi-redirect-plus', 'section-main' );
	add_settings_field( 'mobi-back-main', 'Back to full version website', 'redirect_back_main', 'mobi-redirect-plus', 'section-main' );
	add_settings_section( 'section-specific', '<br/><hr/><br/>Redirect Page', 'redireect_settings_callback', 'mobi-redirect-plus' );
	add_settings_field( 'mobi-specific-page', 'Redirect page', 'redirect_specific_page', 'mobi-redirect-plus', 'section-specific' );
	add_settings_section( 'section-advance', '<br/><hr/><br/>Advanced Settings', 'advanced_settings_callback', 'mobi-redirect-plus' );
	add_settings_field( 'mobi-tablet-ipad', 'iPad URL', 'redirect_tablet_ipad', 'mobi-redirect-plus', 'section-advance' );
	add_settings_field( 'mobi-tablet-all', 'Others Tablet URL', 'redirect_tablet_all', 'mobi-redirect-plus', 'section-advance' );
	add_settings_field( 'mobi-iphone', 'iPhone URL', 'redirect_mobi_iphone', 'mobi-redirect-plus', 'section-advance' );
	add_settings_field( 'mobi-android', 'Android URL', 'redirect_mobi_android', 'mobi-redirect-plus', 'section-advance' );
	add_settings_field( 'mobi-windows', 'Windows Phone URL', 'redirect_mobi_windows', 'mobi-redirect-plus', 'section-advance' );
	

	function main_setting_callback(){
		echo 'Active Radio button to enable/disable mobile redirection. Then enter your mobile site URL in the field below';
	}
	function redireect_settings_callback(){
		echo 'Option for redirecting your Full website or A specific page. If you choose specific page, please add the page URL in the field';
	}
	function advanced_settings_callback(){
		echo 'This is Advance setting. If you want to use different URL for different plateform like iPhone, Android, Windows 
			Phone then fill those field. Same for Tablet, Tablet URL give you the option to redirct your target URL for 
			Tablet devices if required. Otherwise if you not set those, website will be redirect to the main mobile link';
	}
	function redirect_mobile_callback(){
		$setting = (array)get_option('mobi-setting');?>
		<input type="radio" name="mobi-setting[redirect]" value="yes" <?php checked('yes', $setting['redirect']); ?> />Active
  		<input type="radio" name="mobi-setting[redirect]" value="no" <?php checked('no', $setting['redirect']); ?> />Inactive
  		<?php
	}
	function mobile_link_callback(){
		$setting = (array)get_option('mobi-setting');
		$link = esc_attr( $setting['link'] );
		echo "<input type='text' class='regular-text' name='mobi-setting[link]' value='$link' />";
		echo '<p class="description">Enter mobile site URL like &nbsp; http://m.google.com</p>';
	}

	function redirect_tablet_callback(){
		$setting = (array)get_option('mobi-setting');?>
		<input type="radio" name="mobi-setting[redirect_tab]" value="yes" <?php checked('yes', $setting['redirect_tab']); ?> />Yes
  		<input type="radio" name="mobi-setting[redirect_tab]" value="no" <?php checked('no', $setting['redirect_tab']); ?> />No
  		<?php
  		echo '<p class="description">If you want to stop redirection for Tablet then check yes (default is no)</p>';
	}
	//full version website
	function redirect_back_main(){
		echo "<div style='background:#408CEA;color:#FFFFFF;font-weight:bold;min-height:21px;padding:3px 5px;width:338px;'>";
		echo get_site_url();
		echo "/?main=true</div>";
		echo '<p class="description">Place this link in mobile website for Redirect back mobile visitor to main website</p>';
	}
	//Redirect Page Option	
	function redirect_specific_page(){
		$setting = (array)get_option('mobi-setting');?>
		<input type="radio" onclick="javascript:yesnoCheck();" id="yesCheck" name="mobi-setting[redirect_page]" value="yes" <?php checked('yes', @$setting['redirect_page']); ?> />Full Website
  		<input type="radio" onclick="javascript:yesnoCheck();" id="noCheck" name="mobi-setting[redirect_page]" value="no" <?php checked('no', @$setting['redirect_page']); ?> />Specific Page
  		<input type='text' style="display:none" class='regular-text' id="spacific-page" name='mobi-setting[specific_page]' value='<?php echo @$setting['specific_page'];?>' />
  		<?php
	}
	//Tablet Option
	function redirect_tablet_ipad(){
		$setting = (array)get_option('mobi-setting');
		$link = esc_attr( $setting['tab_ipad'] );
		echo "<input type='text' class='regular-text' name='mobi-setting[tab_ipad]' value='$link' />";
		echo '<p class="description">Redirect URL for iPad</p>';
	}
	function redirect_tablet_all(){
		$setting = (array)get_option('mobi-setting');
		$link = esc_attr( $setting['tab_all'] );
		echo "<input type='text' class='regular-text' name='mobi-setting[tab_all]' value='$link' />";
		echo '<p class="description">Redirect URL for others tablet</p>';
	}
	//Phone seperate redirect
	function redirect_mobi_iphone(){
		$setting = (array)get_option('mobi-setting');
		$link = esc_attr( $setting['mobi_iphone'] );
		echo "<input type='text' class='regular-text' name='mobi-setting[mobi_iphone]' value='$link' />";
		echo '<p class="description">Redirect URL for iPhone</p>';
	}
	function redirect_mobi_android(){
		$setting = (array)get_option('mobi-setting');
		$link = esc_attr( $setting['mobi_android'] );
		echo "<input type='text' class='regular-text' name='mobi-setting[mobi_android]' value='$link' />";
		echo '<p class="description">Redirect URL for iPhone</p>';
	}
	function redirect_mobi_windows(){
		$setting = (array)get_option('mobi-setting');
		$link = esc_attr( $setting['mobi_windows'] );
		echo "<input type='text' class='regular-text' name='mobi-setting[mobi_windows]' value='$link' />";
		echo '<p class="description">Redirect URL for Windows Phone</p>';
	}
}
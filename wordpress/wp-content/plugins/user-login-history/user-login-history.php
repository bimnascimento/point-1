<?php
/*
  Plugin Name: User Login History
  Plugin URI: https://github.com/faiyazalam/wp_login_history_plugin
  Description: Easily tracks user login with a set of multiple attributes like ip, login/logout/last-seen time, country, username, user role, browser, OS etc.
  Version: 1.4
  Text Domain: fauserloginhistory
  Author: Faiyaz Alam
  Author URI: https://github.com/faiyazalam/
 */

/**
 * NOTE: WordPress has some events scheduled even when you don't schedule them manually. 
 * So I think this hook (after_setup_theme) will be call automatically even when user does not reload the page or does not send any request.
 * So I am using this feature to catch Last Acitivity Time
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
require_once('lib/functions.php');
require_once('lib/class-ulh-list-table.php');

//Activate Hook Plugin
register_activation_hook(__FILE__, 'ulh_add_user_logins_table');

function ulh_add_user_logins_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $fa_user_logins_table = $wpdb->prefix . "fa_user_logins";

    $sql = "CREATE TABLE $fa_user_logins_table (
		 id int(11) NOT NULL AUTO_INCREMENT,
   user_id int(11) ,
  `time_login` datetime NOT NULL,
  `time_logout` datetime NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `operating_system` varchar(100) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `country_code` varchar(20) NOT NULL	,		  					  
   PRIMARY KEY (`id`)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
    update_option('fa_userloginhostory_version', '1.0');
    ulh_update_tables_when_plugin_updating();
}

//Load the language files
add_action('plugins_loaded', 'ulh_userloginhistoryt_init');

function ulh_userloginhistoryt_init() {
    load_plugin_textdomain('fauserloginhistory', false, plugin_basename(dirname(__FILE__) . '/languages/'));
}

//Show Plugin Notice in Admin Section
add_action('admin_notices', 'ulh_admin_notice');

function ulh_admin_notice() {
    $pluginAdminPages = array(
        'fa_user_login_history_listing',
        'user-login-history-settings'
    );
    $options = get_option('ulh_settings');
    $showNotice = isset($options['ulh_is_show_plugin_notice']) ? $options['ulh_is_show_plugin_notice'] : 0;

    if (isset($_GET['page']) && (in_array($_GET['page'], $pluginAdminPages))) {
        $showNotice = TRUE;
    }
    if ($showNotice) {


        echo '<div class="ulh_admin_notice"><p>';
        printf(__("<a target = '_blank' href='https://www.upwork.com/o/profiles/users/_~01737016f9bf37a62b/'>About Plugin Author</a>"));
        echo '</p></div>';
    }
}

//Bootstrap
add_action('admin_init', 'ulh_bootstrap');

function ulh_bootstrap() {
    ob_start();
    if (!session_id()) {
        session_start();
    }


    include_once( get_home_path() . '/wp-load.php' );
}

//Uninstall Hook Plugin
register_deactivation_hook(__FILE__, 'ulh_userloginhistory_uninstall');

function ulh_userloginhistory_uninstall() {
    ulh_delete_plugin_options();

    global $wpdb;
    $fa_user_logins_table = $wpdb->prefix . "fa_user_logins";
    $sql = "DROP TABLE IF EXISTS .$fa_user_logins_table";
    $wpdb->query("DROP TABLE IF EXISTS " . $fa_user_logins_table);
}

//enque scripts only for this plugin pages in admin section
add_action('admin_enqueue_scripts', 'ulh_load_admin_scripts');

function ulh_load_admin_scripts() {
    if (!isset($_GET['page'])) {
        return;
    }

    if ('fa_user_login_history_listing' == $_GET['page']) {
        wp_register_style('ulh_wp_admin_css', plugin_dir_url(__FILE__) . 'css/style.css');
        wp_enqueue_style('ulh_wp_admin_css');
        wp_register_style('ulh_jquery_ui', plugin_dir_url(__FILE__) . 'css/jquery-ui.min.css');
        wp_enqueue_style('ulh_jquery_ui');
        
        wp_enqueue_script('ulh_wp_admin_js', plugin_dir_url(__FILE__) . 'js/jquery-ui.min.js');
    }
}

add_action('init', 'ulh_update_tables_when_plugin_updating');

function ulh_update_tables_when_plugin_updating() {
    global $wpdb;
    $oldVersion = get_option('fa_userloginhostory_version', '1.0');
    $newVersion = '1.4';

    if (!(version_compare($oldVersion, $newVersion) < 0)) {
        return FALSE;
    }

    $charset_collate = $wpdb->get_charset_collate();
    $fa_user_logins_table = $wpdb->prefix . "fa_user_logins";

    $sql = "CREATE TABLE $fa_user_logins_table (
		 id int(11) NOT NULL AUTO_INCREMENT,
   user_id int(11) ,
  `time_login` datetime NOT NULL,
  `time_logout` datetime NOT NULL,
   `time_last_seen` datetime NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `operating_system` varchar(100) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `country_code` varchar(20) NOT NULL	,		  					  
   PRIMARY KEY (`id`)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
    update_option('fa_userloginhostory_version', $newVersion);
}

/* Call when user does login. 
  Run before the headers and cookies are sent. */
add_action('after_setup_theme', 'ulh_after_setup_theme');
function ulh_after_setup_theme() {
      ulh_updateLastSeenTime();
      return;
}

/**
 * save user info on login
 */
add_action('wp_login', 'ulh_user_login');
function ulh_user_login($user_login) {
     $user = get_user_by('login', $user_login);
    wp_set_current_user($user->ID); //update the global user variables
    
    return ulh_save_user_login();
}


//call when user does logout
add_action('wp_logout', 'ulh_save_user_logout');

function ulh_save_user_logout() {
    global $wpdb, $table_prefix, $current_user;
    $userId = $current_user->ID;
    $emptyTime = '0000-00-00 00:00:00';
    $timeLogout = ulh_get_current_date_time();

    $fa_user_logins_table = $table_prefix . 'fa_user_logins';
    $sql = " select id from $fa_user_logins_table where user_id='$userId' and time_logout='$emptyTime' order by id desc limit 1 ; ";
    $results = $wpdb->get_results($sql);

    $result = $results[0];
    $id = $result->id;

    if ($id) {
        $sql = " update $fa_user_logins_table set time_logout='$timeLogout' where id=$id ; ";

        $wpdb->query($sql);
    }
}

// add main menu in admin 
add_action('admin_menu', 'ulh_add_admin_menu');

function ulh_add_admin_menu() {

    add_options_page('user-login-history-settings', ULH_PLUGIN_NAME_FA, 'manage_options', 'user-login-history-settings', 'ulh_options_page');
}

add_action('admin_init', 'ulh_settings_init');


    class FA_User_Login_History_List_Table extends ULH_List_Table {

        public function __construct() {

            parent::__construct([
                'singular' => __('User Login History', 'fauserloginhistory'), //singular name of the listed records
                'plural' => __('User Login Histories', 'fauserloginhistory'), //plural name of the listed records
                'ajax' => false //does this table support ajax?
            ]);
        }

       

        public function ulh_prepare_where_query() {
            $fields = array(
                'user_id',
                'country_name',
                'country_code',
                'browser',
                'test',
                'ip_address',
                'role',
                'date_from',
                'date_to',
           
              
                
            );
            $output = array();
            $sqlQuery = FALSE;
            $countQuery = FALSE;
            $values = array();
            $dateType = FALSE;
            
            if(isset($_GET['date_type']) && "login" == $_GET['date_type'])
            {
              $dateType = 'login'  ;
            }
            if(isset($_GET['date_type']) && "logout" == $_GET['date_type'])
            {
              $dateType = 'logout'  ;
            }
        
         
            
            foreach ($fields as $field) {
                   $dataType = "%s";
                     $operatorSign = "=";
                   
                if (isset($_GET['ulh_filter_form_submit']) && isset($_GET[$field]) && "" != $_GET[$field]) {
                    $getValue = $_GET[$field];
                    if ('user_id' == $field) {
                        $dataType = "%d";
                        $field = 'a.user_id';
                    }
                    if ('role' == $field) {
                        $field = 'meta_value';
                        $operatorSign = "LIKE";
                        $getValue = "%" . $getValue . "%";
                    }
                 
                    if($dateType)
                    {
                       if ('date_from' == $field) {
                        $field = 'time_'.$dateType;
                        $operatorSign = ">=";
                    }
                    if ('date_to' == $field) {
                        $field = 'time_'.$dateType;
                        $operatorSign = "<=";
                    }   
                    }
                  
                 

                    $sqlQuery .= " AND $field $operatorSign $dataType ";
                    $countQuery .= " AND $field $operatorSign $dataType ";

                    $values[] = $getValue;
                }
            }

            return !$sqlQuery ? FALSE : array('sql_query' => $sqlQuery, 'count_query' => $countQuery, 'values' => $values);
        }

        /**
         * Retrieve rows
         *
         * @param int $per_page
         * @param int $page_number
         *
         * @return mixed
         */
        public function get_rows($per_page = 5, $page_number = 1) {

            global $wpdb;
            $getValues = array();

            $table_name = $wpdb->prefix . "fa_user_logins";
            $table_name_usermeta = $wpdb->prefix . "usermeta";

            $sql = "select "
                    . "DISTINCT(a.id) as id, "
                    . "a.country_name,"
                    . "a.country_code, "
                    . "a.ip_address, "
                    . "a.browser, "
                    . "a.operating_system, "
                    . "a.time_login, "
                    . "a.time_logout, "
                    . "a.time_last_seen, "
                    . "b.* "
                    . "FROM " . $table_name . " AS a  "
                    . "inner join $table_name_usermeta AS b ON b.user_id=a.user_id where b.meta_key = '{$wpdb->prefix}capabilities' AND  1 ";

            $whereQuery = $this->ulh_prepare_where_query();

            if ($whereQuery) {

                $sql .= $whereQuery['sql_query'];
                $getValues = $whereQuery['values'];
            }

            if (!empty($_REQUEST['orderby'])) {
                $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
                $sql .=!empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
            } else {

                $sql .= ' ORDER BY id DESC';
            }

            $sql .= " LIMIT $per_page";
            $sql .= '  OFFSET   ' . ( $page_number - 1 ) * $per_page . "   ";

            if (!empty($getValues)) {
                return $wpdb->get_results($wpdb->prepare($sql, $getValues), 'ARRAY_A');
            }
            return $wpdb->get_results($sql, 'ARRAY_A');
        }

        /**
         * Delete a record.
         *
         * @param int $id row ID
         */
        public static function delete_record($id) {
            global $wpdb;

            $wpdb->delete(
                    "{$wpdb->prefix}fa_user_logins", [ 'id' => $id], [ '%d']
            );
        }

        /**
         * Returns the count of records in the database.
         *
         * @return null|string
         */
        public function record_count() {

            global $wpdb;
            $getValues = array();


            $table_name = $wpdb->prefix . "fa_user_logins";
            $table_name_usermeta = $wpdb->prefix . "usermeta";


            $sql = "select "
                    . "COUNT(DISTINCT(a.id))"
                    . "FROM " . $table_name . " AS a  "
                    . "inner join $table_name_usermeta AS b ON b.user_id=a.user_id where b.meta_key = '{$wpdb->prefix}capabilities' AND  1 ";

            $whereQuery = $this->ulh_prepare_where_query();

            if ($whereQuery) {

                $sql .= $whereQuery['count_query'];
                $getValues = $whereQuery['values'];
            }

            if (!empty($getValues)) {
                return $wpdb->get_var($wpdb->prepare($sql, $getValues));
            }
            return $wpdb->get_var($sql);
        }

        /** Text displayed when no record is available */
        public function no_items() {
            _e('No records avaliable.', 'fauserloginhistory');
        }

        
        public function get_admin_preferred_timezone()
        {
            $timeZone = FALSE;
            $options = get_option( 'ulh_settings' ); 
                           if(isset( $options['ulh_is_show_plugin_preferred_timezone']) && "" !=  $options['ulh_is_show_plugin_preferred_timezone'])
                           {
                              $timeZone =  $options['ulh_is_show_plugin_preferred_timezone'];   
                           }
                           return $timeZone && ""!= $timeZone?$timeZone:ULH_SERVER_DEFAULT_TIMEZONE;  

        }
        /**
         * Render a column when no column specific method exist.
         *
         * @param array $item
         * @param string $column_name
         *
         * @return mixed
         */
        public function column_default($item, $column_name) {
            $timezone = $this->get_admin_preferred_timezone();
            $currentDateTime = ulh_get_current_date_time();
          
            switch ($column_name) {
                case 'user_id':
                    return $item[$column_name];

                case 'user_login':
                    $profileLink = get_edit_user_link($item['user_id']);
                    $userData = get_userdata($item['user_id']);
                    $userLogin = $userData->user_login;
                    return "<a href= '$profileLink'>$userLogin</a>";

                case 'role':
                    $userData = get_userdata($item['user_id']);
                    return implode(',', $userData->roles);

                case 'browser':
                    return $item[$column_name];
                case 'time_login':
                    return  ulh_convertToUserTime($item[$column_name], '', $timezone);

                case 'time_logout':
                    return $item[$column_name] == '0000-00-00 00:00:00' ? 'Logged In' : ulh_convertToUserTime($item[$column_name], '', $timezone);

                case 'ip_address':
                    return $item[$column_name];
                case 'operating_system':
                    return $item[$column_name];
                case 'country_name':
                    return $item[$column_name];
                case 'country_code':
                    return $item[$column_name];

                case 'time_last_seen':
     // if user is logged out, logout time becomes last seen time
     $item[$column_name] = $item['time_logout'] != '0000-00-00 00:00:00'? $item['time_logout']:   $item[$column_name];   
     $item[$column_name] =  ulh_convertToUserTime($item[$column_name], '', $timezone);
     $currentDateTime =  ulh_convertToUserTime($currentDateTime, '', $timezone);
     return "<span title = '$item[$column_name]'>" . human_time_diff(strtotime($item[$column_name]), strtotime($currentDateTime)) . ' ago</span>';

                case 'duration':
                    return $item['time_logout'] != '0000-00-00 00:00:00' ? date('H:i:s', strtotime($item['time_logout']) - strtotime($item['time_login'])) : 'Logged In';
                    ;

                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
        }

        /**
         * Render the bulk edit checkbox
         *
         * @param array $item
         *
         * @return string
         */
        function column_cb($item) {

            return sprintf(
                    '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
            );
        }

        /**
         * Method for name column
         *
         * @param array $item an array of DB data
         *
         * @return string
         */
        function column_name($item) {
            $delete_nonce = wp_create_nonce('ulh_delete_record');

            $title = '<strong>' . $item['user_id'] . '</strong>';

            $actions = [
                'delete' => sprintf('<a href="?page=%s&action=%s&record=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
            ];

            return $title . $this->row_actions($actions);
        }

        /**
         *  Associative array of columns
         *
         * @return array
         */
        function get_columns() {
            $columns = [
                'cb' => '<input type="checkbox" />',
                'user_id' => __('User Id', 'fauserloginhistory'),
                'user_login' => __('Username', 'fauserloginhistory'),
                'role' => __('Role', 'fauserloginhistory'),
                'time_login' => __('Login', 'fauserloginhistory'),
                'time_logout' => __('Logout', 'fauserloginhistory'),
                'duration' => __('Duration', 'fauserloginhistory'),
                'time_last_seen' => __('<span title="Last seen time in the session">Last Seen At</span>', 'fauserloginhistory'),
                'ip_address' => __('IP', 'fauserloginhistory'),
                'browser' => __('Browser', 'fauserloginhistory'),
                'operating_system' => __('OS', 'fauserloginhistory'),
                'country_name' => __('Country Name', 'fauserloginhistory'),
                'country_code' => __('Country Code', 'fauserloginhistory'),
            ];


            return $columns;
        }

        /**
         * Columns to make sortable.
         *
         * @return array
         */
        public function get_sortable_columns() {
            $sortable_columns = array(
                'user_id' => array('user_id', true),
                'user_login' => array('user_login', true),
                'role' => array('user_id', true),
                'time_login' => array('time_login', false),
                'time_logout' => array('time_logout', false),
                'ip_address' => array('ip_address', false),
                'browser' => array('browser', false),
                'operating_system' => array('operating_system', false),
                'country_name' => array('country_name', false),
                'country_code' => array('country_code', false),
                'time_last_seen' => array('time_last_seen', false),
            );

            return $sortable_columns;
        }

        /**
         * Returns an associative array containing the bulk action
         *
         * @return array
         */
        public function get_bulk_actions() {
            $actions = [
                'bulk-delete' => 'Delete'
            ];

            return $actions;
        }

        /**
         * Handles data query and filter, sorting, and pagination.
         */
        public function prepare_items() {

            $this->_column_headers = $this->get_column_info();

            /** Process bulk action */
            $this->process_bulk_action();

            $per_page = $this->get_items_per_page('rows_per_page', 5);
            $current_page = $this->get_pagenum();
            $total_items = self::record_count();

            $this->set_pagination_args([
                'total_items' => $total_items, //WE have to calculate the total number of items
                'per_page' => $per_page //WE have to determine how many items to show on a page
            ]);

            $this->items = self::get_rows($per_page, $current_page);
        }

        public function process_bulk_action() {

            //Detect when a bulk action is being triggered...
            if ('delete' === $this->current_action()) {

                // In our file that handles the request, verify the nonce.
                $nonce = esc_attr($_REQUEST['_wpnonce']);

                if (!wp_verify_nonce($nonce, 'ulh_delete_record')) {
                    die('Go get a life script kiddies');
                } else {
                    self::delete_record(absint($_GET['record']));

                    // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                    // add_query_arg() return the current url
                    wp_redirect(esc_url_raw(add_query_arg()));
                    exit;
                }
            }

            // If the delete bulk action is triggered
            if (( isset($_POST['action']) && $_POST['action'] == 'bulk-delete' ) || ( isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete' )
            ) {

                $delete_ids = esc_sql($_POST['bulk-delete']);

                // loop over the array of record IDs and delete them
                foreach ($delete_ids as $id) {
                    self::delete_record($id);
                }

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                wp_redirect(esc_url_raw(add_query_arg()));
                exit;
            }
        }

    }

    class FA_User_Login_History {

        static $instance;
        // FA_User_Login_History_List_Table object
        public $fa_user_login_history_list_table_obj;

        public function __construct() {

            add_filter('set-screen-option', [ __CLASS__, 'set_screen'], 10, 3);
            add_action('admin_menu', [ $this, 'plugin_menu']);
        }

        public static function set_screen($status, $option, $value) {
            return $value;
        }

     

        public function plugin_menu() {

            $hook = add_menu_page(
                    ULH_PLUGIN_NAME_FA, ULH_PLUGIN_NAME_FA, 'manage_options', ULH_LISTING_PAGE_ADMIN, [ $this, 'plugin_settings_page']
            );

            add_action("load-$hook", [ $this, 'screen_option']);
        }


        public function plugin_settings_page() {
            ?>

  
    <script>
  jQuery(function() {
    jQuery( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' } );
  
  });
  
   jQuery(function(){
        jQuery("#date_to").datepicker({ dateFormat: 'yy-mm-dd' });
        jQuery("#date_from").datepicker({ dateFormat: 'yy-mm-dd' }).bind("change",function(){
            var minValue = jQuery(this).val();
            minValue = jQuery.datepicker.parseDate("yy-mm-dd", minValue);
            minValue.setDate(minValue.getDate()+1);
            jQuery("#date_to").datepicker( "option", "minDate", minValue );
        })
    });
  </script>
        <div class="wrap">
            <h1><?php echo ULH_PLUGIN_NAME_FA ?> v<?php echo get_option('fa_userloginhostory_version', '1.0'); ?></h1>

<div id="poststuff">
<div class="search-filter">
<form name="user-login-histoty-search-form" method="get" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
<input type="hidden" name="page" value="<?php echo ULH_LISTING_PAGE_ADMIN ?>" />
<table width="100%" class="form-table">
<tbody>
<tr>
   

    <td><input autocomplete="off" placeholder="From" id="date_from" name="date_from" value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : "" ?>" class="textfield-bg"></td>
    <td><input autocomplete="off" placeholder="To" name="date_to" id="date_to" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : "" ?>" class="textfield-bg"></td>

<td>
    
    <select name="date_type" class="selectfield-bg"> 
      
    <?php 
    $dateTypes = array('login'=>'Login', 'logout'=>'Logout');
    foreach ($dateTypes as $dateTypeKey=>$dateType) { ?>

            <option value="<?php print $dateTypeKey ?>" <?php selected($_GET['date_type'], $dateTypeKey); ?>>
            <?php echo $dateType ?>
            </option>
            <?php } ?>
    </select>

</td>

  


</tr>
</tbody></table>
<table width="100%" class="form-table">
<tbody>
<tr>
   
<td><input placeholder="Enter User Id" name="user_id" value="<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : "" ?>" class="textfield-bg"></td>
<td><input placeholder="Enter Country Name" name="country_name" value="<?php echo isset($_GET['country_name']) ? $_GET['country_name'] : "" ?>" class="textfield-bg"></td>
<td><input placeholder="Enter Country Code" name="country_code" value="<?php echo isset($_GET['country_code']) ? $_GET['country_code'] : "" ?>" class="textfield-bg"></td>
<td><input placeholder="Enter Browser" name="browser" value="<?php echo isset($_GET['browser']) ? $_GET['browser'] : "" ?>" class="textfield-bg"></td>
<td><input placeholder="Enter Ip Address" name="ip_address" value="<?php echo isset($_GET['ip_address']) ? $_GET['ip_address'] : "" ?>" class="textfield-bg"></td>
<td><select class="selectfield-bg" name="role">
<option value="">Select Role</option>
<?php
$selectedRole = isset($_GET['role']) ? $_GET['role'] : NULL;
wp_dropdown_roles($selectedRole);
?>
</select></td>

<td><input type="submit" value="Filter" name="ulh_filter_form_submit" class="go-bg"></td>


</tr>
</tbody></table>
</form>
</div>
<div id="post-body" class="metabox-holder ">
<div id="post-body-content">
<div class="meta-box-sortables ui-sortable">
<form method="post">
<?php
$this->fa_user_login_history_list_table_obj->prepare_items();
$this->fa_user_login_history_list_table_obj->display();
?>
</form>
</div>
</div>
</div>
<br class="clear">
</div>
        </div>
        <?php
    }


    public function screen_option() {

        $option = 'per_page';
        $args = [
            'label' => 'Show Records Per Page',
            'default' => 5,
            'option' => 'rows_per_page'
        ];

        add_screen_option($option, $args);

        $this->fa_user_login_history_list_table_obj = new FA_User_Login_History_List_Table();
    }

    //Singleton instance
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}

add_action('plugins_loaded', function () {
    FA_User_Login_History::get_instance();
});

//settings page
function ulh_settings_init() {

    register_setting('pluginPage', 'ulh_settings');


    add_settings_section(
            'ulh_pluginPage_section_notice', __('Hide/Show Plugin Notice', 'fauserloginhistory'), 'ulh_settings_section_callback', 'pluginPage'
    );
    add_settings_section(
            'ulh_pluginPage_section_preferred_timezone', __('Preferred Timezone', 'fauserloginhistory'), 'ulh_settings_section_callback_preffered_timezone', 'pluginPage'
    );

    add_settings_field(
            'ulh_is_show_plugin_notice', __('Show Plugin Notice', 'fauserloginhistory'), 'ulh_checkbox_notice_render', 'pluginPage', 'ulh_pluginPage_section_notice'
    );
    add_settings_field(
            'ulh_is_show_plugin_preferred_timezone', __('Select Preferred Timezone', 'fauserloginhistory'), 'ulh_checkbox_preferred_timezone_render', 'pluginPage', 'ulh_pluginPage_section_preferred_timezone'
    );
}

function ulh_checkbox_notice_render() {

    $options = get_option('ulh_settings');
    $options['ulh_is_show_plugin_notice'] = isset($options['ulh_is_show_plugin_notice']) ? $options['ulh_is_show_plugin_notice'] : 0;
    ?>
    <input type='checkbox' name='ulh_settings[ulh_is_show_plugin_notice]' <?php checked($options['ulh_is_show_plugin_notice'], 1); ?> value='1'>
    <?php
}

function ulh_checkbox_preferred_timezone_render() {

    $options = get_option('ulh_settings');
    $options['ulh_is_show_plugin_preferred_timezone'] = isset($options['ulh_is_show_plugin_preferred_timezone']) ? $options['ulh_is_show_plugin_preferred_timezone'] : 0;
    ?>
    <select name="ulh_settings[ulh_is_show_plugin_preferred_timezone]" style="font-family: 'Courier New', Courier, monospace; width: 450px;">
        <option value="0">Select Timezone</option>
    <?php foreach (ulh_get_time_zone_list() as $t) { ?>

            <option value="<?php print $t['zone'] ?>" <?php selected($options['ulh_is_show_plugin_preferred_timezone'], $t['zone']); ?>>
            <?php echo $t['zone'] . "(" . $t['diff_from_GMT'] . ")" ?>
            </option>
            <?php } ?>
    </select>
        <?php
    }

    function ulh_settings_section_callback_preffered_timezone() {

        echo __('Set this option to convert the saved logged-in time into the preferred Timezone and then show on User Login History Listing table.<br>NOTE: To make the Timezone fix/static the plugin uses UTC that will be used to save user login time into database.', 'fauserloginhistory');
    }

    function ulh_settings_section_callback() {
        return;
    }

    function ulh_options_page() {
        ?>
    <h1>Settings :: <strong><?php echo ULH_PLUGIN_NAME_FA ?> v<?php echo get_option('fa_userloginhostory_version', '1.0'); ?></strong></h1>
    <form action='options.php' method='post'>
    <?php
    settings_fields('pluginPage');
    do_settings_sections('pluginPage');
    submit_button();
    ?>

    </form>
        <?php
    }
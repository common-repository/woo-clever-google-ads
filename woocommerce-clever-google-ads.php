<?php
/**
 * Plugin Name: Clever Ecommerce Ads on Google for WooCommerce
 * Plugin URI:  cleverecommerce.com
 * Description: Get your ad on Google with a Premium Google Partner. With just 5 simple steps your campaigns will be on the Adwords search network, thanks to the technology of Clever. No work from your side. We will upload all campaigns for you.
 * Version:     4.6
 * Author:      Clever Ecommerce
 * Author URI:  
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woo-clever-google-ads
 * Domain Path: /languages 
 */

/**
 * Copyright: © 2018 CleverPPC.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}else{
    $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
    if (!stripos(implode($all_plugins), 'woocommerce.php')) {
        exit('Ups, you need WooCommerce to run our plugin!');
    }
}


define('CLEVERPPC_VERSION', '1.2.3');
define('CLEVERPPC_MIN_WOOCOMMERCE', '2.6');
define('CLEVERPPC_PATH', plugin_dir_path(__FILE__));
define('CLEVERPPC_LINK', plugin_dir_url(__FILE__));
define('CLEVERPPC_PLUGIN_NAME', plugin_basename(__FILE__));


class CLEVERPPC_STARTER {

    private $support_time = 1519919054; // Date of the old < 3.3 version support
    private $default_woo_version = 2.9;
    private $actualized = 0.0;
    private $version_key = "cleverppc_woo_version";
    private $_cleverppc = null;

    public function __construct() {
        $this->actualized = floatval(get_option($this->version_key, $this->default_woo_version));
    }

    public function update_version() {
        if (defined('WOOCOMMERCE_VERSION') AND ( $this->actualized !== floatval(WOOCOMMERCE_VERSION))) {
            update_option('cleverppc_woo_version', WOOCOMMERCE_VERSION);
        }
    }

    public function get_actual_obj() {
        if ($this->_cleverppc != null) {
            return $this->_cleverppc;
        }
        include_once CLEVERPPC_PATH . 'classes/admin_settings.php';      
        $this->_cleverppc = new CLEVERPPC();
        return $this->_cleverppc;
    }

    static function install() {
        global $wpdb;
        $trk = self::genKey(7);
        include_once( 'includes/class-clever-auth.php' );
        $cleverAuth = new Clever_Auth();
        $domain = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='siteurl'" );
        $result = $cleverAuth->generate_keys( __( 'Clever Adwords', 'wc_cleverppc' ), $domain, 'read_write' );
        // $wpdb->insert( 
        //     $wpdb->prefix.'woocommerce_api_keys', 
        //     array( 
        //         'user_id' => 3,
        //         'description' => "cleverppc_api",
        //         'permissions' => "read_write",
        //         'consumer_key' => $result['consumer_key'],
        //         'consumer_secret' => $result['consumer_secret'],
        //         'truncated_key' => $trk,
        //         'last_access' => date('Y-m-d H:i:s')
        //     ),
        //     array( 
        //         '%d',
        //         '%s',
        //         '%s',
        //         '%s',
        //         '%s',
        //         '%s',
        //         '%s'
        //     ) 
        // );
        // Create table wp_wc_CleverPPC and generate an account_id
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.'wc_cleveradwords';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
            $sql = "CREATE TABLE $table_name (
            account_id varchar(255) NOT NULL
            ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            $key = self::genKey(32);
            $wpdb->insert( 
                $table_name, 
                array('account_id' => $key),
                array('%s') 
            );  
        }
        //Enable API
        $wpdb->update( 
                    $wpdb->prefix.'options', 
                    array(
                            'option_value' => 'yes',
                            'autoload' => 'yes'
                        ),
                    array(
                            'option_name' => 'woocommerce_api_enabled'
                        ), 
                    array(
                            '%s',
                            '%s'
                        ), 
                    array(
                            '%s'
                        ) 
                    );
        $auth_token = self::getAuthenticationToken();
        $headers = "Authorization: {$auth_token['result']}";
        $data = self::getInformation();
        $data += ['access_token' => $result['consumer_key'], 'secret' => $result['consumer_secret']];
        #$data = self::generatePayload();
        self::request('create_shop', 'POST', $data, $headers);
    }

    static function uninstall() {
        $auth_token = self::getAuthenticationToken();
        $headers = "Authorization: {$auth_token['result']}";
        $data = self::getInformation();
        self::request('uninstall_shop', 'POST', $data, $headers);
    }

    private static function request($endPoint, $verb, $data, $headers){
        $url = "https://woocommerce.cleverecommerce.com/api/woocommerce/{$endPoint}"; 
        $args = array(
            'headers'     => array(
                'Authorization' => $headers ,
                'Prueba' => 'prueba',
            ),
            'body' => $data,
        ); 

        $result = wp_remote_post($url, $args );
        $body = wp_remote_retrieve_body( $result );
        return $body;
    }

    private static function getAuthData(){
        return array('email' => 'woocommerce@cleverppc.com', 'password' => 'cleverppc');
    }

    private static function getAuthenticationToken(){
        try {
            // Prepare auth data
            $_data = self::getAuthData();
            // Perform request and get raw response object
            $_response = self::request('authenticate', 'POST', $_data, '');
            // Decoding response data
            $_decoded_data = self::decodeResponse($_response);
            // Setting auth token
            //define('CLEVERPPC_AUTH_TOKEN', $_decoded_data->auth_token);
            // Setting result
            $_result = array('result' => $_decoded_data->auth_token, 'code' => '200');
        } catch (RequestException $e) {
            // Call to Roll-bar, later on
            $_result = array('result' => false, 'code' => $e->getCode(), 'message' => $e->getMessage());
        }
        return $_result;
    }

    protected static function decodeResponse($response){
        return json_decode($response, false);
    }

    private static function genKey($size){
        $key = '';
        /* There are no O/0 in the codes in order to avoid confusion */
        $chars = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
        for ($i = 1; $i <= $size; ++$i) {
            $key .= $chars[rand(0, 33)];
        }
        return $key;
    }

    // In progess, not proved
    public static function generatePayload(){
        global $wpdb;
        $account_id = $wpdb->get_var( "SELECT account_id FROM {$wpdb->prefix}wc_cleveradwords" );
        $email = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='admin_email'" );
        $payload = array('store_hash' => $account_id,
                                'timestamp' => time(),
                                'email' => $email);
        return $payload;
    }

    public static function generateHmac(){
        $payload = self::generatePayload();
        $encoded = json_encode($payload);
        $encoded_payload = base64_encode($encoded);
        $hash_mac = hash_hmac(self::getHashMacAlgorithm(), $encoded, self::getHashSecret());
        $payload_signature = base64_encode($hash_mac);
        $hmac = "{$encoded_payload}.{$payload_signature}";
        return $hmac;
    }

    public static function getHashMacAlgorithm(){
        return 'sha256';
    }

    public static function getHashSecret(){
        return '4n7fdidvdrzvwe5hb0i4blohf4d8crc';
    }

    //cogiendo siempre el primer dato de cada "array" que me devuelven las consultas de la base de datos
    private static function getInformation(){
        global $wpdb;
        $name = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='blogname'" );
        $domain = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='siteurl'" );
        $email = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='admin_email'" );
        $countries_zones = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones" ); //la zona que tu quieras escribir
        $countries_locations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_locations" ); //la localizacion ES,FR, everywhere...
        $currency = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='woocommerce_currency'" );
        $language = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='WPLANG'" );
        $shop_country = $wpdb->get_var( "SELECT option_value FROM {$wpdb->prefix}options where option_name='woocommerce_default_country'" );
        // $apikey = $wpdb->get_var( "SELECT consumer_key FROM {$wpdb->prefix}woocommerce_api_keys where description='cleverppc_api'" );
        // $apisecret = $wpdb->get_var( "SELECT consumer_secret FROM {$wpdb->prefix}woocommerce_api_keys where description='cleverppc_api'" );
        $cleverppc_data = $wpdb->get_var( "SELECT account_id FROM {$wpdb->prefix}wc_cleveradwords" );
        $countries[]=array();
        foreach($countries_zones as $zone){
            $flag=FALSE;
            foreach($countries_locations as $location){
                if(($zone->zone_id) == ($location->zone_id) and !in_array(substr($location->location_code, 0,2),$countries)){
                    $flag=TRUE;
                    array_push($countries,substr($location->location_code, 0,2));
                }
            }
            if($flag==FALSE and !in_array("*",$countries)){
                array_push($countries,"*");
            }
        }
        $find = "_";
        if($language != null){
            $pos = strpos($language, $find);
            if($pos !== false){
                $language = substr($language, 0, $pos);
            }
        }else{
            $language = "en";
        }
        $find2 = ":";
        if($shop_country != null){
            $pos2 = strpos($shop_country, $find2);
            if($pos2 !== false){
                $shop_country = substr($shop_country, 0, $pos2);
            }
        }
        $_store = array(
            'name' => $name,
            'domain' =>  $domain,
            'email' => $email,
            'countries' => $countries, //implode(',', $countries->zone_name)
            'logo_url' => '',
            'platform' => 'woocommerce',
            'currency' => $currency,
            'language' => $language, //cojo el primer language pero si tiene otros plugins instalados puede tener multilanguage
            'client_id' => $cleverppc_data,
            'shop_country' => $shop_country
        );
        return $_store;
    }

    //cogiendo siempre el primer dato de cada "array" que me devuelven las consultas de la base de datos
}

register_activation_hook( __FILE__, array( 'CLEVERPPC_STARTER', 'install' ) );
register_deactivation_hook( __FILE__, array( 'CLEVERPPC_STARTER', 'uninstall' ) );
register_uninstall_hook( __FILE__, array( 'CLEVERPPC_STARTER', 'uninstall' ) );
$CLEVERPPC_STARTER = new CLEVERPPC_STARTER();

$CLEVERPPC = $CLEVERPPC_STARTER->get_actual_obj();
$hmac = $CLEVERPPC_STARTER->generateHmac();
define('CLEVERPPC_HMAC', $hmac);
$GLOBALS['CLEVERPPC'] = $CLEVERPPC;
add_action('init', array($CLEVERPPC, 'init'), 1);

?>

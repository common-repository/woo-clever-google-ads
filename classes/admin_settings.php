<?php

final class CLEVERPPC {

    public function __construct() {
        add_action( 'admin_menu', array($this, 'google_add_admin_menu'), 9999);
        add_action('woocommerce_settings_tabs_array', array($this, 'woocommerce_settings_tabs_array'), 9999);
        add_action('woocommerce_settings_tabs_cleverppc', array($this, 'print_plugin_options'), 9999);
        add_action( 'admin_enqueue_scripts', array($this,'wpdocs_selectively_enqueue_admin_script') );
    }



    public function init() {
        if (!class_exists('WooCommerce')) {
            return;
        }
    }

    public function google_add_admin_menu() { 

        add_menu_page( 'Clever Ecommerce Ads on Google', 'Clever Ecommerce Ads on Google', 'manage_options', 'clever_ecommerce_ads_on_google_for_woocommerce', array($this, 'print_plugin_options') );

    }

    function wpdocs_selectively_enqueue_admin_script($hook) {
        if ( 'toplevel_page_clever_ecommerce_ads_on_google_for_woocommerce' != $hook ) {
        return;
    }
        wp_enqueue_style( 'my_custom_styles', plugin_dir_url( __FILE__ ) . 'styles.css', array(), '1.0' );
        wp_enqueue_style( 'my_custom_font', plugin_dir_url( __FILE__ ) . 'font.css', array(), '1.0' );
        wp_enqueue_style( 'bootstrap_css', plugin_dir_url( __FILE__ ) . 'bootstrap.min.css', array(), '1.0' );
        wp_enqueue_script( 'bootrstrap_js', plugin_dir_url( __FILE__ ) . 'bootstrap.min.js', array(), '1.0' );
    }


    public function woocommerce_settings_tabs_array($tabs) {
        $tabs['cleverppc'] = __('WC Clever Google Ads', 'woocommerce-currency');
        return $tabs;
    }

    public function render_html($pagepath, $data = array()) {
        @extract($data);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }
   
    public function print_plugin_options() {
        $allowed_html_tags = wp_kses_allowed_html( 'post' );
        $iframe_html = "<iframe src='https://woocommerce.cleverecommerce.com/?hmac=" . esc_attr(CLEVERPPC_STARTER::generateHmac()) . "'  width='100%' height='800' frameborder='0' style='position: relative;  webkitallowfullscreen mozallowfullscreen allowfullscreen'></iframe>";
        $allowed_html_tags['iframe'] = array(
          'src'             => true,
          'height'          => true,
          'width'           => true,
          'frameborder'     => true,
          'allowfullscreen' => true,
          'style'           => true,
        );
        echo wp_kses($iframe_html, $allowed_html_tags);
    }

}

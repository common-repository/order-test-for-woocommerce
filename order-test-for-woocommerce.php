<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 * 
 * @link              https://bluecoral.vn
 * @since             1.0
 * @package           Order_Test_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Order Test For Woocommerce
 * Plugin URI:        https://bluecoral.vn
 * Description:       Description
 * Version:           1.0
 * Author:            Blue Coral
 * Author URI:        https://bluecoral.vn
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       order-test-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bluecoral_Order_Test_For_Woocommerce {

    public static $PLUGIN_FILE =  __FILE__;
    public static $PLUGIN_VERSION = "1.0";

    public function __construct() {

        require_once Bluecoral_Order_Test_For_Woocommerce::getPluginDir() . "payments.php";

        add_filter( 'woocommerce_payment_gateways', array( $this, 'init_new_gateway' ) );
        add_filter('woocommerce_checkout_redirect_empty_cart', array( $this, 'check_cart_empty' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'setup_checkout_billing_default_data' ) );
    }

    public static function getPluginDir() {
        return plugin_dir_path( self::$PLUGIN_FILE );
    }
    
    public static function getPluginUrl() {
        return plugin_dir_url( self::$PLUGIN_FILE );
    }

    /*
     * This action hook registers our PHP class as a WooCommerce payment gateway
     */
    public function init_new_gateway( $gateways ) {
        $gateways = array_merge( $gateways, array( 'Order_Test_For_Woocommerce' ) );
        return $gateways;
    }

    public function check_cart_empty( $not_redirect_with_empty_cart ) {
        // Check if the cart is empty
        if ( is_checkout() && WC()->cart->is_empty() ) {
            $gateway = new Order_Test_For_Woocommerce();
            $enabled_product = $gateway->get_form_field_data('enabled_product');

            if ( $enabled_product == 'yes') {
                $quantity = isset( $_GET["quantity"] ) && !empty( sanitize_text_field( $_GET["quantity"] ) ) ? sanitize_text_field( $_GET["quantity"] ) : 1;

                $product_id = $gateway->get_form_field_data('product');
                WC()->cart->add_to_cart( $product_id, $quantity );
                return false;
            }
            return $not_redirect_with_empty_cart;
        }
        return $not_redirect_with_empty_cart;
    }

    public function setup_checkout_billing_default_data( $fields ) {

        $gateway = new Order_Test_For_Woocommerce();
        $enabled_billing_form = $gateway->get_form_field_data('enabled_billing_form');
        if ( $enabled_billing_form == "yes" ) {
            $first_name = $gateway->get_form_field_data('first_name');
            $last_name = $gateway->get_form_field_data('last_name');
            $phone = $gateway->get_form_field_data('phone');
            $email = $gateway->get_form_field_data('email');
            $city = $gateway->get_form_field_data('city');
            $address1 = $gateway->get_form_field_data('address1');
            $country = $gateway->get_form_field_data('country');

            $fields['billing']['billing_first_name']['default'] = $first_name;
            $fields['billing']['billing_last_name']['default'] = $last_name;
            $fields['billing']['billing_country']['default'] = $country;
            $fields['billing']['billing_address_1']['default'] = $address1;
            $fields['billing']['billing_city']['default'] = $city;
            $fields['billing']['billing_phone']['default'] = $phone;
            $fields['billing']['billing_email']['default'] = $email;
        }
        // Return the modified fields
        return $fields;
    }
}

add_action( 'plugins_loaded', function() {
    new Bluecoral_Order_Test_For_Woocommerce();
} );

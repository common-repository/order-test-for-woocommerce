<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'WC_Payment_Gateway') ) {
    class Order_Test_For_Woocommerce extends WC_Payment_Gateway {

        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct() {

            $this->id                   = 'order-test-for-woocommerce'; // payment gateway plugin ID
            $this->enabled              = $this->get_option( 'enabled' );
            $this->enabled_product      = $this->get_option( 'enabled_product' );
            $this->enabled_billing_form = $this->get_option( 'enabled_billing_form' );
            $this->title                = "Order Test For Woocommerce";
            $this->has_fields           = true; // in case you need a custom credit card form
            $this->method_title         = 'Order Test For Woocommerce';
            $this->method_description   = 'You can quick open checkout by link {site-url}/checkout'; // will be displayed on the options page
            $this->version              = "1.0";
            $this->product              = $this->get_option( 'product' );
            $this->first_name           = $this->get_option( 'first_name' );
            $this->last_name            = $this->get_option( 'last_name' );
            $this->phone                = $this->get_option( 'phone' );
            $this->email                = $this->get_option( 'email' );
            $this->city                 = $this->get_option( 'city' );
            $this->address1             = $this->get_option( 'address1' );
            $this->country              = $this->get_option( 'country' );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

            add_action( 'woocommerce_after_checkout_validation', array( $this, 'validation_order_test_status' ), 10, 2 );
 

            // You can also register a webhook here
            // add_action( 'woocommerce_api_order_test_for_woocommerce', array( $this, 'webhook' ) );
        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields() {

            $args = [
                'status'    => 'publish',
                'orderby' => 'name',
                'order'   => 'ASC',
                'limit' => -1,
            ];

            $products = array();
            $products[""] = "-- Select product --";
            $all_products = wc_get_products($args);

            foreach ( $all_products as $key => $product ) {

                if ( $product->get_type() == "variable" ) {
                    $child_ids = get_children( array(
                        'post_parent' => $product->get_id(),
                        'post_type' => 'product_variation',
                        'fields' => 'ids'
                    ));
                    foreach ($child_ids as $child_id) {
                        $products[$child_id] = get_the_title($child_id);
                    }
                }
                else {
                    $products[$product->get_id()] = $product->get_title();
                }
            }
            $this->form_fields = array(
                'enabled' => array(
                    'label'       => 'Enable Order Test For Woocommerce',
                    'type'        => 'checkbox',
                    'description' => '',
                    'class'       => 'hidden',
                    'default'     => 'no'
                ),
                'enabled_product' => array(
                    'label'       => 'Product default in Checkout',
                    'type'        => 'checkbox',
                    'description' => 'You can quickly open the checkout page by clicking on the following link: <a href="'. get_home_url() .'/checkout" target="_blank">'. get_home_url() .'/checkout</a>',
                    'class'       => 'hidden',
                    'default'     => 'no'
                ),
                'product' => array(
                    'label'       => 'Product default in Checkout',
                    'type' => 'select',
                    'label' => __('Product', 'woocommerce'),
                    'options' => $products,
                ),
                'enabled_billing_form' => array(
                    'label'       => 'Billing default',
                    'type'        => 'checkbox',
                    'description' => '',
                    'class'       => 'hidden',
                    'default'     => 'no'
                ),
                'first_name' => array(
                    'title'       => 'First Name',
                    'type'        => 'text',
                    'default'     => 'Blue Coral'
                ),
                'last_name' => array(
                    'title'       => 'Last Name',
                    'type'        => 'text',
                    'default'     => 'Digital agency in Saigon'
                ),
                'phone' => array(
                    'title'       => 'Phone Number',
                    'type'        => 'text',
                    'default'     => '0123456789'
                ),
                'email' => array(
                    'title'       => 'Email',
                    'type'        => 'text',
                    'default'     => 'support@bluecoral.vn'
                ),
                'address1' => array(
                    'title'       => 'Address',
                    'type'        => 'text',
                    'default'     => '123 Street A'
                ),
                'city' => array(
                    'title'       => 'City',
                    'type'        => 'text',
                    'default'     => 'Ho Chi Minh'
                ),
                'country' => array(
                    'title'       => 'Country',
                    'type' => 'select',
                    'label' => __('Country', 'woocommerce'),
                    'options' => $this->get_countries(),
                    'default' => 'VN'
                ),
            );
        }

        public function admin_scripts() {
            wp_enqueue_script( $this->id . '-admin-js', Bluecoral_Order_Test_For_Woocommerce::getPluginUrl() . '/dashboard.js', array( 'jquery' ), $this->version, true );
        }

        function validation_order_test_status( $fields, $errors ){
 
            $nonce_value = wc_get_var( 
                sanitize_text_field( $_REQUEST['woocommerce-process-checkout-nonce'] ), 
                wc_get_var( sanitize_text_field( $_REQUEST['_wpnonce'] ), '' ) 
            );

            if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
                $errors->add( 'validation_nonce_bcr_order_test', 'Nonce validation failed!' );
            }

            if ( $fields["payment_method"] == $this->id && !isset( $_POST["order_test_status"] ) ) {
                if ( !$errors->has_errors( 'validation_bcr_order_test' ) ) {
                    $errors->add( 'validation_bcr_order_test', 'Order Test Status is required!' );
                }
            }
        }

        public function payment_fields() {
            ?>
                <div class="woocommerce-status">
                    <p>Set status for order: </p>
                    <label> <input type="radio" name="order_test_status" value="cancelled"> Cancelled</label> <br/>
                    <label> <input type="radio" name="order_test_status" value="failed"> Failed</label> <br/>
                    <label> <input type="radio" name="order_test_status" value="refunded"> Refunded</label> <br/>
                    <label> <input type="radio" name="order_test_status" value="on-hold"> On hold</label> <br/>
                    <label> <input type="radio" name="order_test_status" value="processing"> Processing</label> <br/>
                    <label> <input type="radio" name="order_test_status" value="completed"> Completed</label> <br/>
                </div>
            <?php
        }

        public function process_payment( $order_id ) {

            $nonce_value = wc_get_var( 
                            sanitize_text_field( $_REQUEST['woocommerce-process-checkout-nonce'] ), 
                            wc_get_var( sanitize_text_field( $_REQUEST['_wpnonce'] ), '' ) 
                        );

            if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
                wc_add_notice( "Validation failed!", 'error' );
                return array(
                    'result' => 'failure'
                );
            }
            $order = wc_get_order( $order_id );

            // wc-processing
            // wc-on-hold
            // wc-refunded
            // wc-completed
            // wc-cancelled
            // wc-failed
            $order_test_status = sanitize_text_field( $_POST["order_test_status"] );
            $order->set_status( 'wc-' . $order_test_status );
            $order->save();

            $redirect_url = $this->get_return_url( $order );

            $payment_url = add_query_arg( $args, Bluecoral_Order_Test_For_Woocommerce::getPluginUrl() . "status.php" );
            
            return array(
                'result' => 'success',
                'redirect' => $redirect_url
            );
        }

        public function get_form_field_data($field) {
            return $this->$field;
        }

        function get_countries() {
            $country = array();
            $country["AF"] = "Afghanistan";
            $country["AX"] = "Åland Islands";
            $country["AL"] = "Albania";
            $country["DZ"] = "Algeria";
            $country["AS"] = "American Samoa";
            $country["AD"] = "Andorra";
            $country["AO"] = "Angola";
            $country["AI"] = "Anguilla";
            $country["AQ"] = "Antarctica";
            $country["AG"] = "Antigua and Barbuda";
            $country["AR"] = "Argentina";
            $country["AM"] = "Armenia";
            $country["AW"] = "Aruba";
            $country["AU"] = "Australia";
            $country["AT"] = "Austria";
            $country["AZ"] = "Azerbaijan";
            $country["BS"] = "Bahamas";
            $country["BH"] = "Bahrain";
            $country["BD"] = "Bangladesh";
            $country["BB"] = "Barbados";
            $country["BY"] = "Belarus";
            $country["PW"] = "Belau";
            $country["BE"] = "Belgium";
            $country["BZ"] = "Belize";
            $country["BJ"] = "Benin";
            $country["BM"] = "Bermuda";
            $country["BT"] = "Bhutan";
            $country["BO"] = "Bolivia";
            $country["BQ"] = "Bonaire, Saint Eustatius and Saba";
            $country["BA"] = "Bosnia and Herzegovina";
            $country["BW"] = "Botswana";
            $country["BV"] = "Bouvet Island";
            $country["BR"] = "Brazil";
            $country["IO"] = "British Indian Ocean Territory";
            $country["BN"] = "Brunei";
            $country["BG"] = "Bulgaria";
            $country["BF"] = "Burkina Faso";
            $country["BI"] = "Burundi";
            $country["KH"] = "Cambodia";
            $country["CM"] = "Cameroon";
            $country["CA"] = "Canada";
            $country["CV"] = "Cape Verde";
            $country["KY"] = "Cayman Islands";
            $country["CF"] = "Central African Republic";
            $country["TD"] = "Chad";
            $country["CL"] = "Chile";
            $country["CN"] = "China";
            $country["CX"] = "Christmas Island";
            $country["CC"] = "Cocos (Keeling) Islands";
            $country["CO"] = "Colombia";
            $country["KM"] = "Comoros";
            $country["CG"] = "Congo (Brazzaville)";
            $country["CD"] = "Congo (Kinshasa)";
            $country["CK"] = "Cook Islands";
            $country["CR"] = "Costa Rica";
            $country["HR"] = "Croatia";
            $country["CU"] = "Cuba";
            $country["CW"] = "Curaçao";
            $country["CY"] = "Cyprus";
            $country["CZ"] = "Czech Republic";
            $country["DK"] = "Denmark";
            $country["DJ"] = "Djibouti";
            $country["DM"] = "Dominica";
            $country["DO"] = "Dominican Republic";
            $country["EC"] = "Ecuador";
            $country["EG"] = "Egypt";
            $country["SV"] = "El Salvador";
            $country["GQ"] = "Equatorial Guinea";
            $country["ER"] = "Eritrea";
            $country["EE"] = "Estonia";
            $country["SZ"] = "Eswatini";
            $country["ET"] = "Ethiopia";
            $country["FK"] = "Falkland Islands";
            $country["FO"] = "Faroe Islands";
            $country["FJ"] = "Fiji";
            $country["FI"] = "Finland";
            $country["FR"] = "France";
            $country["GF"] = "French Guiana";
            $country["PF"] = "French Polynesia";
            $country["TF"] = "French Southern Territories";
            $country["GA"] = "Gabon";
            $country["GM"] = "Gambia";
            $country["GE"] = "Georgia";
            $country["DE"] = "Germany";
            $country["GH"] = "Ghana";
            $country["GI"] = "Gibraltar";
            $country["GR"] = "Greece";
            $country["GL"] = "Greenland";
            $country["GD"] = "Grenada";
            $country["GP"] = "Guadeloupe";
            $country["GU"] = "Guam";
            $country["GT"] = "Guatemala";
            $country["GG"] = "Guernsey";
            $country["GN"] = "Guinea";
            $country["GW"] = "Guinea-Bissau";
            $country["GY"] = "Guyana";
            $country["HT"] = "Haiti";
            $country["HM"] = "Heard Island and McDonald Islands";
            $country["HN"] = "Honduras";
            $country["HK"] = "Hong Kong";
            $country["HU"] = "Hungary";
            $country["IS"] = "Iceland";
            $country["IN"] = "India";
            $country["ID"] = "Indonesia";
            $country["IR"] = "Iran";
            $country["IQ"] = "Iraq";
            $country["IE"] = "Ireland";
            $country["IM"] = "Isle of Man";
            $country["IL"] = "Israel";
            $country["IT"] = "Italy";
            $country["CI"] = "Ivory Coast";
            $country["JM"] = "Jamaica";
            $country["JP"] = "Japan";
            $country["JE"] = "Jersey";
            $country["JO"] = "Jordan";
            $country["KZ"] = "Kazakhstan";
            $country["KE"] = "Kenya";
            $country["KI"] = "Kiribati";
            $country["KW"] = "Kuwait";
            $country["KG"] = "Kyrgyzstan";
            $country["LA"] = "Laos";
            $country["LV"] = "Latvia";
            $country["LB"] = "Lebanon";
            $country["LS"] = "Lesotho";
            $country["LR"] = "Liberia";
            $country["LY"] = "Libya";
            $country["LI"] = "Liechtenstein";
            $country["LT"] = "Lithuania";
            $country["LU"] = "Luxembourg";
            $country["MO"] = "Macao";
            $country["MG"] = "Madagascar";
            $country["MW"] = "Malawi";
            $country["MY"] = "Malaysia";
            $country["MV"] = "Maldives";
            $country["ML"] = "Mali";
            $country["MT"] = "Malta";
            $country["MH"] = "Marshall Islands";
            $country["MQ"] = "Martinique";
            $country["MR"] = "Mauritania";
            $country["MU"] = "Mauritius";
            $country["YT"] = "Mayotte";
            $country["MX"] = "Mexico";
            $country["FM"] = "Micronesia";
            $country["MD"] = "Moldova";
            $country["MC"] = "Monaco";
            $country["MN"] = "Mongolia";
            $country["ME"] = "Montenegro";
            $country["MS"] = "Montserrat";
            $country["MA"] = "Morocco";
            $country["MZ"] = "Mozambique";
            $country["MM"] = "Myanmar";
            $country["NA"] = "Namibia";
            $country["NR"] = "Nauru";
            $country["NP"] = "Nepal";
            $country["NL"] = "Netherlands";
            $country["NC"] = "New Caledonia";
            $country["NZ"] = "New Zealand";
            $country["NI"] = "Nicaragua";
            $country["NE"] = "Niger";
            $country["NG"] = "Nigeria";
            $country["NU"] = "Niue";
            $country["NF"] = "Norfolk Island";
            $country["KP"] = "North Korea";
            $country["MK"] = "North Macedonia";
            $country["MP"] = "Northern Mariana Islands";
            $country["NO"] = "Norway";
            $country["OM"] = "Oman";
            $country["PK"] = "Pakistan";
            $country["PS"] = "Palestinian Territory";
            $country["PA"] = "Panama";
            $country["PG"] = "Papua New Guinea";
            $country["PY"] = "Paraguay";
            $country["PE"] = "Peru";
            $country["PH"] = "Philippines";
            $country["PN"] = "Pitcairn";
            $country["PL"] = "Poland";
            $country["PT"] = "Portugal";
            $country["PR"] = "Puerto Rico";
            $country["QA"] = "Qatar";
            $country["RE"] = "Reunion";
            $country["RO"] = "Romania";
            $country["RU"] = "Russia";
            $country["RW"] = "Rwanda";
            $country["ST"] = "São Tomé and Príncipe";
            $country["BL"] = "Saint Barthélemy";
            $country["SH"] = "Saint Helena";
            $country["KN"] = "Saint Kitts and Nevis";
            $country["LC"] = "Saint Lucia";
            $country["SX"] = "Saint Martin (Dutch part)";
            $country["MF"] = "Saint Martin (French part)";
            $country["PM"] = "Saint Pierre and Miquelon";
            $country["VC"] = "Saint Vincent and the Grenadines";
            $country["WS"] = "Samoa";
            $country["SM"] = "San Marino";
            $country["SA"] = "Saudi Arabia";
            $country["SN"] = "Senegal";
            $country["RS"] = "Serbia";
            $country["SC"] = "Seychelles";
            $country["SL"] = "Sierra Leone";
            $country["SG"] = "Singapore";
            $country["SK"] = "Slovakia";
            $country["SI"] = "Slovenia";
            $country["SB"] = "Solomon Islands";
            $country["SO"] = "Somalia";
            $country["ZA"] = "South Africa";
            $country["GS"] = "South Georgia/Sandwich Islands";
            $country["KR"] = "South Korea";
            $country["SS"] = "South Sudan";
            $country["ES"] = "Spain";
            $country["LK"] = "Sri Lanka";
            $country["SD"] = "Sudan";
            $country["SR"] = "Suriname";
            $country["SJ"] = "Svalbard and Jan Mayen";
            $country["SE"] = "Sweden";
            $country["CH"] = "Switzerland";
            $country["SY"] = "Syria";
            $country["TW"] = "Taiwan";
            $country["TJ"] = "Tajikistan";
            $country["TZ"] = "Tanzania";
            $country["TH"] = "Thailand";
            $country["TL"] = "Timor-Leste";
            $country["TG"] = "Togo";
            $country["TK"] = "Tokelau";
            $country["TO"] = "Tonga";
            $country["TT"] = "Trinidad and Tobago";
            $country["TN"] = "Tunisia";
            $country["TR"] = "Turkey";
            $country["TM"] = "Turkmenistan";
            $country["TC"] = "Turks and Caicos Islands";
            $country["TV"] = "Tuvalu";
            $country["UG"] = "Uganda";
            $country["UA"] = "Ukraine";
            $country["AE"] = "United Arab Emirates";
            $country["GB"] = "United Kingdom (UK)";
            $country["US"] = "United States (US)";
            $country["UM"] = "United States (US) Minor Outlying Islands";
            $country["UY"] = "Uruguay";
            $country["UZ"] = "Uzbekistan";
            $country["VU"] = "Vanuatu";
            $country["VA"] = "Vatican";
            $country["VE"] = "Venezuela";
            $country["VN"] = "Vietnam";
            $country["VG"] = "Virgin Islands (British)";
            $country["VI"] = "Virgin Islands (US)";
            $country["WF"] = "Wallis and Futuna";
            $country["EH"] = "Western Sahara";
            $country["YE"] = "Yemen";
            $country["ZM"] = "Zambia";
            $country["ZW"] = "Zimbabwe";
            return $country;
        }
        
    }
}
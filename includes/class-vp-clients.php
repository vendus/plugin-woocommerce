<?php

class Vendus_Plugin_Clients
{
    public function __construct()
    {
        
    }

    static public function run()
    {
        self::addFieldBilling();
        self::addFieldOrderAdminPanel();
        self::addJs();
        self::addFieldUserEdit();
        self::addFieldPageFinishShop();
        self::addFieldEmails();
        self::addFieldRestApi();
        self::validations();
    }

    static public function addFieldBilling()
    {
        add_filter( 'woocommerce_billing_fields', 'vendus_plugin_woo_nif_billing_fields', 10, 2 );
        function vendus_plugin_woo_nif_billing_fields( $fields, $country ) {
            $fields['billing_nif'] = array(
                'type'			=>	'text',
                'label'			=> apply_filters( 'woocommerce_nif_field_label', 'NIF / NIPC' ),
                'placeholder'	=> apply_filters( 'woocommerce_nif_field_placeholder', 'Número de identificação fiscal' ),
                'class'			=> apply_filters( 'woocommerce_nif_field_class', array( 'form-row-first' ) ),
                'required'		=> (
                    $country == 'PT' || apply_filters( 'woocommerce_nif_show_all_countries', false)
                    ?
                    apply_filters( 'woocommerce_nif_field_required', false ) 
                    :
                    false
                ),
                'clear'			=> apply_filters( 'woocommerce_nif_field_clear', true ), 
                'autocomplete'	=> apply_filters( 'woocommerce_nif_field_autocomplete', 'on' ),
                'priority'		=> apply_filters( 'woocommerce_nif_field_priority', 120 ),
                'maxlength'		=> apply_filters( 'woocommerce_nif_field_maxlength', 9 ),
                'validate'		=> (
                    $country == 'PT'
                    ?
                    (
                        apply_filters( 'woocommerce_nif_field_validate', false )
                        ?
                        array( 'nif_pt' )
                        :
                        array()
                    )
                    :
                    false
                ),
            );
            return $fields;
        }
    }


    static public function addFieldOrderAdminPanel()
    {
        add_filter( 'woocommerce_admin_billing_fields', 'vendus_plugin_woo_nif_admin_billing_fields' );
        function vendus_plugin_woo_nif_admin_billing_fields( $billing_fields ) {
            global $post;
            if ( $post->post_type == 'shop_order' || $post->post_type == 'shop_subscription' ) {
                $order = new WC_Order( $post->ID );
                $countries = new WC_Countries();
                $billing_country = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
                
                if ( $billing_country == 'PT' || ( $billing_country == '' && $countries->get_base_country() == 'PT' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
                    $billing_fields['nif'] = array(
                        'label' => apply_filters( 'woocommerce_nif_field_label', 'NIF / NIPC' ),
                    );
                }
            }
            return $billing_fields;
        }
    }

    static public function addJs()
    {
        add_action( 'admin_init', 'vendus_plugin_woo_nif_admin_init_found_customer_details' );
        function vendus_plugin_woo_nif_admin_init_found_customer_details() {
            if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
                add_filter( 'woocommerce_ajax_get_customer_details', 'vendus_plugin_woo_nif_ajax_get_customer_details', 10, 3 );
            } else {
                add_filter( 'woocommerce_found_customer_details', 'vendus_plugin_woo_nif_found_customer_details_old', 10, 3 );
            }
        }

        //Pre 3.0
        function vendus_plugin_woo_nif_found_customer_details_old( $customer_data, $user_id, $type_to_load ) {
            if ( $type_to_load == 'billing' ) {
                if ( ( isset( $customer_data['billing_country'] ) && $customer_data['billing_country'] == 'PT' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
                    $customer_data['billing_nif'] = get_user_meta( $user_id, $type_to_load . '_nif', true );
                }
            }
            return $customer_data;
        }

        //3.0 and above - See https://github.com/woocommerce/woocommerce/issues/12654
        function vendus_plugin_woo_nif_ajax_get_customer_details( $customer_data, $customer, $user_id ) {
            if ( ( isset( $customer_data['billing']['country']) && $customer_data['billing']['country'] == 'PT' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
                $customer_data['billing']['nif'] = $customer->get_meta( 'billing_nif' );
            }
            return $customer_data;
        }
    }

    static public function addFieldUserEdit()
    {
        add_action( 'woocommerce_customer_meta_fields', 'vendus_plugin_woo_nif_customer_meta_fields' );
        function vendus_plugin_woo_nif_customer_meta_fields( $show_fields ) {
            if ( isset( $show_fields['billing'] ) && is_array( $show_fields['billing']['fields'] ) ) {
                $show_fields['billing']['fields']['billing_nif'] = array(
                    'label' => apply_filters( 'woocommerce_nif_field_label', 'NIF / NIPC' ),
                    'description' => apply_filters( 'woocommerce_nif_field_placeholder', 'Número de identificação fiscal' ),
                );
            }
            return $show_fields;
        }
    }

    static public function addFieldPageFinishShop()
    {
        add_action( 'woocommerce_order_details_after_customer_details', 'vendus_plugin_woo_nif_order_details_after_customer_details' );
        function vendus_plugin_woo_nif_order_details_after_customer_details( $order ) {
            $billing_country = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
            $billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
            if ( ( $billing_country == 'PT' || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) && $billing_nif ) {
                ?>
                <tr>
                    <th><?php echo apply_filters( 'woocommerce_nif_field_label', 'NIF / NIPC' ); ?>:</th>
                    <td><?php echo esc_html( $billing_nif ); ?></td>
                </tr>
                <?php
            }
        }
    }

    static public function addFieldEmails()
    {
        add_filter( 'woocommerce_email_customer_details_fields', 'vendus_plugin_woo_nif_email_customer_details_fields', 10, 3 );
        function vendus_plugin_woo_nif_email_customer_details_fields( $fields, $sent_to_admin, $order ) {
            $billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
            if ( $billing_nif ) {
                $fields['billing_nif'] = array(
                    'label' => apply_filters( 'woocommerce_nif_field_label', 'NIF / NIPC' ),
                    'value' => wptexturize( $billing_nif )
                );
            }
            return $fields;
        }
    }

    static public function addFieldRestApi()
    {
        add_filter( 'woocommerce_api_order_response', 'vendus_plugin_woo_nif_woocommerce_api_order_response', 11, 2 ); //After WooCommerce own add_customer_data
        function vendus_plugin_woo_nif_woocommerce_api_order_response( $order_data, $order ) {
            //Order
            if ( isset( $order_data['billing_address'] ) ) {
                $billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
                $order_data['billing_address']['nif'] = $billing_nif;
            }
            return $order_data;
        }

        add_filter( 'woocommerce_api_customer_response', 'vendus_plugin_woo_nif_woocommerce_api_customer_response', 10, 2 );
        function vendus_plugin_woo_nif_woocommerce_api_customer_response( $customer_data, $customer ) {
            //Customer
            if ( isset( $customer_data['billing_address'] ) ) {
                $billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $customer->get_meta( 'billing_nif' ) : get_user_meta( $customer->get_id(), 'billing_nif', true );
                $customer_data['billing_address']['nif'] = $billing_nif;
            }
            return $customer_data;
        }
    }

    static public function validations()
    {
        // checkout
        add_action( 'woocommerce_checkout_process', 'vendus_plugin_woo_nif_checkout_process' );
        function vendus_plugin_woo_nif_checkout_process() {
            if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
                $customer_country = version_compare( WC_VERSION, '3.0', '>=' ) ? WC()->customer->get_billing_country() : WC()->customer->get_country();
                $countries = new WC_Countries();
                if ( $customer_country == 'PT' || ( $customer_country == '' && $countries->get_base_country() == 'PT' ) ) {
                    $billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' );
                    if ( vendus_plugin_woo_valida_nif( $billing_nif, true ) || ( trim( $billing_nif ) == '' &&  !apply_filters( 'woocommerce_nif_field_required', false ) ) ) { //If the field is NOT required and it's empty, we shouldn't validate it
                        //OK
                    } else {
                        wc_add_notice(
                            sprintf( '%s inválido para Portugal.', '<strong>'.apply_filters( 'woocommerce_nif_field_label', 'NIF / NIPC' ).'</strong>' ),
                            'error'
                        );
                    }
                } else {
                    //Not Portugal
                }
            } else {
                //All good - No validation required
            }
        }

        // save address
        add_action( 'woocommerce_after_save_address_validation', 'vendus_plugin_woo_nif_after_save_address_validation', 10, 3 );
        function vendus_plugin_woo_nif_after_save_address_validation( $user_id, $load_address, $address ) {
            if ( $load_address == 'billing' ) {
                if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
                    $country = wc_clean( isset( $_POST['billing_country'] ) ? $_POST['billing_country'] : '' );
                    if ( $country == 'PT' ) {
                        $billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' );
                        if ( vendus_plugin_woo_valida_nif( $billing_nif, true ) || ( trim( $billing_nif ) == '' &&  !apply_filters( 'woocommerce_nif_field_required', false ) ) ) { //If the field is NOT required and it's empty, we shouldn't validate it
                        //OK
                        } else {
                            wc_add_notice(
                                sprintf( '%s inválido para Portugal.', '<strong>'.apply_filters( 'woocommerce_nif_field_label', 'NIF / NIPC' ).'</strong>' ),
                                'error'
                            );
                        }
                    }
                }
            }
        }

        // nif
        function vendus_plugin_woo_valida_nif( $nif, $ignoreFirst = true ) {
            //Limpamos eventuais espaços a mais
            $nif = trim( $nif );
            //Verificamos se é numérico e tem comprimento 9
            if ( !is_numeric( $nif ) || strlen( $nif ) != 9 ) {
                return false;
            } else {
                $nifSplit = str_split( $nif );
                //O primeiro digíto tem de ser 1, 2, 5, 6, 8 ou 9
                //Ou não, se optarmos por ignorar esta "regra"
                if (
                    in_array( $nifSplit[0], array( 1, 2, 5, 6, 8, 9 ) )
                    ||
                    $ignoreFirst
                ) {
                    //Calculamos o dígito de controlo
                    $checkDigit=0;
                    for( $i=0; $i<8; $i++ ) {
                        $checkDigit += $nifSplit[$i] * ( 10-$i-1 );
                    }
                    $checkDigit = 11 - ( $checkDigit % 11 );
                    //Se der 10 então o dígito de controlo tem de ser 0
                    if( $checkDigit >= 10 ) $checkDigit = 0;
                    //Comparamos com o último dígito
                    if ( $checkDigit == $nifSplit[8] ) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
    }
}
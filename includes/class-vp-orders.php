<?php

class Vendus_Plugin_Orders
{
    const CUSTOM_FIELD_INVOICES = '_vendus_plugin_invoices_data';

    public function __construct() {

    }

    static public function getExemptions($exemption = '')
    {
        $list = array(
            'M01' => 'Artigo 16.º, n.º 6 do CIVA ou similar',
            'M02' => 'Artigo 6.º do Decreto-Lei n.º 198/90, de 19 de Junho',
            'M03' => 'Exigibilidade de caixa',
            'M04' => 'Artigo 13.º do CIVA ou similar',
            'M05' => 'Artigo 14.º do CIVA ou similar',
            'M06' => 'Artigo 15.º do CIVA ou similar',
            'M07' => 'Artigo 9.º do CIVA ou similar',
            'M08' => 'IVA - autoliquidação',
            'M09' => 'IVA - não confere direito a dedução',
            'M10' => 'IVA - Regime de isenção (Artigo 53.º do CIVA)',
            'M11' => 'Regime particular do tabaco',
            'M12' => 'Regime da margem de lucro - Agências de viagens',
            'M13' => 'Regime da margem de lucro - Bens em segunda mão',
            'M14' => 'Regime da margem de lucro - Objetos de arte',
            'M15' => 'Regime da margem de lucro - Objetos de coleção e antiguidades',
            'M16' => 'Artigo 14.º do RITI ou similar',
            'M99' => 'Não sujeito; não tributado ou similar',
        );
        
        if($exemption && isset($list[$exemption])) {
            return $list[$exemption];
        }

        return $list;
    }

    static public function run($isDev = false)
    {
        $vpOrders = new Vendus_Plugin_Orders();
        $vpOrders->addColumnList();
        $vpOrders->addPostBox();
    }

    static public function addColumnList()
    {
        if(!function_exists('vendus_plugin_add_header_column')){
            function vendus_plugin_add_header_column($columns) {
                $columns['vendus_plugin_invoice'] = 'Vendus';
                return $columns;
            }
            add_filter('manage_edit-shop_order_columns', 'vendus_plugin_add_header_column');
        }
        
        if(!function_exists('vendus_plugin_add_list_column')){
            function vendus_plugin_add_list_column($column) {
                if($column != 'vendus_plugin_invoice') return;
                global $post;
                $order = get_metadata('post', $post->ID);
                
                if(isset($order[Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES]) && !empty(Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES)) {
                    $invoice = unserialize($order[Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES][0]);
                    echo '<a target="_blank" href="' . esc_url(admin_url('admin.php?page=vendus_plugin_settings&action=vendus_plugin_action_view_pdf&id=' . $invoice['id'])) . '">Ver Fatura</a>';
                    return;
                }

                $order  = new WC_Order($post->ID);
                $status = $order->get_status();

                if($status == 'completed') {
                    echo '<a href="' . esc_url(admin_url('admin.php?page=vendus_plugin_settings&action=vendus_plugin_action_invoice&id=' . $post->ID)) . '">Emitir Fatura</a>';
                    return;
                }

                echo 'Emitir Fatura ' . wc_help_tip("A Encomenda ainda não se encontra completa. É necessario alterar o estado da encomenda para completa.");
                return;
            }
            add_filter( 'manage_shop_order_posts_custom_column', 'vendus_plugin_add_list_column', 10);
        }
    }

    static public function addPostBox()
    {
        add_action('add_meta_boxes', 'vendus_plugin_add_meta_boxes');
        function vendus_plugin_add_meta_boxes( $post ) {
            add_meta_box('vendus_add_meta_box', 'Vendus', 'vendus_plugin_render_meta_boxes', 'shop_order', 'side', 'core');
        }

        if(!function_exists('vendus_plugin_render_meta_boxes')){
            function vendus_plugin_render_meta_boxes() {
                global $post;
                $order = get_metadata('post', $post->ID);
                
                if(isset($order[Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES]) && !empty(Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES)) {
                    $invoice = unserialize($order[Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES][0]);
                    echo '<p><a target="_blank" class="button" style="width: 100%; text-align:center;" href="' . esc_url(admin_url('admin.php?page=vendus_plugin_settings&action=vendus_plugin_action_view_pdf&id=' . $invoice['id'])) . '">Ver Fatura</a></p>';
                    echo '<hr style="margin: 0 -12px 0 -12px;">';
                    
                    if(isset($invoice['nc'])) {
                        echo '<p><a target="_blank" class="button" style="width: 100%; text-align:center;" href="' . esc_url(admin_url('admin.php?page=vendus_plugin_settings&action=vendus_plugin_action_view_pdf&id=' . $invoice['nc']['id'])) . '">Ver Nota de Crédito</a></p>';
                    }else{
                        $formLink = admin_url('admin.php?page=vendus_plugin_settings&action=vendus_plugin_action_invoice_nc&id=' . $post->ID);
                        echo "<form></form>";
                        echo '<form action="' . esc_url($formLink) . '" method="POST">
                            <div style="boorder-top: 1px solid #ddd;">
                                <p>
                                    <label for="vendus_plugin_generate_nc">Motivo para Emissão:</label>
                                    <textarea type="text" name="obs" id="vendus_plugin_generate_nc" class="input-text" rows="3" style="width: 100%;" required></textarea>
                                </p>
                                <button type="submit" class="button" style="width: 100%;margin-top: -5px;">Emitir Nota de Crédito</button>
                            </div>
                        </form>';
                    }
                    
                    return;
                }
    
                $order  = new WC_Order($post->ID);
                $status = $order->get_status();
    
                if($status == 'completed') {
                    echo '<p><a class="button" style="width: 100%; text-align:center;" href="' . esc_url(admin_url('admin.php?page=vendus_plugin_settings&action=vendus_plugin_action_invoice&id=' . $post->ID)) . '">Emitir Fatura</a></p>';
                    return;
                }

                echo '<p><a target="_blank" class="button disabled" disabled="disabled" style="width: 100%; text-align:center;" href="#">Emitir Fatura '.wc_help_tip("A Encomenda ainda não se encontra completa. É necessario alterar o estado da encomenda para completa.").'</a></p>';
                return;
            }
        }
    }
}
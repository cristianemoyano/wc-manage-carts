<?php
/*
Plugin Name: WooCommerce Manage Carts
Version: 1.0.0
Description: Plugin personalizado para listar carritos activos y abandonados de WC
Author: Cristian Moyano
*/


function wc_wsp_order_enqueue_admin_script() {
    wp_enqueue_script( 'wp-wsp-order-script', plugin_dir_url( __FILE__ ) . '/assets/js/main.js', array(), '1.0' );
}
// Cargar script
add_action('admin_enqueue_scripts', 'wc_wsp_order_enqueue_admin_script');

function wc_wsp_order_enqueue_styles() {
    // Registrar el estilo
    wp_register_style('wc_wsp_orde-style', plugins_url('/assets/css/main.css', __FILE__));
    // Encolar el estilo
    wp_enqueue_style('wc_wsp_orde-style');
}
add_action('admin_enqueue_scripts', 'wc_wsp_order_enqueue_styles');


// Agrega una página de administración para mostrar la lista de pedidos
function mostrar_carritos_admin_page() {
    add_menu_page(
        'Ver Carritos', // Título de la página
        'Ver Carritos', // Título del menú
        'manage_options', // Capacidad de usuario requerida para ver la página
        'wc-manage-carts', // Slug del menú
        'mostrar_carritos' // Callback para renderizar el contenido de la página
    );
}


// Callback para renderizar el contenido de la página de administración
function mostrar_carritos() {
    $carritos = obtener_carritos();

    echo '<div class="wrap">';
    echo '<h1>Lista de Carritos</h1>';

    echo $carritos;

    echo '</div>';
}


// Función para obtener la lista de pedidos
function obtener_carritos() {
    // Comprueba si WooCommerce está activo
    if (class_exists('WooCommerce')) {

        global $wpdb;

        // Obtiene todos los carritos activos
        $active_carts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_sessions WHERE session_expiry > " . time() );

        // Inicio de la tabla
        $tabla = '';

        $tabla .= '<div class="table-container wrap ">';
        $tabla .= '<table id="cart-table" class="wp-list-table widefat fixed striped table-view-list tabla cart">';
        $tabla .= '<thead>';
        $tabla .= '<tr>';
        $tabla .= '<th class="manage-cart-id column-primary">ID</th>';
        $tabla .= '<th class="manage-client sortable desc">Cliente</th>';
        $tabla .= '<th class="manage-company">Empresa</th>';
        $tabla .= '<th class="manage-item-count">Items</th>';
        $tabla .= '<th class="manage-total">Total</th>';
        $tabla .= '<th class="manage-phone">Teléfono</th>';
        $tabla .= '</tr>';
        $tabla .= '</thead>';
        $tabla .= '<tbody>';

        foreach ( $active_carts as $cart_item ) {
            
            // Obtiene los datos del carrito
            $cart_id = $cart_item->session_id;
            $cart_data = maybe_unserialize( $cart_item->session_value );
            $cart_total_items = $cart_data['cart_contents_count'];
            $cart_total_amount = $cart_data['total'];

            // Obtiene los datos del cliente asociado al carrito
            $client_id = $cart_data['customer_user'];
            $client_data = get_userdata( $client_id );
            $client_fullname = $client_data->first_name . ' ' . $client_data->last_name;
            $client_company = get_user_meta( $client_id, 'billing_company', true );
            $client_phone = get_user_meta( $client_id, 'billing_phone', true );

            $tabla .= '<tr id="'.$cart_id.'">';
            $tabla .= '<td class="column-cart-id">' . $cart_id . '</td>';
            $tabla .= '<td class="column-client">' . $client_fullname . '</td>';
            $tabla .= '<td class="column-company">' . $client_company . '</td>';
            $tabla .= '<td class="column-item-count">' . $cart_total_items . '</td>';
            $tabla .= '<td class="column-total">' . $cart_total_amount . '</td>';
            $tabla .= '<td class="column-phone">' . $client_phone . '</td>';
            $tabla .= '</tr>';
        }

        // Cierre de la tabla
        $tabla .= '</tbody>';
        $tabla .= '</table>';
        $tabla .= '</div>';

        return $tabla;
    } else {
        return 'WooCommerce no está activo.';
    }
}

// Agrega la página de administración al hook 'admin_menu'
add_action('admin_menu', 'mostrar_carritos_admin_page');

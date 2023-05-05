<?php
/*
Plugin Name: WooCommerce Manage Carts
Version: 1.0.0
Description: Plugin personalizado para listar carritos activos y abandonados de WC
Author: Cristian Moyano
*/


function wc_manage_carts_enqueue_admin_script() {
    wp_enqueue_script( 'wp-wsp-order-script', plugin_dir_url( __FILE__ ) . '/assets/js/main.js', array(), '1.0' );
}
// Cargar script
add_action('admin_enqueue_scripts', 'wc_manage_carts_enqueue_admin_script');

function wc_manage_carts_enqueue_styles() {
    // Registrar el estilo
    wp_register_style('wc_wsp_orde-style', plugins_url('/assets/css/main.css', __FILE__));
    // Encolar el estilo
    wp_enqueue_style('wc_wsp_orde-style');
}
add_action('admin_enqueue_scripts', 'wc_manage_carts_enqueue_styles');


// Agrega una página de administración para mostrar la lista de pedidos
function mostrar_carritos_admin_page() {
    add_menu_page(
        'Ver Carritos', // Título de la página
        'Ver Carritos', // Título del menú
        'manage_options', // Capacidad de usuario requerida para ver la página
        'wc-manage-carts', // Slug del menú
        'mostrar_carritos', // Callback para renderizar el contenido de la página
        'dashicons-cart'
    );
}



// Callback para renderizar el contenido de la página de administración
function mostrar_carritos() {
    $carritos = obtener_carritos();

    echo '<div class="wrap">';
    echo '<h1>Lista de Carritos</h1>';
    echo '<p>Listado de carritos activos por cliente. Son pedidos que aún no han confirmado la compra.</p>';

    echo $carritos;

    echo '</div>';
}


// Función para obtener la lista de pedidos
function obtener_carritos() {
    // Comprueba si WooCommerce está activo
    if (class_exists('WooCommerce')) {

        global $wpdb;

        // Obtiene todos los carritos activos
        // $active_carts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_sessions WHERE session_expiry > " . time() );
        $active_carts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_sessions" );
        
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
        $tabla .= '<th class="manage-date">Última fecha de modificación del carrito</th>';
        $tabla .= '</tr>';
        $tabla .= '</thead>';
        $tabla .= '<tbody>';

        foreach ( $active_carts as $cart_item ) {
            
            // Obtiene los datos del carrito
            $cart_id = $cart_item->session_id;
            $cart_data = maybe_unserialize( $cart_item->session_value );

            // Comprueba si el carrito contiene productos antes de intentar acceder a la clave 'cart_contents_count'
            if ( isset( $cart_data['cart_totals'] ) ) {
                $unserialized_cart_totals = unserialize( $cart_data['cart_totals'] );

                $unserialized_cart = unserialize( $cart_data['cart'] );
                $cart_length = count($unserialized_cart);
                $cart_total_items =  $cart_length;
                $cart_total_amount = $unserialized_cart_totals['total'];
            } else {
                $cart_total_items = 0;
                $cart_total_amount = 0;
            }

            // Comprueba si la clave 'customer_user' está definida en el array
            if ( isset( $cart_data['customer'] ) ) {
                $unserialized_customer = unserialize( $cart_data['customer'] );
                $customer_id = $unserialized_customer['id'];
                $customer_fullname = $unserialized_customer['first_name'] . ' ' . $unserialized_customer['last_name'];
                $customer_company = $unserialized_customer['company'];
                $customer_phone = $unserialized_customer['phone'];
            } else {
                $customer_id = 0;
                $customer_fullname = '';
                $customer_company = '';
                $customer_phone = '';
            }

            if ($cart_total_items === 0 || empty($customer_company)) {
                continue; // saltar a la siguiente iteración del bucle
            }

            // Restar 24 horas (24 horas * 60 minutos * 60 segundos)
            $session_expiry_24h_ago = $cart_item->session_expiry - (24 * 60 * 60);
            $date_24h_ago = date('d/m/Y', $session_expiry_24h_ago);


            $tabla .= '<tr id="'.$cart_id.'">';
            $tabla .= '<td class="column-cart-id">' . $cart_id . '</td>';
            $tabla .= '<td class="column-client">' . $customer_fullname . ' (ID #' . $customer_id . ')</td>';
            $tabla .= '<td class="column-company">' . $customer_company . '</td>';
            $tabla .= '<td class="column-item-count">' . $cart_total_items . '</td>';
            $tabla .= '<td class="column-total"> $ ' . $cart_total_amount . '</td>';
            $tabla .= '<td class="column-phone">' . $customer_phone . '</td>';
            $tabla .= '<td class="column-date">' . $date_24h_ago. '</td>';
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

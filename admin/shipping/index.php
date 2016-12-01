<?php
require_once('../../util/main.php');
require_once('util/secure_conn.php');
require_once('util/valid_admin.php');

require_once('mail/index.php');

require_once('model/customer_db.php');
require_once('model/address_db.php');
require_once('model/order_db.php');
require_once('model/product_db.php');

$action = filter_input(INPUT_POST, 'action');
if ($action == NULL) {
    $action = filter_input(INPUT_GET, 'action');
    if ($action == NULL) {        
        $action = 'view_orders';
    }
}

switch($action) {
    case 'view_orders':
        $new_orders = get_unfilled_orders();
        $old_orders = get_filled_orders();
        include 'orders.php';
        break;
    case 'view_order':
        $order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

        // Get order data
        $order = get_order($order_id);
        $order_date = date('M j, Y', strtotime($order['orderDate']));
        $order_items = get_order_items($order_id);

        // Get customer data
        $customer = get_customer($order['customerID']);
        $name = $customer['firstName'] . ' ' . $customer['lastName'];
        $email = $customer['emailAddress'];
        $card_number = $order['cardNumber'];
        $card_expires = $order['cardExpires'];
        $card_name = card_name($order['cardType']);

        $shipping_address = get_address($order['shipAddressID']);
        $ship_line1 = $shipping_address['line1'];
        $ship_line2 = $shipping_address['line2'];
        $ship_city = $shipping_address['city'];
        $ship_state = $shipping_address['state'];
        $ship_zip = $shipping_address['zipCode'];
        $ship_phone = $shipping_address['phone'];

        $billing_address = get_address($order['billingAddressID']);
        $bill_line1 = $billing_address['line1'];
        $bill_line2 = $billing_address['line2'];
        $bill_city = $billing_address['city'];
        $bill_state = $billing_address['state'];
        $bill_zip = $billing_address['zipCode'];
        $bill_phone = $billing_address['phone'];

        include 'order.php';
        break;
    case 'print_details':
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        
        // Get order data
        $order = get_order($order_id);
        $order_items = get_order_items($order_id);

        // Get customer data
        $customer = get_customer($order['customerID']);
        $name = $customer['firstName'] . ' ' . $customer['lastName'];
        
        // Get shipping data
        $shipping_address = get_address($order['shipAddressID']);
        $ship_line1 = $shipping_address['line1'];
        $ship_line2 = $shipping_address['line2'];
        $ship_city = $shipping_address['city'];
        $ship_state = $shipping_address['state'];
        $ship_zip = $shipping_address['zipCode'];
        $ship_phone = $shipping_address['phone'];
        
        if (strlen($ship_line2) > 0) {
            $ship_to = $ship_line1 . "\n" . $ship_line2;
        } else {
            $ship_to = $ship_line1;
        }
        
        // Create shipping document
        $file_name = "order" . $order_id . ".txt";
        $file = fopen('documents/' . $file_name, 'wb');
        
        $details = "ORDER " . $order_id . 
                "   SHIPPING DETAILS\n\n\nSHIP TO:\n" . 
                $name . "\n" . 
                $ship_to . "\n" .
                $ship_city . ", " . $ship_state . " " . $ship_zip . "\n" . 
                $ship_phone . 
                "\n\n\nORDER ITEMS:\n";
        
        fwrite($file, $details);
        
        foreach ($order_items as $item) {
            $product_id = $item['productID'];
            $product = get_product($product_id);
            $item_name = $product['productName'];
            $quantity = $item['quantity'];
            
            $item_details = $item_name . "   x" . $quantity . "\n";
            fwrite($file, $item_details);
        }
        
        fclose($file);
        
        $url = '?action=view_order&order_id=' . $order_id;
        redirect($url);
        break;
    case 'set_ship_date':
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $order = get_order($order_id);
        $customer = get_customer($order['customerID']);
        $customer_name = $customer['firstName'] . ' ' . $customer['lastName'];
        $email = $customer['emailAddress'];
        
        set_ship_date($order_id);
        send_shipped_email($order_id, $customer_name, $email);
        
        $url = '?action=view_order&order_id=' . $order_id;
        redirect($url);
    case 'confirm_delete':
        // Get order data
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $order = get_order($order_id);
        $order_date = date('M j, Y', strtotime($order['orderDate']));

        // Get customer data
        $customer = get_customer($order['customerID']);
        $customer_name = $customer['lastName'] . ', ' . $customer['firstName'];
        $email = $customer['emailAddress'];

        include 'confirm_delete.php';
        break;
    case 'delete':
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        delete_order($order_id);
        redirect('.');
        break;
    default:
        display_error("Unknown order action: " . $action);
        break;
}
?>
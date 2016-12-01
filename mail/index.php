<?php
require_once('util/main.php');

require_once('model/customer_db.php');
require_once('model/address_db.php');
require_once('model/order_db.php');
require_once('model/product_db.php');

function send_email($order_id) {
    require_once('class.PHPMailer.php');
    set_time_limit(0);
    
    $destination = $_SESSION['user']['emailAddress'];
    $customer_name = $_SESSION['user']['firstName'] . ' ' .
                     $_SESSION['user']['lastName'];

    ob_start();                          // start capturing output
    include('message.php');              // execute the file
    $messageHTML = ob_get_contents();    // get the contents from the buffer
    ob_end_clean();  
    
    $message =  ' This is information that will not be HTML friendly for Emails that do not support HTML';

    $email = new PHPMailer();
    $email->IsSMTP();
    $email->Host       = "smtp.gmail.com";
    $email->SMTPAuth   = true;  
    $email->Port       = 587;
    $email->SMTPDebug  = 1;    
    $email->SMTPSecure = 'tls';     
    $email->Username   = "guitarshop2016@gmail.com"; 
    $email->Password   = "yz6N0bgk3mX0";             
    $email->SetFrom('guitarshop2016@gmail.com', 'Guitar Shop');
    $email->SingleTo  = true;	
    $email->From      = 'guitarshop2016@gmail.com'; 
    $email->FromName  = 'Guitar Shop'; 
    $email->Subject   = 'Order Confirmation #' . $order_id; 
    $email->Body      = $messageHTML ;     
    $email->AltBody = $message;            
    $destination_email_address = $destination; 
    $destination_user_name = $customer_name; 
    $email->AddAddress($destination_email_address, $destination_user_name);
    
    if(!$email->Send()) {
        echo "Mailer Error: " . $email->ErrorInfo;
    }	
}

function send_shipped_email($order_id, $customer_name, $customer_email) {
    require_once('class.PHPMailer.php');
    set_time_limit(0);
    ob_start();                          // start capturing output
    include('message.php');              // execute the file
    $messageHTML = ob_get_contents();    // get the contents from the buffer
    ob_end_clean();  
    
    $message =  ' This is information that will not be HTML friendly for Emails that do not support HTML';

    $email = new PHPMailer();
    $email->IsSMTP();
    $email->Host       = "smtp.gmail.com";
    $email->SMTPAuth   = true;  
    $email->Port       = 587;
    $email->SMTPDebug  = 1;    
    $email->SMTPSecure = 'tls';     
    $email->Username   = "guitarshop2016@gmail.com"; 
    $email->Password   = "yz6N0bgk3mX0";             
    $email->SetFrom('guitarshop2016@gmail.com', 'Guitar Shop');
    $email->SingleTo  = true;	
    $email->From      = 'guitarshop2016@gmail.com'; 
    $email->FromName  = 'Guitar Shop'; 
    $email->Subject   = 'Shipping Confirmation, Order #' . $order_id; 
    $email->Body      = $messageHTML ;     
    $email->AltBody = $message;
    $email->AddAddress($customer_email, $customer_name);
    
    if(!$email->Send()) {
        echo "Mailer Error: " . $email->ErrorInfo;
    }	
}

?>

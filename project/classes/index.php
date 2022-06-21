<?php
// require_once('connect.php');
include('connect.php');
$container['view'] = __DIR__ . '/../project/';

$sql = "SELECT ('truck') AS name, COUNT(truck_id) AS amount
        FROM truck

        UNION
        SELECT ('truck_delivery') AS name, COUNT(truck_id) AS amount    
        FROM truck_delivery

        UNION
        SELECT ('delivery') AS name, COUNT(delivery_id) AS amount        
        FROM delivery

        UNION
        SELECT ('delivery_order') AS name, COUNT(delivery_id) AS amount      
        FROM delivery_order

        UNION
        SELECT ('orderlist') AS name, COUNT(order_id) AS amount    
        FROM orderlist

        UNION
        SELECT ('order_product') AS name, COUNT(order_id) AS amount   
        FROM order_product

        UNION
        SELECT ('product') AS name, COUNT(product_id) AS amount        
        FROM product

        UNION
        SELECT ('product_customer') AS name, COUNT(product_id) AS amount     
        FROM product_customer

        UNION
        SELECT ('customer') AS name, COUNT(customer_id) AS amount       
        FROM customer

        UNION
        SELECT ('sent_customer') AS name, COUNT(sent_id) AS amount   
        FROM sent_customer

        UNION
        SELECT ('order_sent') AS name, COUNT(order_id) AS amount     
        FROM order_sent

        UNION
        SELECT ('sent') AS name, COUNT(sent_id) AS amount     
        FROM sent 
";       //時間不對OK

$result = mysqli_query($conn, $sql);

$myarray = array();


if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        $myarray[] = $row;
    }

    echo json_encode($myarray);
} else {
    echo '沒有資料內容';
}

mysqli_close($conn);

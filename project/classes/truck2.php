<?php
include('connect.php');

$sql = "SELECT order_product.product_id, product.product_name 
        FROM truck LEFT JOIN truck_delivery ON truck.truck_id = truck_delivery.truck_id 
        LEFT JOIN delivery ON truck_delivery.delivery_id = delivery.delivery_id 
        LEFT JOIN delivery_order ON delivery.delivery_id = delivery_order.delivery_id
        LEFT JOIN order_product ON delivery_order.order_id = order_product.order_id 
        LEFT JOIN product ON order_product.product_id = product.product_id
        WHERE truck.truck_id = '1721' 
        AND delivery.success_number = truck.last_success
";   

$result = mysqli_query($conn, $sql); 

$myarray = array();

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){

            $myarray[] = $row;
        }

        echo json_encode($myarray);
    }else{
        echo '沒有資料內容';
    }

mysqli_close($conn);
?>

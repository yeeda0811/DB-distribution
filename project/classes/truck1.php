<?php
include('connect.php');

$sql = "SELECT truck_delivery.truck_id, delivery_order.order_id, customer.customer_name
        FROM truck_delivery
        LEFT JOIN delivery_order ON truck_delivery.delivery_id = delivery_order.delivery_id
        LEFT JOIN order_product ON delivery_order.order_id = order_product.order_id
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        WHERE truck_delivery.truck_id = '1721'
        GROUP BY customer.customer_name
";   

$result = mysqli_query($conn, $sql); 

$myarray = array();

    //判斷資料表有沒有內容，如果是空的就不執行查詢
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){

            $myarray[] = $row;
        }
        //轉成 json 語法
        echo json_encode($myarray);
    }else{
        echo '沒有資料內容';
    }

mysqli_close($conn);
?>

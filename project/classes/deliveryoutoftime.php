<?php
include('connect.php');

$sql = "SELECT order_product.order_id, order_product.product_id, product.product_name
        FROM product
        LEFT JOIN order_product ON product.product_id = order_product.product_id
        LEFT JOIN delivery_order ON order_product.order_id = delivery_order.order_id
        LEFT JOIN delivery ON delivery_order.delivery_id = delivery.delivery_id
        LEFT JOIN orderlist ON delivery_order.order_id = orderlist.order_id
        WHERE orderlist.timeline < delivery.arrival_time
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

<?php
include('connect.php');

$sql = "SELECT customer.customer_id, customer.customer_name, SUM(orderlist.ship_fee) AS Fee
        FROM orderlist
        LEFT JOIN order_product ON orderlist.order_id = order_product.order_id 
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        WHERE orderlist.timeline BETWEEN '2021-11-01' AND '2022-11-25'
        GROUP BY customer.customer_id
        ORDER BY Fee DESC;
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

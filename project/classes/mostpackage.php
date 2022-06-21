<?php
include('connect.php');

$sql = "SELECT product_customer.customer_id, customer.customer_name , count(order_product.order_id) AS packageCount
        FROM order_product
        LEFT JOIN orderlist ON order_product.order_id = orderlist.order_id
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        WHERE orderlist.timeline BETWEEN '2021-11-01' AND '2022-11-25'
        GROUP BY product_customer.customer_id
        ORDER BY packageCount DESC
";

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

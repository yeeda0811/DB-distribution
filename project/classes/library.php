<?php
$sql = "SELECT truck_delivery.truck_id, delivery_order.order_id, customer.customer_name
FROM truck_delivery
LEFT JOIN delivery_order ON truck_delivery.delivery_id = delivery_order.delivery_id
LEFT JOIN order_product ON delivery_order.order_id = order_product.order_id
LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
WHERE truck_delivery.truck_id = '1721'
GROUP BY customer.customer_name
";       //1721車上貨物的customer

$sql = "SELECT order_product.order_id,product.product_name 
        FROM truck LEFT JOIN truck_delivery ON truck.truck_id = truck_delivery.truck_id 
        LEFT JOIN delivery ON truck_delivery.delivery_id = delivery.delivery_id 
        LEFT JOIN delivery_order ON delivery.delivery_id = delivery_order.delivery_id
        LEFT JOIN order_product ON delivery_order.order_id = order_product.order_id 
        LEFT JOIN product ON order_product.product_id = product.product_id
        WHERE truck.truck_id = '1721' AND delivery.success_number = truck.last_success
";       //1721上一次成功的貨物

$sql = "SELECT product_customer.customer_id, product_customer,customer_name , MAX(SUM(ship_fee)) as fee 
        FROM order_product
        LEFT JOIN orderlist ON order_product.order_id = orderlist,order_id
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        group by product_customer.customer_id
        WHERE orderlist.timeline BETWEEN 'Jan-01-2022' AND 'Dec-31-2022';
";       //運費最多

$sql = "SELECT product_customer.customer_id, product_customer,customer_name , MAX(count(order_product.order_id))
        FROM order_product
        LEFT JOIN orderlist ON order_product.order_id = orderlist,order_id
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        group by product_customer.customer_id
        WHERE orderlist.timeline BETWEEN 'Jan-01-2022' AND 'Dec-31-2022';
";       //郵寄最多

$sql = "SELECT order_product.order_id, order_product.product_id
        FROM order_product
        LEFT JOIN delivery_order ON order_product.order_id = delivery_order.order_id
        LEFT JOIN delivery ON delivery_order.delivery_id = delivery.delivery_id
        LEFT JOIN orderlist ON delivery_order.order_id = orderlist.order_id
        WHERE orderlist.timeline < delivery.arrival 
";       //時間不對
//123
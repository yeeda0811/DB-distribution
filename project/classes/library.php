<?php
$sql = "SELECT truck_delivery.truck_id, delivery_order.order_id, customer.customer_name
        FROM truck_delivery
        LEFT JOIN delivery_order ON truck_delivery.delivery_id = delivery_order.delivery_id
        LEFT JOIN order_product ON delivery_order.order_id = order_product.order_id
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        WHERE truck_delivery.truck_id = '1721'
        GROUP BY customer.customer_name
";       //1721車上貨物的customer OK

$sql = "SELECT order_product.product_id,product.product_name 
        FROM truck LEFT JOIN truck_delivery ON truck.truck_id = truck_delivery.truck_id 
        LEFT JOIN delivery ON truck_delivery.delivery_id = delivery.delivery_id 
        LEFT JOIN delivery_order ON delivery.delivery_id = delivery_order.delivery_id
        LEFT JOIN order_product ON delivery_order.order_id = order_product.order_id 
        LEFT JOIN product ON order_product.product_id = product.product_id
        WHERE truck.truck_id = '1721' 
        AND delivery.success_number = truck.last_success
";       //1721上一次成功的貨物 OK

$sql = "SELECT customer.customer_id, customer.customer_name, SUM(orderlist.ship_fee) AS Fee
        FROM orderlist
        LEFT JOIN order_product ON orderlist.order_id = order_product.order_id 
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        WHERE orderlist.timeline BETWEEN '2021-11-01' AND '2022-11-25'
        GROUP BY customer.customer_id
        ORDER BY Fee DESC;
";       //運費最多加上時間OK

$sql = "SELECT customer.customer_id, customer.customer_name, SUM(orderlist.ship_fee) AS Fee
        FROM orderlist
        LEFT JOIN order_product ON orderlist.order_id = order_product.order_id 
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        GROUP BY customer.customer_id
        ORDER BY Fee DESC
";       //運費最多  OK

$sql = "SELECT product_customer.customer_id, customer.customer_name , count(order_product.order_id) AS packageCount
        FROM order_product
        LEFT JOIN orderlist ON order_product.order_id = orderlist.order_id
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        WHERE orderlist.timeline BETWEEN '2021-11-01' AND '2022-11-25'
        GROUP BY product_customer.customer_id
        ORDER BY packageCount DESC
";       //郵寄最多加上時間 OK

$sql = "SELECT order_product.order_id, order_product.product_id
        FROM order_product
        LEFT JOIN delivery_order ON order_product.order_id = delivery_order.order_id
        LEFT JOIN delivery ON delivery_order.delivery_id = delivery.delivery_id
        LEFT JOIN orderlist ON delivery_order.order_id = orderlist.order_id
        WHERE orderlist.timeline < delivery.arrival 
";       //時間不對OK

$sql = "SELECT ('truck') AS name, COUNT(truck_id) AS count數量
        FROM truck

        UNION
        SELECT ('truck_delivery') AS name, COUNT(truck_id)
        FROM truck_delivery

        UNION
        SELECT ('delivery') AS name, COUNT(delivery_id)
        FROM delivery

        UNION
        SELECT ('delivery_order') AS name, COUNT(delivery_id)
        FROM delivery_order

        UNION
        SELECT ('orderlist') AS name, COUNT(order_id)
        FROM orderlist

        UNION
        SELECT ('order_product') AS name, COUNT(order_id)
        FROM order_product

        UNION
        SELECT ('product') AS name, COUNT(product_id)
        FROM product

        UNION
        SELECT ('product_customer') AS name, COUNT(product_id)
        FROM product_customer

        UNION
        SELECT ('customer') AS name, COUNT(customer_id)
        FROM customer

        UNION
        SELECT ('sent_customer') AS name, COUNT(sent_id)
        FROM sent_customer

        UNION
        SELECT ('order_sent') AS name, COUNT(order_id)
        FROM order_sent

        UNION
        SELECT ('sent') AS name, COUNT(sent_id)
        FROM sent 
";       //時間不對OK



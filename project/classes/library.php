<?php


$sql = 'SELECT * FROM buyer';       //buyer 表


$sql = "SELECT truck_delivery.truck_id, delivery_order.order_id, customer.customer_name
        FROM truck_delivery
        LEFT JOIN delivery_order ON truck_delivery.delivery_id = delivery_order.delivery_id
        LEFT JOIN order_product ON delivery_order.order_id = order_product.order_id
        LEFT JOIN product_customer ON order_product.product_id = product_customer.product_id
        LEFT JOIN customer ON product_customer.customer_id = customer.customer_id
        WHERE truck_delivery.truck_id = '1721'
        GROUP BY customer.customer_name
";       //1721車上貨物的customer
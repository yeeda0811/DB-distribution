Distribution System 2022/6/20

author: 
    410977023 軟體二 何銘耀
    410977025 軟體二 吳俋達

environment: 
    fronted:
        html + js and render with css, boostrap and datatable
    backed:
        php(using mysqli) + mysql
    並在iis上查看

table:

    這邊的id全部都是primary key，並自動填入
    customer (customer_id, customer_name) 使用Distribution System之用戶
    product (product_id, product_name) 商品，id 分別為編號及名稱
    orderlist (order_id, product_type, ship_fee, timeline, order_check, payment) 這一筆訂單(包裹)編號、大小、所需運費、預計送達日期、貨物類型(是否為特殊物品)、付款方式
    delivery(delivery_id, arrival, arrival_time, safty, success_number) 配送編號、配送地址、抵達時間、是否安全抵達以及訂單運送成功編號
    truck(truck_id, diver, last_success) 貨車編號、司機名稱、上一次成功的編號
    sent(sent_id, sent_name) 寄送單號、寄送單名稱
    

    以下table會把上面的id 當作foreign key，並使用關聯式 CASCADE 在 UPDATE 與 DELECE
    product_customer(product_id, customer_id) product 以及對應之 customer，也就是顧客買了什麼貨品
    order_product(order_id, product_id) 訂單(包裹)包括了哪些貨品
    delivery_order(delivery_id, order_id) 這一批一起配送的單子內有那些包裹
    truck_delivery(truck_id, delivery_id) 貨車對應到的一筆配送單
    sent_customer(sent_id, customer_id) 寄送單與對應之顧客



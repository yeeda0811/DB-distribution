<?php

use \Slim\Http\UploadedFile;
use Slim\Views\PhpRenderer;

class Product
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function preDatatables($data)
    {
        $result = [
            "draw" => 0,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => []
        ];
        foreach ($data as $key => $value) {
            if ($key == "draw") {
                $result['draw'] = $data['draw']++;
            }
        }
        return $result;
    }
    public function getItems($data)
    {
        $values = [
            "size" => 0,
            "length" => 0,
            "start" => 0
        ];
        $search_valid = "";
        if (array_key_exists("search", $data)) {
            if ($data['search'] !== '') {
                $search = $data["search"];
                $search_valid = " WHERE (itemtypes.*::text LIKE '%$search%') AND ( (itemtypes.*::text LIKE '%$search%') != ((itemtypes.item_id::text LIKE '%$search%') AND (itemtypes.*::text NOT LIKE '%$search%')))
                ";
            }
        }

        if (array_key_exists("length", $data)) {
            $values["length"] = $data["length"];
        }
        if (array_key_exists("start", $data)) {
            $values["size"] = ($data["start"] / $values["length"] + 1) * $values["length"];
            $values["start"] = $data["start"];
        }
        unset($values['length']);
        $sql = "SELECT *
            FROM
            (   
                SELECT * ,ROW_NUMBER() OVER (ORDER BY itemtypes.item_id ASC) AS rownum
                FROM (
                SELECT item.item_id, item.item_name,STRING_AGG(kind.kind_name,',') AS itemtypes, item.price, item.amount, unit.unit_name, \"user\".user_name, item.describe
                        FROM product.item
                        LEFT JOIN product.unit ON product.unit.unit_id = product.item.unit_id
                        LEFT JOIN \"user\".\"user\" ON product.item.user_id=\"user\".\"user\".user_id
                        LEFT JOIN product.item_kind ON item_kind.item_id=item.item_id
                        LEFT JOIN product.kind ON kind.kind_id= item_kind.kind_id
                        GROUP BY item.item_id,item.price, item.amount, unit.unit_name, \"user\".user_name, item.describe
               )itemtypes
               $search_valid
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start 
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getItemsTotal($data)
    {
        $sql = "SELECT COUNT(*)
            FROM product.item
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }
    public function getItem($data)
    {
        $values = [
            "item_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT item.item_id, item.item_name,JSON_AGG(JSON_BUILD_OBJECT('kind_id',kind.kind_id)) AS itemtypes, item.price, item.amount, unit.unit_id,unit.unit_name, \"user\".user_name, item.describe 
                FROM product.item
                LEFT JOIN product.unit ON product.unit.unit_id = product.item.unit_id
                LEFT JOIN \"user\".\"user\" ON product.item.user_id=\"user\".\"user\".user_id
                LEFT JOIN product.item_kind ON item_kind.item_id=item.item_id
                LEFT JOIN product.kind ON kind.kind_id= item_kind.kind_id
                WHERE item.item_id = :item_id
                GROUP BY item.item_id,item.price, item.amount, unit.unit_id,unit.unit_name, \"user\".user_name, item.describe
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key => $row) {
            foreach ($row as $row_key => $column) {
                if ($this->isJson($column)) {
                    $result[$key][$row_key] = json_decode($column, true);
                }
            }
        }
        return $result;
    }

    function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    public function addItem($data)
    {
        // $set_item_data = [
        //     "item_name" => 0,
        //     "price" => 0,
        //     "amount" => 0,
        //     "unit_id" => 0,
        //     "user_id" => $_SESSION['user_id'],
        //     "describe" => 0,

        // ];
        // foreach ($set_item_data as $key => $value) {
        //     if (array_key_exists($key, $data)) {
        //         $set_item_data[$key] = $data[$key];
        //     }
        // }
        $sql = "INSERT INTO product.item(item_name, price, amount, unit_id, user_id, describe)
        VALUES(:item_name,:price,:amount,:unit_id,:user_id,:describe)
        RETURNING item_id";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':item_name', $data["item_name"], PDO::PARAM_STR);
        $sth->bindValue(':price', $data["price"], PDO::PARAM_INT);
        $sth->bindValue(':amount', $data["amount"], PDO::PARAM_INT);
        $sth->bindValue(':unit_id', $data["unit"], PDO::PARAM_INT);
        $sth->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $sth->bindValue(':describe', $data["describe"], PDO::PARAM_STR);
        $sth->execute();
        $item_id = $sth->fetchAll(PDO::FETCH_ASSOC);
        $item_id = $item_id[0]["item_id"];
        var_dump($item_id);
        foreach ($data["item_type"] as $type) {
            $sql = "INSERT INTO product.item_kind(item_id,kind_id)
            VALUES(:item_id,:kind_id)";
            $sth = $this->container->db->prepare($sql);
            $sth->bindValue(':item_id', $item_id, PDO::PARAM_INT);
            $sth->bindValue(':kind_id', $type, PDO::PARAM_INT);
            $sth->execute();
        }
        foreach ($data["picture"] as $type) {
            $sql = "INSERT INTO product.item_file(item_id,file_id)
            VALUES(:item_id,:file_id)";
            $sth = $this->container->db->prepare($sql);
            $sth->bindValue(':item_id', $item_id, PDO::PARAM_INT);
            $sth->bindValue(':file_id', $type, PDO::PARAM_INT);
            $sth->execute();
            var_dump($type);
            var_dump($sth->errorInfo());
        }
    }

    public function EditItem($data)
    {
        // $set_item_data = [
        //     "item_name" => 0,
        //     "price" => 0,
        //     "amount" => 0,
        //     "unit_id" => 0,
        //     "user_id" => $_SESSION['user_id'],
        //     "describe" => 0,

        // ];
        // foreach ($set_item_data as $key => $value) {
        //     if (array_key_exists($key, $data)) {
        //         $set_item_data[$key] = $data[$key];
        //     }
        // }
        $sql = "UPDATE product.item
            SET item_name = :item_name,price = :price,amount = :amount,unit_id = :unit_id,user_id = :user_id,describe = :describe
            WHERE item_id = :item_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':item_name', $data["item_name"], PDO::PARAM_STR);
        $sth->bindValue(':price', $data["price"], PDO::PARAM_INT);
        $sth->bindValue(':amount', $data["amount"], PDO::PARAM_INT);
        $sth->bindValue(':unit_id', $data["unit"], PDO::PARAM_INT);
        $sth->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $sth->bindValue(':describe', $data["describe"], PDO::PARAM_STR);
        $sth->bindValue(':item_id', $data["item_id"], PDO::PARAM_STR);
        $sth->execute();
        $sql = "DELETE FROM product.item_kind
            WHERE item_id= :item_id;
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':item_id', $data["item_id"], PDO::PARAM_INT);
        $sth->execute();
        if (array_key_exists("item_type", $data)) {
            foreach ($data["item_type"] as $type) {
                $sql = "INSERT INTO product.item_kind(item_id,kind_id)
                        VALUES(:item_id,:kind_id)";
                $sth = $this->container->db->prepare($sql);
                $sth->bindValue(':item_id', $data["item_id"], PDO::PARAM_INT);
                $sth->bindValue(':kind_id', $type, PDO::PARAM_INT);
                $sth->execute();
            }
        }
        $sql = "DELETE FROM product.item_file
        WHERE item_id= :item_id;
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':item_id', $data["item_id"], PDO::PARAM_INT);
        $sth->execute();
        if (array_key_exists("picture", $data)) {
            foreach ($data["picture"] as $type) {
                $sql = "UPDATE product.item_file
                    SET file_id = :file
                    WHERE item_id =:item_id
                    ";
                $sth = $this->container->db->prepare($sql);
                $sth->bindValue(':item_id', $data["item_id"], PDO::PARAM_INT);
                $sth->bindValue(':file_id', $type, PDO::PARAM_INT);
                $sth->execute();
            }
        }
    }
    public function DeleteItem($data)
    {
        //資料格式範例 [{item_id:1}, {item_id:2}, ... ]
        $set_item_data_array = [];
        $set_item_data = [
            "item_id" => 0
        ];
        foreach ($data['data'] as $row => $data_to_push) {
            foreach ($set_item_data as $key => $value) {
                if (array_key_exists($key, $data_to_push)) {
                    $set_item_data[$key] = (int) $data_to_push[$key];
                    array_push($set_item_data_array, $set_item_data);
                }
            }
        }
        foreach ($set_item_data_array as $i => $delete_data) {
            $sql = "DELETE FROM product.item
                WHERE item.item_id = :item_id 
            ";
            $sth = $this->container->db->prepare($sql);
            $sth->execute($delete_data);
        }
    }
    public function getItemTypes($data)
    {
        $sql = 'SELECT kind_id, kind_name
        FROM product.kind
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return ($result);
    }
    public function getUnit($data)
    {
        $sql = 'SELECT unit_id, unit_name
                FROM product.unit
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return ($result);
    }
    public function getPayingTypes($data)
    {
        $sql = 'SELECT paying_id, paying_name
                FROM product.paying_type
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return ($result);
    }
    public function getUserItems($data)
    {
        $sql = 'SELECT item_id,item_name
                FROM product.item
                WHERE user_id=:user_id;
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return ($result);
    }
    public function getImports($data)
    {
        $values = [
            "size" => 0,
            "length" => 0,
            "start" => 0
        ];
        if (array_key_exists("length", $data)) {
            $values["length"] = $data["length"];
        }
        if (array_key_exists("start", $data)) {
            $values["size"] = ($data["start"] / $values["length"] + 1) * $values["length"];
            $values["start"] = $data["start"];
        }
        unset($values['length']);
        $sql = "SELECT *
            FROM
            (
                SELECT import.import_id,item.item_name,import.import_amount, import.import_date , ROW_NUMBER() OVER (ORDER BY import_id DESC) AS rownum
                FROM product.item
                INNER JOIN product.import ON import.item_id = item.item_id
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getImportsTotal($data)
    {
        $sql = "SELECT COUNT(*)
            FROM product.import
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }
    public function getImport($data)
    {
        $sql = "SELECT import.import_id,item.item_id,item.item_name,import.import_amount, import.import_date,import.user_id
                FROM product.item
                INNER JOIN product.import ON import.item_id = item.item_id
             WHERE import_id = :import_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function addImport($data)
    {
        $set_import_data = [
            "item_id" => 0,
            "import_amount" => 0,
            "import_date" => 0,
            "user_id" => $_SESSION['user_id']
        ];
        foreach ($set_import_data as $key => $value) {
            if (array_key_exists($key, $data)) {
                $set_import_data[$key] = $data[$key];
            }
        }
        $sql = "INSERT INTO product.import(
            item_id, import_amount, import_date, user_id)
            VALUES (:item_id, :import_amount, :import_date, :user_id);
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($set_import_data);
        $add_import_data = [
            "item_id" => 0,
            "import_amount" => 0,
        ];
        foreach ($add_import_data as $key => $value) {
            if (array_key_exists($key, $data)) {
                $add_import_data[$key] = $data[$key];
            }
        }
        $sql = "UPDATE product.item
                    SET amount= (amount + :import_amount)
                    WHERE item_id = :item_id
                ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($add_import_data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function EditImport($data)
    {
        $set_import_data = [
            "import_amount" => 0,
            "import_date" => 0,
            "import_id" => 0
        ];
        foreach ($set_import_data as $key => $value) {
            if (array_key_exists($key, $data)) {
                $set_import_data[$key] = $data[$key];
            }
        }
        $sql = "UPDATE product.import
            SET import_amount=:import_amount, import_date=:import_date
            WHERE import_id = :import_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($set_import_data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    public function DeleteImport($data)
    {
        //資料格式範例 [{import_id:1}, {import_id:2}, ... ]
        $set_import_data_array = [];
        $set_import_data = [
            "import_id" => 0
        ];
        foreach ($data['data'] as $row => $data_to_push) {
            foreach ($set_import_data as $key => $value) {
                if (array_key_exists($key, $data_to_push)) {
                    $set_import_data[$key] = (int) $data_to_push[$key];
                    array_push($set_import_data_array, $set_import_data);
                }
            }
        }

        foreach ($set_import_data_array as $i => $delete_data) {
            $sql = "DELETE FROM product.import
                WHERE import.import_id = :import_id 
                RETURNING import_id, import_amount
            ";
            $sth = $this->container->db->prepare($sql);
            $sth->execute($delete_data);
            $import_delete = $sth->fetchAll(PDO::FETCH_ASSOC);
            if (count($import_delete) > 0) {
                $delete_count = [
                    "import_amount" => 0,
                    "import_id" => 0,
                ];
                foreach ($delete_count as $key => $value) {
                    if (array_key_exists($key, $import_delete)) {
                        $delete_count[$key] = $import_delete[$key];
                    }
                }

                $sql = "UPDATE product.item
                    SET amount= (amount - :import_amount)
                    WHERE item_id IN 
                    (SELECT item_id
                     FROM product.import
                     WHERE import_id = :import_id 
                    )
                ";
                $sth = $this->container->db->prepare($sql);
                $sth->execute($delete_count);
            }
        }
    }
    public function getDiscounts($data)
    {
        $values = [
            "size" => 0,
            "length" => 0,
            "start" => 0
        ];
        if (array_key_exists("length", $data)) {
            $values["length"] = $data["length"];
        }
        if (array_key_exists("start", $data)) {
            $values["size"] = ($data["start"] / $values["length"] + 1) * $values["length"];
            $values["start"] = $data["start"];
        }
        unset($values['length']);
        $sql = "SELECT *
            FROM
            (
            SELECT discount.discount_id, discount.discount_name, discount_type.discount_type_name, discount.discount_price , discount.discount_calculate, ROW_NUMBER() OVER (ORDER BY discount_id ASC) AS rownum
                FROM product.discount
                INNER JOIN  product.discount_type ON discount.discount_type_id = discount_type.discount_type_id
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll();
        return $result;
    }
    public function getDiscountsTotal($data)
    {
        $sql = "SELECT COUNT(*)
            FROM product.discount
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }
    public function getDiscount($data)
    {
        $sql = "SELECT  discount.discount_name, discount.discount_type_id, discount_type.discount_type_name, discount.discount_price,discount.discount_calculate
                FROM product.discount
                INNER JOIN product.discount_type ON discount.discount_type_id = discount_type.discount_type_id
            WHERE discount_id = :discount_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($data);
        $result = $sth->fetchAll();
        return $result;
    }
    public function getDiscountType($data)
    {
        $sql = 'SELECT discount_type_id, discount_type_name
        FROM product.discount_type
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return ($result);
    }
    public function addDiscount($data)
    {
        $add_discount_data = [
            "discount_name" => 0,
            "discount_type_id" => 0,
            "discount_price" => 0,
            "discount_calculate" => 0,
            "user_id" => $_SESSION['user_id']
        ];
        foreach ($add_discount_data as $key => $value) {
            if (array_key_exists($key, $data)) {
                $add_discount_data[$key] = $data[$key];
            }
        }
        $sql = "INSERT INTO product.discount(
            discount_name,discount_type_id,discount_price,discount_calculate, user_id)
            VALUES (:discount_name,:discount_type_id,:discount_price,:discount_calculate, :user_id);
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($add_discount_data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function EditDiscount($data)
    {
        $add_discount_data = [
            "discount_name" => 0,
            "discount_type_id" => 0,
            "discount_price" => 0,
            "discount_calculate" => 0,
            "discount_id" => 0
        ];
        foreach ($add_discount_data as $key => $value) {
            if (array_key_exists($key, $data)) {
                $add_discount_data[$key] = $data[$key];
            }
        }
        $sql = "UPDATE product.discount
            SET discount_name = :discount_name,discount_type_id = :discount_type_id,discount_price = :discount_price,discount_calculate = :discount_calculate
            WHERE discount_id = :discount_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($add_discount_data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function DeleteDiscount($data)
    {
        //資料格式範例 [{item_id:1}, {item_id:2}, ... ]
        $set_discount_data_array = [];
        $set_discount_data = [
            "discount_id" => 0
        ];
        foreach ($data['data'] as $row => $data_to_push) {
            foreach ($set_discount_data as $key => $value) {
                if (array_key_exists($key, $data_to_push)) {
                    $set_discount_data[$key] = (int) $data_to_push[$key];
                    array_push($set_discount_data_array, $set_discount_data);
                }
            }
        }
        foreach ($set_discount_data_array as $i => $delete_data) {
            $sql = "DELETE FROM product.discount
                WHERE discount.discount_id = :discount_id 
            ";
            $sth = $this->container->db->prepare($sql);
            $sth->execute($delete_data);
        }
    }
    public function getOrders($data)
    {
        $values = [
            "size" => 0,
            "length" => 0,
            "start" => 0
        ];
        if (array_key_exists("length", $data)) {
            $values["length"] = $data["length"];
        }
        if (array_key_exists("start", $data)) {
            $values["size"] = ($data["start"] / $values["length"] + 1) * $values["length"];
            $values["start"] = $data["start"];
        }
        unset($values['length']);
        $sql = "SELECT *
            FROM
            (
                SELECT order_data.* ,
                CASE WHEN order_data.discount_type_id = 1 THEN (order_data.order_sum * 100 / order_data.discount_calculate) -  order_data.order_sum 
                WHEN order_data.discount_type_id = 2 THEN order_data.discount_calculate
                ELSE order_data.order_sum END AS discount_pay
                FROM (
                SELECT product.order.order_id, \"user\".user_name,discount.discount_name, paying_type.paying_name, product.order.sum AS order_sum, product.order.order_date,product.order.paying_date,product.discount.discount_price,product.discount.discount_calculate,product.discount.discount_type_id, ROW_NUMBER() OVER (ORDER BY order_id ASC) AS rownum
                FROM product.order
                LEFT JOIN \"user\".\"user\" ON product.order.user_id = \"user\".user_id
                LEFT JOIN product.discount ON product.order.discount_id = product.discount.discount_id
                LEFT JOIN product.paying_type ON product.order.paying_id = product.paying_type.paying_id
                )order_data
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll();
        return $result;
    }
    public function getOrdersTotal($data)
    {
        $sql = "SELECT COUNT(*)
            FROM product.order
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }
    public function getOrder($data)
    {
        $sql = "SELECT order_data.* ,
                CASE WHEN order_data.discount_type_id = 1 THEN (order_data.order_sum * 100 / order_data.discount_calculate) -  order_data.order_sum 
                WHEN order_data.discount_type_id = 2 THEN order_data.discount_calculate
                ELSE order_data.order_sum END AS discount_pay
                FROM (
                SELECT product.order.order_id, \"user\".user_name,discount.discount_id, discount.discount_name, \"order\".paying_id, paying_type.paying_name, product.order.sum AS order_sum, product.order.order_date,product.order.paying_date,product.discount.discount_price,product.discount.discount_calculate,product.discount.discount_type_id
                FROM product.order
                LEFT JOIN \"user\".\"user\" ON product.order.user_id = \"user\".user_id
                LEFT JOIN product.discount ON product.order.discount_id = product.discount.discount_id
                LEFT JOIN product.paying_type ON product.order.paying_id = product.paying_type.paying_id
                WHERE \"order\".order_id = :order_id
        )order_data
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function addOrder($data)
    {
        $add_discount_data = [
            "user_id" => $_SESSION['user_id'],
            "discount_id" => 0,
            "sum" => 0,
            "order_date" => 0,
            "paying_id" => 0,
            "paying_date" => 0,

        ];
        foreach ($add_discount_data as $key => $value) {
            if (array_key_exists($key, $data)) {
                $add_discount_data[$key] = $data[$key];
            }
        }
        $sql = "INSERT INTO product.order(
            user_id,discount_id,sum,order_date, paying_id,paying_date)
            VALUES (:user_id,:discount_id,:sum,:order_date, :paying_id,:paying_date);
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($add_discount_data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function EditOrder($data)
    {
        $edit_order_data = [

            "discount_id" => 0,
            "sum" => 0,

            "order_date" => 0,
            "paying_id" => 0,
            "paying_date" => 0,
        ];
        foreach ($edit_order_data as $key => $value) {
            if (array_key_exists($key, $data)) {
                $edit_order_data[$key] = $data[$key];
            }
        }
        $sql = "UPDATE product.order
            SET discount_id = :discount_id, sum = :sum, order_date = :order_date, paying_id = :paying_id, paying_date = :paying_date
            WHERE order_id = :order_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($edit_order_data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function deleteOrders($data)
    {
        $conditions = "(";
        $values = [];
        foreach($data as $key => $value)
        {
            foreach($value as $Key => $Value)
            {
                $conditions .= ":article_id_{$key},";
                $values["article_id_{$key}"] = $Value["article_id"];
            }
        }
        $conditions = rtrim($conditions, ",");
        $conditions .= ")";
        $sql = "DELETE FROM product.order
            WHERE product.order_id IN {$conditions}
        ";
        $sth = $this->container->db->prepare($sql);
        if($sth->execute($values)){
            $result = ["status" => "success",];
        }
        else{
            $result = ["status" => "failed",];
        }
        return $result;
    }

    public function uploadFile($data)
    {
        global $container;
        $directory = $container->get('upload_directory');

        $uploadedFiles = $data;

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['file'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $file['file_name'] = $this->moveUploadedFile($directory, $uploadedFile);
            $file['file_client_name'] = $uploadedFile->getClientFilename();
        }
        return $file;
    }
    function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    public function insertFile($file)
    {
        $values = [
            "file_name" => "",
            "file_client_name" => ""
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $file)) {
                $values[$key] = $file[$key];
            }
        }
        $sql = "INSERT INTO product.file(file_name,file_client_name)
            VALUES (:file_name,:file_client_name)
            RETURNING file_id;
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getFile($data)
    {
        $sql = "SELECT file_name
            FROM product.file
            WHERE file_id = :file_id;
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($data);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($files as $file) {
            return $this->container->get('upload_directory') . DIRECTORY_SEPARATOR . $file['file_name'];
        }
    }

    function compressImage($source = false, $destination = false, $quality = 80, $filters = false)
    {
        $info = getimagesize($source);
        switch ($info['mime']) {
            case 'image/jpeg':
                /* Quality: integer 0 - 100 */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromjpeg($source);

            case 'image/gif':
                return imagecreatefromgif($source);

            case 'image/png':
                /* Quality: Compression integer 0(none) - 9(max) */
                if (!is_int($quality) or $quality < 0 or $quality > 9) $quality = 6;
                return imagecreatefrompng($source);

            case 'image/webp':
                /* Quality: Compression 0(lowest) - 100(highest) */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromwebp($source);

            case 'image/bmp':
                /* Quality: Boolean for compression */
                if (!is_bool($quality)) $quality = true;
                return imagecreatefrombmp($source);

            default:
                return;
        }
    }
}

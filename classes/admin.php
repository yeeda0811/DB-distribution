<?php

use Slim\Views\PhpRenderer;

class Admin
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getAdminTest()
    {
        $sql = 'SELECT *
                FROM public.test';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        return $result;
    }

    public function login($data)
    {

        $sql = "SELECT user_id
                FROM \"user\".\"user\" 
                WHERE account = :account AND password = :password";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($data);
        $result = $sth->fetchAll();
        $status = ["status" => "failed"];
        foreach ($result as $key => $value) {
            $_SESSION['user_id'] = $value['user_id'];
            $status = ["status" => "success"];
        }
        return $status;
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

    public function getAccountsTotal($data)
    {
        $sql = 'SELECT COUNT(*)
            FROM "user"."user"
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function getAccounts($data)
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
        $sql = 'SELECT *
            FROM
            (
                SELECT user_id, account, password, user_name, email, ROW_NUMBER() OVER (ORDER BY user_id ASC) AS rownum
                FROM "user"."user"
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll();
        return $result;
    }

    public function getRolesTotal($data)
    {
        $sql = 'SELECT COUNT(*)
            FROM "user"."role"
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function getRole($data)
    {
        $values = [
            "role_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT \"role\".role_id, \"role\".role_name,JSON_AGG(JSON_BUILD_OBJECT('permission_id',permission.permission_id)) AS permissions
            FROM \"user\".\"role\"
            LEFT JOIN \"user\".role_permission ON \"role\".role_id = role_permission.role_id
            LEFT JOIN \"user\".permission ON role_permission.permission_id = permission.permission_id
            WHERE \"role\".role_id = :role_id
            GROUP BY \"role\".role_id, \"role\".role_name
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll();
        foreach ($result as $key => $row) {
            foreach ($row as $row_key => $column) {
                if ($this->isJson($column)) {
                    $result[$key][$row_key] = json_decode($column, true);
                }
            }
        }
        return $result;
    }

    public function getAuthorities($data)
    {
        $values = [
            "role_id" => null,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT role_id, role_name, STRING_AGG(permission_name, ', ') AS permission_names
            FROM
            (
                SELECT \"role\".role_id, \"role\".role_name, permission.permission_id, permission.permission_name
                FROM \"user\".role_permission 
                LEFT JOIN \"user\".\"role\" ON \"user\".\"role\".role_id = \"user\".role_permission.role_id
                LEFT JOIN \"user\".permission ON \"user\".role_permission .permission_id = permission.permission_id
                GROUP BY \"role\".role_id, \"role\".role_name, permission.permission_id, permission.permission_name
                ORDER BY \"role\".role_id, permission.permission_id
            ) AS role_permission
            GROUP BY role_id, role_name
            ORDER BY role_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        return $result;
    }

    public function getAccountRole($data)
    {
        $values = [
            "user_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT \"user\".user_id, \"user\".account, \"user\".password, \"user\".email, \"user\".user_name,JSON_AGG(JSON_BUILD_OBJECT('role_id',role.role_id)) AS roles
                FROM \"user\".\"user\"
                LEFT JOIN \"user\".user_role ON \"user\".user_id = user_role.user_id
                LEFT JOIN \"user\".role ON user_role.role_id = \"role\".role_id
                WHERE \"user\".user_id = :user_id
                GROUP BY \"user\".user_id, \"user\".account, \"user\".password, \"user\".email, \"user\".user_name
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

    public function getRoles($data)
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
                SELECT *, ROW_NUMBER() OVER (ORDER BY permissions.role_id ASC) AS rownum
                FROM(
                    SELECT \"role\".role_id, \"role\".role_name,COALESCE(STRING_AGG(permission.permission_name,','),'-') AS permissions
                    FROM \"user\".\"role\"
                    LEFT JOIN \"user\".role_permission ON \"role\".role_id = role_permission.role_id
                    LEFT JOIN \"user\".permission ON role_permission.permission_id = permission.permission_id
                    GROUP BY \"role\".role_id, \"role\".role_name
                ) permissions
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll();
        return $result;
    }

    public function getOwnPermissions($data)
    {
        $values = [
            "user_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = 'SELECT permission.permission_name,permission.permission_url,permission.permission_icon
            FROM "user".user_role
            LEFT JOIN "user".role_permission ON role_permission.role_id = user_role.role_id
            LEFT JOIN "user".permission ON role_permission.permission_id = permission.permission_id
            WHERE user_role.user_id = :user_id
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getOwnUserName($data)
    {
        $values = [
            "user_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = 'SELECT "user".user_name
            FROM "user"."user"
            WHERE "user".user_id = :user_id
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPermissions($data)
    {
        $sql = 'SELECT permission_id, permission_name
        FROM "user".permission;
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllRole($data)
    {
        $sql = 'SELECT role_id, role_name
        FROM "user".role;
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAuthoritiesTotal($data)
    {
        $sql = 'SELECT COUNT(*)
            FROM "user"."role"
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function DeleteUser($data)
    {
        $conditions = "(";
        $values = [];
        foreach($data as $key => $value)
        {
            foreach($value as $Key => $Value)
            {
                $conditions .= ":user_id_{$key},";
                $values["user_id_{$key}"] = $Value["user_id"];
            }
        }
        $conditions = rtrim($conditions, ",");
        $conditions .= ")";
        $sql = "DELETE FROM \"user\".\"user\"
            WHERE \"user\".user_id IN {$conditions}
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

    public function DeleteRole($data)
    {
        $set_item_data_array = [];
        $set_item_data = [
            "role_id" => 0
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
            $sql = "DELETE FROM \"user\".role
                WHERE role.role_id = :role_id 
            ";
            $sth = $this->container->db->prepare($sql);
            $sth->execute($delete_data);
        }
    }

    // public function getAuthorities($data)
    // {
    //     $values = [
    //         "size" => 0,
    //         "length" => 0,
    //         "start" => 0
    //     ];
    //     if (array_key_exists("length", $data)) {
    //         $values["length"] = $data["length"];
    //     }
    //     if (array_key_exists("start", $data)) {
    //         $values["size"] = ($data["start"] / $values["length"] + 1) * $values["length"];
    //         $values["start"] = $data["start"];
    //     }
    //     unset($values['length']);
    //     $sql = 'SELECT *
    //         FROM
    //         (
    //             SELECT role_id, role_name, ROW_NUMBER() OVER (ORDER BY role_id ASC) AS rownum
    //             FROM "user"."role"(
    //                 LEFT JOIN "user"."role" ON "role"."role_id" = "role_permission"."role_id"
    //                 )
    //             INNER JOIN "user"."permission" ON "role_permission"."permission_id" = "permission"."permission_id"
    //             LIMIT :size
    //         )AS dt
    //         WHERE dt.rownum > :start
    //     ';
    //     $sth = $this->container->db->prepare($sql);
    //     $sth->execute($values);
    //     $result = $sth->fetchAll();
    //     return $result;
    // }

    public function getAccountIdentity($data)
    {
        $sql = 'SELECT role_id, role_name
        FROM "user".role
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return ($result);
    }

    public function drawPic()
    {

        function getCode($num, $w, $h)
        {
            $code = "";
            for ($i = 0; $i < $num; $i++) {
                $code .= rand(0, 9);
            }
            //4位驗證碼也可以用rand(1000,9999)直接生成
            //將生成的驗證碼寫入session，備驗證時用
            $_SESSION["codeCheck"] = $code;
            //建立圖片，定義顏色值
            header("Content-type: image/PNG");
            $im = imagecreate($w, $h);
            $black = imagecolorallocate($im, 0, 0, 0);
            $gray = imagecolorallocate($im, 200, 200, 200);
            $bgcolor = imagecolorallocate($im, 255, 255, 255);
            //填充背景
            imagefill($im, 0, 0, $gray);
            //畫邊框
            imagerectangle($im, 0, 0, $w - 1, $h - 1, $black);

            //將數字隨機顯示在畫布上,字元的水平間距和位置都按一定波動範圍隨機生成
            $strx = rand(3, 30);
            for ($i = 0; $i < $num; $i++) {
                $strpos = rand(1, 5);
                imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
                $strx += rand(6, 15);
            }
            imagepng($im); //輸出圖片
            imagedestroy($im); //釋放圖片所佔記憶體
        }
        getCode(4, 100, 40);
    }

    public function checkCode($verify)
    {
        if ($verify == $_SESSION["codeCheck"]) {
            $status = 'success';
        } else {
            $status = 'failed';
        }
        return $status;
    }

    public function getUsers($data)
    {
        $values = [
            "user_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = 'SELECT user_id, account, password, user_name, email
                FROM "user"."user"
                WHERE user_id = :user_id;
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function addUsers($data)
    {
        $values = [
            "account" => '',
            "password" => '',
            "user_name" => '',
            "email" => ''
        ];
        $stmt_string = "";
        $stmt_array = [];
        foreach ($data as $index => $row) {
            $stmt_string .= "(".implode(",",array_map(function($value,$index){
                return "{$value}_{$index}";
            },array_keys($values),array_fill(0,count($values),$index)))."),";
            foreach ($values as $key => $value) {
                $stmt_array["{$key}_{$index}"] = $value;
                if(array_key_exists($key,$row)){
                    $stmt_array["{$key}_{$index}"] = $row[$key];
                }
            }
        }
        var_dump($stmt_array);
        var_dump($stmt_string);
        exit(0);
        $sql = "INSERT INTO \"user\".\"user\"
                (account, password, user_name, email)
            VALUES {$stmt_string};
        ";
        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        return ($result);
    }

    public function patchUsers($data)
    {
        $values = [
            "account" => '',
            "password" => '',
            "user_name" => '',
            "email" => ''
        ];
        $stmt_string = "";
        $stmt_array = [];
        foreach ($data as $index => $row) {
            $stmt_string .= "(".implode(",",array_map(function($value,$index){
                return ":{$value}_{$index}";
            },array_keys($values),array_fill(0,count($values),$index)))."),";
            foreach ($values as $key => $value) {
                $stmt_array["{$key}_{$index}"] = $value;
                if(array_key_exists($key,$row)){
                    $stmt_array["{$key}_{$index}"] = $row[$key];
                }
            }
        }
        return $stmt_string;
        $sql = "INSERT INTO \"user\".\"user\" (account, password, user_name, email)
                VALUES {$stmt_string}
                ON CONFLICT (account) 
                DO UPDATE
                SET password = :password,
                account = :account,
                user_name = :user_name,
                email = :email
                WHERE accout = :account
                ";
        $sth = $this->container->db->prepare($sql);
        
        $result = $sth->execute($stmt_array);
        return ($result);
    }
}

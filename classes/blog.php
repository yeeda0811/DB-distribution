<?php

use Slim\Views\PhpRenderer;

class Blog
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
    public function getAdminBlogs($data)
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
            FROM(
                SELECT article.article_id, article.article_title,content.article_text, \"user\".user_name, TO_CHAR( article.article_date,'YYYY-MM-DD HH12:MIPM') AS article_date
                    , ROW_NUMBER() OVER ( ORDER BY article.article_date DESC) AS rownum
                FROM blog.article
                LEFT JOIN \"user\".\"user\" ON \"user\".User_id = article.user_id
                LEFT JOIN blog.content ON blog.content.article_id = blog.article.article_id 
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start
        "; //limit :  
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll();
        return $result;
    }
    function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    public function getAdminBlogsTotal($data)
    {
        $sql = 'SELECT COUNT(*)
            FROM blog.article
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function getBlogs($data)
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
        $sql = " SELECT * 
            FROM(
                SELECT * , ROW_NUMBER() OVER ( ORDER BY article.article_date ASC) AS rownum 
                FROM(
                    SELECT \"user\".\"user\".user_name, article.article_id, article.article_title, content.article_text, article.user_id, article.article_date, COALESCE(article_tag.tags,'-') AS tagtypes, ROW_NUMBER() OVER ( ORDER BY article.article_date DESC) AS row_number
                    FROM blog.article
                    LEFT JOIN blog.content ON blog.content.article_id = blog.article.article_id 
                    LEFT JOIN \"user\".\"user\" on \"user\".\"user\".user_id = blog.article.user_id
                    LEFT JOIN (
                        SELECT article_tag.article_id, STRING_AGG(tag.tag,',') AS tags
                        FROM blog.article_tag
                        LEFT JOIN blog.tag ON article_tag.tag_id = tag.tag_id
                        GROUP BY article_tag.article_id
                    )article_tag ON article.article_id = article_tag.article_id
                ) article
                LIMIT :size
            )AS dt
            WHERE dt.rownum > :start
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll();
        return $result;
    }
    public function getBlogsTotal($data)
    {
        $sql = 'SELECT COUNT(*)
            FROM blog.article
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function getBrowseBlogs($data)
    {
        $values = [
            "article_id" => 0,
        ];
        //dictionary
        //從data找article_id找到後放在裡面
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = 'SELECT blog.article.article_title,blog.content.article_text
            FROM blog.article
            LEFT JOIN blog.content ON blog.content.article_id = blog.article.article_id
            WHERE blog.article.article_id= :article_id;     
        ';
        //用article的article_id去找content
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        //用values找資料
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        //選取全部
        return $result;
    }
    public function getAdminBlog($data)
    {
        $values = [
            "article_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT article.article_id, article.article_title, JSON_AGG(JSON_BUILD_OBJECT('tag_id',tag.tag_id)) AS tagtypes, content.article_text
            FROM blog.article
            LEFT JOIN blog.content ON blog.content.article_id = blog.article.article_id
            LEFT JOIN blog.article_tag ON blog.article.article_id = blog.article_tag.article_id
            LEFT JOIN blog.tag ON blog.article_tag.tag_id = blog.tag.tag_id
            WHERE article.article_id =:article_id
            GROUP BY article.article_id, article.article_title, content.article_text;
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

    public function deleteBlog($data)
    {
        $set_item_data_array = [];
        $set_item_data = [
            "article_id" => 0
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
            $sql = 'DELETE article.article_id,article.article_title,content.article_text
                FROM blog.article
                LEFT JOIN blog.content ON blog.content.article_id=blog.article.article_id
                WHERE article.article_id =:article_id
            ';
            $sth = $this->container->db->prepare($sql);
            $sth->execute($delete_data);
        }
    }

    public function getAllTag($data)
    {
        $sql = 'SELECT *
        FROM "blog".tag;
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function patchBlogs($data)
    {
        $values = [
            "title" => '',
            "content" => '',
            "tag" => '',
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
                VALUES {$stmt_string} (:account)
                ON CONFLICT (account) 
                DO UPDATE
                SET password = :password
                SET account = :account
                SET user_name = :user_name
                SET email = :email
                WHERE accout = :account
                ";
        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($stmt_array);

        $sql = "DELETE
                INSERT
                ";
        $sth = $this->container->db->prepare($sql);
        
        $result = $sth->execute($stmt_array);
        return ($result);
    }
    

    // public function deleteBlog($data)
    // {    
    //     foreach ($data as $i => $delete_data) {0
    //         $sql = "DELETE FROM blog.article
    //         WHERE article.article_id=:article_id
    //         ";
    //         $sth = $this->container->db->prepare($sql);
    //         $sth->execute($delete_data);
    //     }
    // }
    // public function addBlog(){
    //     $sql = "INSERT INTO blog.article(article_id,article_title,user_id) VALUES (13,'hello13',13);
    //     INSERT INTO blog.content(article_text) VALUES ('<p>hello13/p>');
    //     ";
    // }
    // public function EditBlog(){
    //     $sql = "DELETE FROM blog.article
    //     WHERE article.article_id=:article_id
    //     ";
    // }
}

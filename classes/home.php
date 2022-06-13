<?php

use Slim\Views\PhpRenderer;

class Home
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
    public function getAllItems($data)
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
                    SELECT item.item_id, item.item_name, item.price,ROW_NUMBER() OVER (ORDER BY item.item_id ASC) AS rownum
                    FROM product.item
                    LIMIT :size
                )AS dt
                WHERE dt.rownum > :start 
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        // var_dump($data_array);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $values['row_size'] = 3;/* 一行幾筆 */
        $reverse = [];
        $reverse_temp = [];
        foreach ($result as $key => $value) {
            array_push($reverse_temp, $value);
            if ($key !== 0 && ($key + 1) % $values['row_size'] == 0) {
                array_push($reverse, $reverse_temp);
                $reverse_temp = [];
            }
            if ($key === count($result) - 1 && count($reverse_temp) !== 0) {
                for ($i = 0; $i < $values['row_size']; $i++) {
                    if (!array_key_exists($i, $reverse_temp)) {
                        $reverse_temp[$i] = [];
                    }
                }
                array_push($reverse, $reverse_temp);
            }
        }
        $result = $reverse;
        return $result;
    }
    public function getAllItemsTotal($data)
    {
        $sql = "SELECT COUNT(*)
            FROM product.item
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }
    public function getShowItem($data)
    {
        $values = [
            "item_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = 'SELECT blog.article.article_title,blog.content.article_text
            FROM blog.article
            LEFT JOIN blog.content ON blog.content.article_id=blog.article.article_id
            WHERE blog.article.article_id= :article_id;     
        ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}

<?php
include('connect.php');
$sql = 'SELECT * FROM customer';

$result = mysqli_query($conn, $sql); 

$myarray = array();

    //判斷資料表有沒有內容，如果是空的就不執行查詢
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){

            $myarray[] = $row;
        }
        //轉成 json 語法
        echo json_encode($myarray);
    }else{
        echo '沒有資料內容';
    }

mysqli_close($conn);

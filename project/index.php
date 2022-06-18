<?php
// require_once('connect.php');
include('connect.php');

$sql = "SELECT * FROM buyer";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 输出数据
    while($row = $result->fetch_assoc()) {
        echo "buyer_id: " . $row["buyer_id"] ."     ". "buyer_name: " . $row["buyer_name"] . "<br>";
    }
} else {
    echo "0 结果";
}
$conn->close();

?>
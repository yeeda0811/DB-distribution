<?php
$server_name = "140.127.74.186";
// $server_name = "localhost";
$username = "410977025";
$password = "410977025";
$db_name = "410977025";

// // Create connection
// $conn = mysqli_connect($server_name, $username, $password);

// // Check connection
// if (!$conn) {
//   die("Connection failed: " . mysqli_connect_error());
// }
// echo "Connected successfully";
// echo "fully";

$conn = new mysqli($server_name, $username, $password, $db_name);
// Check connection
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
} 

$sql = "SELECT buyer_id FROM buyer";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 输出数据
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["buyer_id"] . "<br>";
    }
} else {
    echo "0 结果";
}
$conn->close();

?>
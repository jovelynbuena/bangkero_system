<?php
require_once('config/db_connect.php');
$res = $conn->query("DESCRIBE galleries");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
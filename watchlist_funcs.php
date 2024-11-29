<?php
session_start();

if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}
$servername = "localhost";
$username = "COMP0178";
$password = "DatabaseCW";
$dbname = "AuctionSystem";

// 创建数据库连接
$connection = mysqli_connect($servername, $username, $password, $dbname);

if (!$connection) {
  die("Error connecting to database: " . mysqli_connect_error());
}
// Extract arguments from the POST variables:
$item_id = $_POST['arguments'];
$user_id = $_SESSION['userid'];

if ($_POST['functionname'] == "add_to_watchlist") {
  // TODO: Update database and return success/failure.
  $sql = "INSERT INTO Watchlist (UserId, ItemId) VALUES (?, ?)";
  $stmt = mysqli_prepare($connection, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $item_id);
    if (mysqli_stmt_execute($stmt)) {
      $res = "success"; // 操作成功
    } else {
      $res = "failure";
    }
    mysqli_stmt_close($stmt);
  } else {
    $res = "failure";
  }

} else if ($_POST['functionname'] == "remove_from_watchlist") {
  // TODO: Update database and return success/failure.
  $sql = "DELETE FROM Watchlist WHERE UserId = ? AND ItemId = ?";
  $stmt = mysqli_prepare($connection, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $item_id);
    if (mysqli_stmt_execute($stmt)) {
      $res = "success"; // 操作成功
    } else {
      $res = "failure";
    }
    mysqli_stmt_close($stmt);
  } else {
    $res = "failure";
  }
} else {
  $res = "failure";
}

// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;

?>
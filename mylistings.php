<?php
session_start();
include_once("header.php");
require("utilities.php");

 // TODO: Check user's credentials (cookie/session).
  // 检查用户是否已经登录
  if (!isset($_SESSION['userid'])) {
    header("Location: login.php"); // 如果没有登录，跳转到登录页面
    exit();
  }
  ?>

<div class="container">

<h2 class="my-3">My listings</h2>
  
  <?php
  // TODO: Perform a query to pull up their auctions.
  $servername = "localhost";
  $username = "COMP0178";
  $password = "DatabaseCW";
  $dbname = "AuctionSystem";
  
  // 连接到数据库
  $connection = mysqli_connect($servername, $username, $password, $dbname);
  if (!$connection) {
      die("Error connecting to database: " . mysqli_connect_error());
  }
  
  // 获取当前登录用户的 ID
  $user_id = $_SESSION['userid'];
  
  // 查询用户发布的拍卖
  $sql = "
  SELECT 
      i.ItemId,
      i.ItemName,
      i.Description,
      a.StartingPrice,
      a.EndDate,
      (SELECT COUNT(*) FROM Bids b WHERE b.ItemId = a.ItemId) AS NumBids
  FROM 
      Auctions a
  JOIN 
      Items i ON a.ItemId = i.ItemId
  WHERE 
      a.SellerId = ?";
  
  // 准备 SQL 查询
  $stmt = mysqli_prepare($connection, $sql);
  if (!$stmt) {
      die("Error preparing SQL statement: " . mysqli_error($connection));
  }
  
  // 绑定用户 ID 参数
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  
  // 执行查询
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  ?>
  
  <div class="container mt-5">
      <ul class="list-group">
  <?php
  // TODO: Loop through results and print them out as list items.
if ($result && mysqli_num_rows($result) > 0) {
    // 遍历结果集
    while ($row = mysqli_fetch_assoc($result)) {
        $item_id = $row['ItemId'];
        $title = htmlspecialchars($row['ItemName']);
        $description = htmlspecialchars($row['Description']);
        $current_price = $row['StartingPrice'];
        $num_bids = $row['NumBids'];
        $end_date = new DateTime($row['EndDate']);
        // 使用 utilities.php 中定义的函数 print_listing_li
        print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
    }
} else {
    echo "<p>You have no listings at the moment.</p>";
}

// 关闭数据库连接
mysqli_stmt_close($stmt);
mysqli_close($connection);
?>
    </ul>
</div>

<?php include_once("footer.php") ?>

?>


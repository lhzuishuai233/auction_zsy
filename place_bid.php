<?php
session_start();

// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.

$item_id = $_SESSION['item_id'];

$servername = "localhost";
$new_user = "COMP0178";
$new_password = "DatabaseCW";
$dbname = "AuctionSystem";

// 连接到数据库
$connection = mysqli_connect($servername, $new_user, $new_password, $dbname);

if (!$connection) {
    die("Error connecting to database: " . mysqli_connect_error());
}

// 检查是否用户已登录
if (!isset($_SESSION['userid'])) {
    echo "<p style='color: red;'>You need to log in to place a bid.</p>";
    header("refresh:1;url=index.php"); // 3秒后重定向到登录页面
    exit();
}
// 检查是否为buyer
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'buyer') {
    echo "<p style='color: red;'>You need to log in as a buyer to perform this action.</p>";
    header("refresh:1;url=index.php"); // 3秒后重定向到首页
    exit();
}


// 提取 POST 变量
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bid_amount = $_POST['bid'] ?? null;

    if (!$bid_amount || !is_numeric($bid_amount) || $bid_amount <= 0) {
        echo "<p style='color: red;'>Invalid bid. Please try again.</p>";
        exit();
    }

    // 检查拍卖状态和当前最高出价
    $sql = "SELECT StartingPrice, EndDate, 
            (SELECT MAX(BidAmount) FROM Bids WHERE ItemId = ?) AS CurrentBid 
        FROM Auctions 
        WHERE ItemId = ?";

    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $item_id, $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $starting_price = $row['StartingPrice'];
        $end_date = new DateTime($row['EndDate']);
        $current_bid = $row['CurrentBid'] ?? $starting_price;

        // 检查拍卖是否已结束
        $now = new DateTime();
        if ($now > $end_date) {
            echo "<p style='color: red;'>This auction has already ended.</p>";
            exit();
        }

        // 检查出价是否高于当前最高出价
        if ($bid_amount <= $current_bid) {
            echo "<p style='color: red;'>Your bid must be higher than the current bid (£" . number_format($current_bid, 2) . ").</p>";
            exit();
        }

        // 插入出价到 Bids 表
        $user_id = $_SESSION['userid'];
        $bid_sql = "INSERT INTO Bids (ItemId, BuyerId, BidAmount, BidTime) VALUES (?, ?, ?, NOW())";
        $bid_stmt = mysqli_prepare($connection, $bid_sql);
        mysqli_stmt_bind_param($bid_stmt, "iid", $item_id, $user_id, $bid_amount);

        if (mysqli_stmt_execute($bid_stmt)) {
            echo "<p style='color: green;'>Your bid of £" . number_format($bid_amount, 2) . " has been successfully placed!</p>";
            header("refresh:2;url=listing.php?item_id=" . $item_id);
        } else {
            echo "<p style='color: red;'>Failed to place your bid. Please try again later.</p>";
        }

        mysqli_stmt_close($bid_stmt);
    } else {
        echo "<p style='color: red;'>Invalid auction. Please try again.</p>";
        exit();
    }

    // 关闭数据库连接
    mysqli_stmt_close($stmt);
}
mysqli_close($connection);



?>
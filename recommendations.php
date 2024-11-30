<?php
session_start();

include_once("header.php");
require("utilities.php");

$current_user_id = $_SESSION['userid'];

// 连接数据库
$servername = "localhost";
$username = "COMP0178";
$password = "DatabaseCW";
$dbname = "AuctionSystem";

$connection = mysqli_connect($servername, $username, $password, $dbname);
if (!$connection) {
    die("Error connecting to database: " . mysqli_connect_error());
}

// 获取用户对哪些分类出价过
$sql_user_categories = "
SELECT DISTINCT i.Category
FROM Bids b
JOIN Items i ON b.ItemId = i.ItemId
WHERE b.BuyerId = {$current_user_id};
";

$result_categories = mysqli_query($connection, $sql_user_categories);
if (!$result_categories) {
    die("Error fetching user categories: " . mysqli_error($connection));
}

$user_categories = [];
while ($row = mysqli_fetch_assoc($result_categories)) {
    $user_categories[] = $row['Category'];
}

if (empty($user_categories)) {
    echo "<div class='container mt-5'><p>No recommendations available. You haven't bid on any items yet.</p></div>";
    include_once("footer.php");
    exit();
}

// 将用户感兴趣的分类转换为 SQL 中的 IN 子句
$user_categories_sql = "'" . implode("','", $user_categories) . "'";

// 查询推荐的拍卖列表
$sql_recommendations = "
SELECT DISTINCT a.AuctionId, i.ItemId, i.ItemName, i.Description, a.StartingPrice, a.EndDate,
    (SELECT COUNT(*) FROM Bids b WHERE b.ItemId = a.ItemId) AS NumBids,
    CASE
        WHEN EXISTS (
            SELECT 1
            FROM Bids b
            WHERE b.ItemId = a.ItemId AND b.BuyerId = {$current_user_id}
        ) THEN 2
        ELSE 1
    END AS Priority
FROM Auctions a
JOIN Items i ON a.ItemId = i.ItemId
WHERE i.Category IN ({$user_categories_sql})
GROUP BY a.AuctionId
ORDER BY Priority ASC, a.EndDate ASC
";


$result_recommendations = mysqli_query($connection, $sql_recommendations);
if (!$result_recommendations) {
    die("Error fetching recommendations: " . mysqli_error($connection));
}

// 分页逻辑
$results_per_page = 10; // 每页显示 10 条
$total_results = mysqli_num_rows($result_recommendations);
$max_page = ceil($total_results / $results_per_page);

if (!isset($_GET['page'])) {
    $curr_page = 1;
} else {
    $curr_page = (int)$_GET['page'];
}

$offset = ($curr_page - 1) * $results_per_page;

// 查询带分页的推荐拍卖
$sql_paginated_recommendations = $sql_recommendations . " LIMIT {$results_per_page} OFFSET {$offset};";
$result_paginated = mysqli_query($connection, $sql_paginated_recommendations);
if (!$result_paginated) {
    die("Error fetching recommendations: " . mysqli_error($connection) . "<br>SQL: " . $sql_paginated_recommendations);
}


?>

<div class="container mt-5">
    <h2 class="my-3">Recommended Auctions</h2>
    <ul class="list-group">
        <?php
        if (mysqli_num_rows($result_paginated) > 0) {
            while ($row = mysqli_fetch_assoc($result_paginated)) {
                $item_id = $row['ItemId'];
                $title = $row['ItemName'];
                $description = $row['Description'];
                $current_price = $row['StartingPrice'];
                $num_bids = $row['NumBids'];
                $end_date = new DateTime($row['EndDate']);

                // 使用 utilities.php 中的 print_listing_li 函数来渲染拍卖项
                print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
            }
        } else {
            echo "<p>No recommendations available.</p>";
        }
        ?>
    </ul>

    <!-- 分页导航 -->
    <nav aria-label="Search results pages" class="mt-5">
        <ul class="pagination justify-content-center">
            <?php
            $querystring = "";
            foreach ($_GET as $key => $value) {
                if ($key != "page") {
                    $querystring .= "$key=$value&amp;";
                }
            }

            if ($curr_page != 1) {
                echo ('
                <li class="page-item">
                    <a class="page-link" href="recommend.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
                        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>');
            }

            for ($i = 1; $i <= $max_page; $i++) {
                if ($i == $curr_page) {
                    echo '<li class="page-item active">';
                } else {
                    echo '<li class="page-item">';
                }
                echo '<a class="page-link" href="recommendations.php?' . $querystring . 'page=' . $i . '">' . $i . '</a></li>';
            }

            if ($curr_page != $max_page) {
                echo ('
                <li class="page-item">
                    <a class="page-link" href="recommendations.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
                        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>');
            }
            ?>
        </ul>
    </nav>
</div>

<?php
// 关闭数据库连接
mysqli_close($connection);
include_once("footer.php");
?>

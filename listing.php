<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<?php

// Get info from the URL:
$item_id = $_GET['id'] ?? null;

if (!$item_id || !is_numeric($item_id)) {
    die("<p style='color: red;'>Invalid item ID.</p>");
}


$_SESSION['item_id'] = $item_id;

// TODO: Use item_id to make a query to the database.
$servername = "localhost";
$new_user = "COMP0178";
$new_password = "DatabaseCW";
$dbname = "AuctionSystem";

$connection = mysqli_connect($servername, $new_user, $new_password, $dbname);

if (!$connection) {
  die("Error connecting to database: " . mysqli_connect_error());
}


// 查询数据库以获取拍卖详细信息

$sql = "
  SELECT 
      a.StartingPrice, 
      a.EndDate, 
      i.ItemName, 
      i.Description, 
      a.Status,
      COUNT(b.BidId) AS NumBids,
      MAX(b.BidAmount) AS CurrentBid
  FROM 
      Auctions a 
  JOIN 
      Items i 
  ON 
      a.ItemId = i.ItemId
  LEFT JOIN 
      Bids b 
  ON 
      b.ItemId = a.ItemId
  WHERE 
      a.ItemId = ?
  GROUP BY 
      a.AuctionId";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
  die("Error preparing statement: " . mysqli_error($connection));
}

// 绑定参数并执行查询
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


if ($row = mysqli_fetch_assoc($result)) {
  $title = $row['ItemName'];
  $description = $row['Description'];
  $current_price = $row['CurrentBid'] ?? $row['StartingPrice'];
  $num_bids = 25;//需被替换$row['NumBids']如果Bid表完成了的话
  $end_time = new DateTime($row['EndDate']);
  $status = $row['Status'];
} else {
  echo "<p style='color: red;'>Item not found.</p>";
  exit();
}



// DELETEME: For now, using placeholder data.
//$title = "Placeholder title";
//$description = "Description blah blah blah";
//$current_price = 30.50;
//$num_bids = 1;
//$end_time = new DateTime('2020-11-02T00:00:00');

// TODO: Note: Auctions that have ended may pull a different set of data,
//       like whether the auction ended in a sale or was cancelled due
//       to lack of high-enough bids. Or maybe not.



// Calculate time to auction end:
$now = new DateTime();

if ($now < $end_time) {
  $time_to_end = date_diff($now, $end_time);
  $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
}

if ($now >= $end_time) {
  $result_sql = "
  SELECT 
      MAX(b.BidAmount) AS final_price,
      a.ReservePrice,
      b.BuyerId AS winner_id
  FROM 
      Auctions a
  LEFT JOIN 
      Bids b ON a.ItemId = b.ItemId
  WHERE 
      a.ItemId = ?
  GROUP BY 
      a.ItemId";

  $result_stmt = mysqli_prepare($connection, $result_sql);
  if (!$result_stmt) {
    die("Error preparing statement: " . mysqli_error($connection));
  }

  mysqli_stmt_bind_param($result_stmt, "i", $item_id);
  mysqli_stmt_execute($result_stmt);
  $result_data = mysqli_stmt_get_result($result_stmt);

  if ($result_row = mysqli_fetch_assoc($result_data)) {
    $final_price = $result_row['final_price'];
    $reserve_price = $result_row['ReservePrice'];
    $winner_id = $result_row['winner_id'];
  } else {
    echo "<p style='color: red;'>No auction data found.</p>";
  }
  mysqli_stmt_close($result_stmt);
}

mysqli_stmt_close($stmt);


// TODO: If the user has a session, use it to make a query to the database
//       to determine if the user is already watching this item.
//       For now, this is hardcoded.
// 检查用户是否登录
$has_session = isset($_SESSION['userid']);
$watching = false;

if ($has_session) {
    // 获取当前用户 ID 和当前 Item ID
    $user_id = $_SESSION['userid'];

    // 查询数据库以检查用户是否已经关注该商品
    $watch_sql = "SELECT COUNT(*) AS is_watching FROM Watchlist WHERE UserId = ? AND ItemId = ?";
    $watch_stmt = mysqli_prepare($connection, $watch_sql);
    if ($watch_stmt) {
        mysqli_stmt_bind_param($watch_stmt, "ii", $user_id, $item_id);
        mysqli_stmt_execute($watch_stmt);
        $watch_result = mysqli_stmt_get_result($watch_stmt);

        if ($watch_row = mysqli_fetch_assoc($watch_result)) {
          if ($watch_row['is_watching'] > 0){
            $watching = true;
          }
        } else{
          $watching = false;
        }

        mysqli_stmt_close($watch_stmt);
    } else {
        echo "<p style='color: red;'>Error checking watchlist: " . mysqli_error($connection) . "</p>";
    }
}
// 关闭连接
mysqli_close($connection);

?>


<div class="container">

  <div class="row"> <!-- Row #1 with auction title + watch button -->
    <div class="col-sm-8"> <!-- Left col -->
      <h2 class="my-3"><?php echo ($title); ?></h2>
    </div>
    <div class="col-sm-4 align-self-center"> <!-- Right col -->
      <?php
      /* The following watchlist functionality uses JavaScript, but could
         just as easily use PHP as in other places in the code */
      if ($now < $end_time):
        ?>
        <div id="watch_nowatch" <?php if ($has_session && $watching)
          echo ('style="display: none"'); ?>>
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to
            watchlist</button>
        </div>
        <div id="watch_watching" <?php if (!$has_session || !$watching)
          echo ('style="display: none"'); ?>>
          <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
        </div>
      <?php endif/* Print nothing otherwise */ ?>
    </div>
  </div>

  <div class="row"> <!-- Row #2 with auction description + bidding info -->
    <div class="col-sm-8"> <!-- Left col with item info -->

      <div class="itemDescription">
        <?php echo ($description); ?>
      </div>

    </div>

    <div class="col-sm-4"> <!-- Right col with bidding info -->

      <p>
        <?php if ($now > $end_time): ?>
          This auction ended <?php echo (date_format($end_time, 'j M H:i')) ?>
          <!-- TODO: Print the result of the auction here? -->
          <?php if (isset($final_price) && $final_price >= $reserve_price): ?>
            <p>Auction ended successfully! Final price: £<?php echo number_format($final_price, 2); ?>. Winner: User ID <?php echo $winner_id; ?>.</p>
        <?php elseif (isset($final_price) && $final_price > 0): ?>
            <p>Auction ended but reserve price (£<?php echo number_format($reserve_price, 2); ?>) was not met. Auction failed.</p>
        <?php else: ?>
            <p>Auction ended with no bids. Auction failed.</p>
            <?php endif; ?>
        <?php else: ?>
          Auction ends <?php echo (date_format($end_time, 'j M H:i') . $time_remaining) ?>
        </p>
        <p class="lead">Current bid: £<?php echo (number_format($current_price, 2)) ?></p>

        <!-- Bidding form -->
        <form method="POST" action="place_bid.php">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">£</span>
            </div>
            <input type="number" class="form-control" name="bid" id="bid">
          </div>
          <button type="submit" class="btn btn-primary form-control">Place bid</button>
        </form>
      <?php endif ?>


    </div> <!-- End of right col with bidding info -->

  </div> <!-- End of row #2 -->



  <?php include_once("footer.php") ?>


  <script>
    // JavaScript functions: addToWatchlist and removeFromWatchlist.

    function addToWatchlist(button) {
      console.log("These print statements are helpful for debugging btw");

      // This performs an asynchronous call to a PHP function using POST method.
      // Sends item ID as an argument to that function.
      $.ajax('watchlist_funcs.php', {
        type: "POST",
        data: { functionname: 'add_to_watchlist', arguments: [<?php echo ($item_id); ?>] },

        success:
          function (obj, textstatus) {
            // Callback function for when call is successful and returns obj
            console.log("Success");
            var objT = obj.trim();

            if (objT == "success") {
              $("#watch_nowatch").hide();
              $("#watch_watching").show();
            }
            else {
              var mydiv = document.getElementById("watch_nowatch");
              mydiv.appendChild(document.createElement("br"));
              mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
            }
          },

        error:
          function (obj, textstatus) {
            console.log("Error");
          }
      }); // End of AJAX call

    } // End of addToWatchlist func

    function removeFromWatchlist(button) {
      // This performs an asynchronous call to a PHP function using POST method.
      // Sends item ID as an argument to that function.
      $.ajax('watchlist_funcs.php', {
        type: "POST",
        data: { functionname: 'remove_from_watchlist', arguments: [<?php echo ($item_id); ?>] },

        success:
          function (obj, textstatus) {
            // Callback function for when call is successful and returns obj
            console.log("Success");
            var objT = obj.trim();

            if (objT == "success") {
              $("#watch_watching").hide();
              $("#watch_nowatch").show();
            }
            else {
              var mydiv = document.getElementById("watch_watching");
              mydiv.appendChild(document.createElement("br"));
              mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
            }
          },

        error:
          function (obj, textstatus) {
            console.log("Error");
          }
      }); // End of AJAX call

    } // End of addToWatchlist func
  </script>
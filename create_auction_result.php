<?php include_once("header.php") ?>

<div class="container my-5">

    <?php
    date_default_timezone_set('Europe/London'); // 根据实际时区调整
    

    // This function takes the form data and adds the new auction to the database.
    
    /* TODO #1: Connect to MySQL database (perhaps by requiring a file that
                already does this). */
    $servername = "localhost";
    $new_user = "COMP0178";
    $new_password = "DatabaseCW";
    $dbname = "AuctionSystem";

    $connection = mysqli_connect($servername, $new_user, $new_password, $dbname);

    if (!$connection) {
        die("Error connecting to database: " . mysqli_connect_error());
    }


    /* TODO #2: Extract form data into variables. Because the form was a 'post'
                form, its data can be accessed via $POST['auctionTitle'], 
                $POST['auctionDetails'], etc. Perform checking on the data to
                make sure it can be inserted into the database. If there is an
                issue, give some semi-helpful feedback to user. */
    $title = trim($_POST['auctionTitle'] ?? '');
    $details = trim($_POST['auctionDetails'] ?? '');
    $category = trim($_POST['auctionCategory'] ?? '');
    $startingPrice = trim($_POST['auctionStartPrice'] ?? '');
    $reservePrice = trim($_POST['auctionReservePrice'] ?? '');
    $endDate = trim($_POST['auctionEndDate'] ?? '');

    $errors = [];


    // Validate inputs
    if (empty($title)) {
        $errors[] = "Auction title is required.";
    }
    if (empty($category)) {
        $errors[] = "Auction category is required.";
    }
    if (!is_numeric($startingPrice) || $startingPrice <= 0) {
        $errors[] = "Starting price must be a positive number.";
    }
    if (!empty($reservePrice) && (!is_numeric($reservePrice) || $reservePrice <= 0)) {
        $errors[] = "Reserve price must be a positive number.";
    }
    if (empty($endDate) || !strtotime($endDate)) {
        $errors[] = "A valid end date is required.";
    }
    if (strtotime($endDate) <= time()) {
        $errors[] = "End date must be in the future.";
    }

    // If validation fails, display errors and stop execution
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
        exit;
    }


    /* TODO #3: If everything looks good, make the appropriate call to insert
                data into the database. */
    // 插入商品数据到 Items 表
    $itemSql = "INSERT INTO Items (ItemName, Description, Category) VALUES (?, ?, ?)";
    $itemStmt = mysqli_prepare($connection, $itemSql);
    if ($itemStmt) {
        mysqli_stmt_bind_param($itemStmt, "sss", $title, $details, $category);
        if (mysqli_stmt_execute($itemStmt)) {
            $itemId = mysqli_insert_id($connection); // 获取新插入商品的 ItemId
    
            // 插入拍卖数据到 Auctions 表
            $sellerId = $_SESSION['userid']; // 获取当前登录用户 ID
            $auctionSql = "INSERT INTO Auctions (ItemId, StartingPrice, ReservePrice, EndDate, SellerId) VALUES (?, ?, ?, ?, ?)";
            $auctionStmt = mysqli_prepare($connection, $auctionSql);
            if ($auctionStmt) {
                mysqli_stmt_bind_param($auctionStmt, "iddsi", $itemId, $startingPrice, $reservePrice, $endDate, $sellerId);
                if (mysqli_stmt_execute($auctionStmt)) {
                    $auctionId = mysqli_insert_id($connection); // 获取新拍卖的 AuctionId
                    echo '<div class="text-center">Auction successfully created! <a href="listing.php?id=' . $auctionId . '">View your new listing.</a></div>';
                } else {
                    echo "<p style='color: red;'>Error creating auction: " . mysqli_error($connection) . "</p>";
                }
                mysqli_stmt_close($auctionStmt);
            } else {
                echo "<p style='color: red;'>Error preparing the auction SQL statement: " . mysqli_error($connection) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Error inserting item: " . mysqli_error($connection) . "</p>";
        }
        mysqli_stmt_close($itemStmt);
    } else {
        echo "<p style='color: red;'>Error preparing the item SQL statement: " . mysqli_error($connection) . "</p>";
    }


    // Close the connection
    mysqli_close($connection);


    ?>

</div>


<?php include_once("footer.php") ?>
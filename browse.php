<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<div class="container">

  <h2 class="my-3">Browse listings</h2>

  <div id="searchSpecs">
    <!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
    <form method="get" action="browse.php">
      <div class="row">
        <div class="col-md-5 pr-0">
          <div class="form-group">
            <label for="keyword" class="sr-only">Search keyword:</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-transparent pr-0 text-muted">
                  <i class="fa fa-search"></i>
                </span>
              </div>
              <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything">
            </div>
          </div>
        </div>
        <div class="col-md-3 pr-0">
          <div class="form-group">
            <label for="cat" class="sr-only">Search within:</label>
            <select class="form-control" id="cat">
              <option selected value="estate">Real estate</option>
              <option value="stock">Stock rights</option>
              <option value="car">Luxury car</option>
              <option value="porcelain">Antique porcelain</option>
              <option value="celebrity">Celebrity calligraphy and painting</option>
              <option value="furniture">Antique furniture</option>
              <option value="clothes">Clothes and bag</option>
              <option value="jewelry">Jewelry and watch</option>
              <option value="toy">Toy</option>
              <option value="other">Other categories</option>
              
            </select>
          </div>
        </div>
        <div class="col-md-3 pr-0">
        <div class="form-inline">
  <label class="mx-2" for="order_by">Sort by:</label>
  <select class="form-control" id="order_by" name="order_by">
    <option value="pricelow" <?php if ($ordering == 'pricelow') echo 'selected'; ?>>Price (low to high)</option>
    <option value="pricehigh" <?php if ($ordering == 'pricehigh') echo 'selected'; ?>>Price (high to low)</option>
    <option value="date" <?php if ($ordering == 'date') echo 'selected'; ?>>Soonest expiry</option>
  </select>
</div>
        </div>
        <div class="col-md-1 px-0">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </div>
    </form>
  </div> <!-- end search specs bar -->


</div>

<?php
// Retrieve these from the URL
if (!isset($_GET['keyword'])) {
  // TODO: Define behavior if a keyword has not been specified.
  $keyword = '';
} else {
  $keyword = $_GET['keyword'];
}

if (!isset($_GET['cat'])) {
  // TODO: Define behavior if a category has not been specified.
  $category = '';
} else {
  $category = $_GET['cat'];
}

if (!isset($_GET['order_by'])) {
  // TODO: Define behavior if an order_by value has not been specified.
  $ordering = 'pricelow';
} else {
  $ordering = $_GET['order_by'];
}

if (!isset($_GET['page'])) {
  $curr_page = 1;
} else {
  $curr_page = $_GET['page'];
}

/* TODO: Use above values to construct a query. Use this query to 
   retrieve data from the database. (If there is no form data entered,
   decide on appropriate default value/default query to make. */


/* For the purposes of pagination, it would also be helpful to know the
   total number of results that satisfy the above query */
// $num_results = 96; // TODO: Calculate me for real
// $results_per_page = 10;
// $max_page = ceil($num_results / $results_per_page);
?>

<div class="container mt-5">

  <!-- TODO: If result set is empty, print an informative message. Otherwise... -->

  <ul class="list-group">

    <!-- TODO: Use a while loop to print a list item for each auction listing
     retrieved from the query -->

    <?php
<<<<<<< HEAD
    // Demonstration of what listings will look like using dummy data.
    $item_id = "87021";
    $title = "Dummy title";
    $description = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum eget rutrum ipsum. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus feugiat, ipsum vel egestas elementum, sem mi vestibulum eros, et facilisis dui nisi eget metus. In non elit felis. Ut lacus sem, pulvinar ultricies pretium sed, viverra ac sapien. Vivamus condimentum aliquam rutrum. Phasellus iaculis faucibus pellentesque. Sed sem urna, maximus vitae cursus id, malesuada nec lectus. Vestibulum scelerisque vulputate elit ut laoreet. Praesent vitae orci sed metus varius posuere sagittis non mi.";
    $current_price = 30;
    $num_bids = 1;
    $end_date = new DateTime('2020-09-16T11:00:00');

    // This uses a function defined in utilities.php
    // print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);

    $item_id = "516";
    $title = "Different title";
    $description = "Very short description.";
    $current_price = 13.50;
    $num_bids = 3;
    $end_date = new DateTime('2020-11-02T00:00:00');

    // print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);



=======
    
>>>>>>> b0d08fe27baf1f0b40b023d324e5c8d62f0147a2
    // 连接数据库
    $servername = "localhost";
    $new_user = "COMP0178";
    $new_password = "DatabaseCW";
    $dbname = "AuctionSystem";

    $connection = mysqli_connect($servername, $new_user, $new_password, $dbname);

    if (!$connection) {
      die("Error connecting to database: " . mysqli_connect_error());
    }

    // 查询总记录数
    $sql_count = "
                  SELECT COUNT(*) AS TotalResults
                  FROM Auctions a
                  JOIN Items i ON a.ItemId = i.ItemId;
                ";
    $result_count = mysqli_query($connection, $sql_count);
    $row_count = mysqli_fetch_assoc($result_count);
    $num_results = (int)$row_count['TotalResults']; // 总记录数

    $results_per_page = 5; // 每页多少条
    $max_page = ceil($num_results / $results_per_page);
    $offset = ($curr_page - 1) * $results_per_page;

    // 查询 Auctions 和 Items 数据
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
<<<<<<< HEAD
  ORDER BY 
      a.EndDate ASC
  LIMIT {$results_per_page} OFFSET {$offset}
      ";
    $result = mysqli_query($connection, $sql);
=======
  WHERE 1=1
  ";
  if (!empty($keyword)) {
    $sql .= " AND (i.ItemName LIKE ? OR i.Description LIKE ?)";
}
  
// Add ordering
$order_by_options = [
  'pricelow' => 'a.StartingPrice ASC',
  'pricehigh' => 'a.StartingPrice DESC',
  'date' => 'a.EndDate ASC'
];
$order_by_clause = $order_by_options[$ordering] ?? $order_by_options['pricelow'];
$sql .= " ORDER BY $order_by_clause";

// Prepare the statement
$stmt = mysqli_prepare($connection, $sql);
>>>>>>> b0d08fe27baf1f0b40b023d324e5c8d62f0147a2

if (!$stmt) {
  die("Error preparing statement: " . mysqli_error($connection));
}

// Bind parameters if keyword is provided
if (!empty($keyword)) {
  $search_param = '%' . $keyword . '%';
  mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
}

// Execute the statement
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="container mt-5">
<ul class="list-group">
  <?php
  // Display the results
  if ($result && mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
<<<<<<< HEAD
        $item_id = $row['ItemId'];
        $title = $row['ItemName'];
        $description = $row['Description'];
        $current_price = $row['StartingPrice'];
        //需被替换$row['NumBids']如果Bid表完成了的话
        $num_bids = $row['NumBids']; 
        $end_date = new DateTime($row['EndDate']);
        print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
      }
    } else {
      echo "<p style='color: red;'>Error fetching auction data: " . mysqli_error($connection) . "</p>";
    }
=======
          $item_id = $row['ItemId'];
          $title = htmlspecialchars($row['ItemName']);
          $description = htmlspecialchars($row['Description']);
          $current_price = $row['StartingPrice'];
          $num_bids = $row['NumBids'];
          $end_date = new DateTime($row['EndDate']);
>>>>>>> b0d08fe27baf1f0b40b023d324e5c8d62f0147a2

          // Use print_listing_li to display the auction listing
          print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
      }
  } else {
      echo "<li class='list-group-item'>No results found for '<strong>" . htmlspecialchars($keyword) . "</strong>'</li>";
  }

  // Close the connection
  mysqli_close($connection);

    ?>

  </ul>

  <!-- Pagination for results listings -->
  <nav aria-label="Search results pages" class="mt-5">
    <ul class="pagination justify-content-center">

      <?php

      // Copy any currently-set GET variables to the URL.
      $querystring = "";
      foreach ($_GET as $key => $value) {
        if ($key != "page") {
          $querystring .= "$key=$value&amp;";
        }
      }

      $high_page_boost = max(3 - $curr_page, 0);
      $low_page_boost = max(2 - ($max_page - $curr_page), 0);
      $low_page = max(1, $curr_page - 2 - $low_page_boost);
      $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

      if ($curr_page != 1) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
      }

      for ($i = $low_page; $i <= $high_page; $i++) {
        if ($i == $curr_page) {
          // Highlight the link
          echo ('
    <li class="page-item active">');
        } else {
          // Non-highlighted link
          echo ('
    <li class="page-item">');
        }

        // Do this in any case
        echo ('
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
      }

      if ($curr_page != $max_page) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
      }
      ?>

    </ul>
  </nav>


</div>



<?php include_once("footer.php") ?>

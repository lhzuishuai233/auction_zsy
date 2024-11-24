<?php
// 登录初始管理员账户
$servername = "localhost";
$admin_username = "root"; // 初始管理员用户名
$admin_password = "";     // 初始管理员密码

// 创建连接
$connection = mysqli_connect($servername, $admin_username, $admin_password);


$dbname = "AuctionSystem"; // 我要创建的数据库名称


// 检查连接是否成功
if (!$connection) {
    die('Error connecting to MySQL server: ' . mysqli_connect_error());
}
echo "Connected successfully to MySQL server.<br>";

// 新建一个用户在该用户下创建我们的数据库
$new_user = 'COMP0178'; // 新建用户名
$new_password = 'DatabaseCW'; // 新建用户密码

// 用于在建表初期测试，保证每次运行该文档都重置
$sql = "DROP USER IF EXISTS '$new_user'@'localhost'";
if (mysqli_query($connection, $sql)) {
    echo "User '$new_user' deleted successfully.<br>";
} else {
    die("Error deleting user '$new_user': " . mysqli_error($connection));
}


// 创建用户
$sql = "CREATE USER IF NOT EXISTS '$new_user'@'localhost' IDENTIFIED BY '$new_password'";
if (mysqli_query($connection, $sql)) {
    echo "User '$new_user' created successfully.<br>";
} else {
    die("Error creating user '$new_user': " . mysqli_error($connection));
}

// 授予权限
$sql = "GRANT ALL PRIVILEGES ON $dbname.* TO '$new_user'@'localhost'";
if (mysqli_query($connection, $sql)) {
    echo "Granted all privileges on '$dbname' to user '$new_user'.<br>";
} else {
    die("Error granting privileges: " . mysqli_error($connection));
}

// 刷新权限表
$sql = "FLUSH PRIVILEGES";
if (mysqli_query($connection, $sql)) {
    echo "Privileges flushed successfully.<br>";
} else {
    die("Error flushing privileges: " . mysqli_error($connection));
}


// 用于重置
$sql = "DROP DATABASE IF EXISTS $dbname";
if (mysqli_query($connection, $sql)) {
    echo "Database '$dbname' dropped successfully.<br>";
}

// 创建数据库
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($connection, $sql)) {
    echo "Database '$dbname' created successfully or already exists.<br>";
} else {
    die("Error creating database: " . mysqli_error($connection));
}

// 使用数据库
$sql = "USE $dbname";
if (mysqli_query($connection, $sql)) {
    echo "Using database '$dbname'.<br>";
} else {
    die("Error selecting database: " . mysqli_error($connection));
}

// 创建表Users
$sql = "
CREATE TABLE IF NOT EXISTS Users (
    UserId INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('buyer', 'seller') NOT NULL
) AUTO_INCREMENT=20240001";
if (mysqli_query($connection, $sql)) {
    echo "Table 'users' created successfully.<br>";
} else {
    die("Error creating table 'users': " . mysqli_error($connection));
}


// 创建表Auctions
$sql = "
CREATE TABLE IF NOT EXISTS Auctions (
    AuctionId INT AUTO_INCREMENT PRIMARY KEY,
    ItemName VARCHAR(255) NOT NULL,
    Description TEXT NOT NULL,
    Category VARCHAR(255) NOT NULL,
    StartingPrice DECIMAL(10, 2) NOT NULL,
    ReservePrice DECIMAL(10, 2) NOT NULL,
    EndDate DATETIME NOT NULL,
    SellerId INT NOT NULL,
    FOREIGN KEY (SellerId) REFERENCES Users(UserId)
)";
if (mysqli_query($connection, $sql)) {
    echo "Table 'auctions' created successfully.<br>";
} else {
    die("Error creating table 'auctions': " . mysqli_error($connection));
}

// 创建表Bids
$sql = "
CREATE TABLE IF NOT EXISTS Bids (
    BidId INT AUTO_INCREMENT PRIMARY KEY,
    AuctionId INT NOT NULL,
    BuyerId INT NOT NULL,
    BidAmount DECIMAL(10, 2) NOT NULL,
    BidTime DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (AuctionId) REFERENCES Auctions(AuctionId),
    FOREIGN KEY (BuyerId) REFERENCES Users(UserId)
)";
if (mysqli_query($connection, $sql)) {
    echo "Table 'bids' created successfully.<br>";
} else {
    die("Error creating table 'bids': " . mysqli_error($connection));
}

// 创建表Notifications（optional）
$sql = "
CREATE TABLE IF NOT EXISTS Notifications (
    NotificationId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    AuctionId INT NOT NULL,
    Message TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    CreatedTime DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId) REFERENCES Users(UserId),
    FOREIGN KEY (AuctionId) REFERENCES Auctions(AuctionId)
)";
if (mysqli_query($connection, $sql)) {
    echo "Table 'notifications' created successfully.<br>";
} else {
    die("Error creating table 'notifications': " . mysqli_error($connection));
}

// 关闭连接
mysqli_close($connection);
echo "Database initialization complete.";
?>

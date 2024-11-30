<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.

// 和database.php一样的操作，后期需要更换为include database.php，在那个php文件中也需要删掉Drop语句
$servername = "localhost";
$new_user = "COMP0178"; 
$new_password = "DatabaseCW"; 
$dbname = "AuctionSystem"; 

$connection = mysqli_connect($servername, $new_user, $new_password, $dbname);

if (!$connection) {
    die("Error connecting to database: " . mysqli_connect_error());
}


// 检查是否通过 POST 提交
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 提取表单数据
    $accountType = trim($_POST['accountType'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $passwordConfirmation = trim($_POST['passwordConfirmation'] ?? '');

    // 数据验证
    $errors = [];

    // 验证角色
    if ($accountType !== 'buyer' && $accountType !== 'seller') {
        $errors[] = "Invalid account type selected. Please choose 'Buyer' or 'Seller'.";
    }

    // 验证邮箱
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    // 验证密码
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $passwordConfirmation) {
        $errors[] = "Passwords do not match.";
    }

    // 如果有错误，显示错误信息并终止
    if (!empty($errors)) {
        // foreach ($errors as $error) {
            // echo "<p style='color: red;'>$error</p>";

            // 修改
            // 把所有错误信息显示在一起
            $error_message = implode("\\n", $errors);
            echo "<script>alert('$error_message');</script>";
        // }
        echo "<script>window.location.href = 'register.php';</script>";
        // exit;
        // 不用exit防止页面停止
    }

    // 对密码进行哈希加密
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 插入用户数据到 Users 表
    $sql = "INSERT INTO Users (Email, Password, Role) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $email, $hashed_password, $accountType);

    // if (mysqli_stmt_execute($stmt)) {
    //     echo "<p style='color: green;'>Account successfully created! You can now <a href='browse.php'>log in</a>.</p>";
    // } else {
    //     if (mysqli_errno($connection) === 1062) { // 检测重复邮箱错误
    //         echo "<p style='color: red;'>This email is already registered. Please use a different email.</p>";
    //     } else {
    //         echo "<p style='color: red;'>Error creating account: " . mysqli_error($connection) . "</p>";
    //     }
    // }

    // 修改 
    // 增加异常捕获，解决无法检测重复邮箱的问题
    try {
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Account successfully created! Click OK to login.'); window.location.href = 'browse.php';</script>";
        } else {
            throw new Exception(mysqli_error($connection), mysqli_errno($connection));
        }
    } catch (Exception $e) {
        if ($e->getCode() === 1062) {
            echo "<script>alert('This email is already registered. Please use a different email.'); window.location.href = 'register.php';</script>";
        } else {
            echo "<p style='color: red;'>Error creating account: " . $e->getMessage() . "</p >";
        }
    }

    // 关闭语句
    mysqli_stmt_close($stmt);
}

// 关闭数据库连接
mysqli_close($connection);

?>
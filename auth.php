<?php
session_start();
// Koneksyon sa iyong database
$conn = new mysqli("localhost", "root", "", "byahero_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Hanapin ang user sa database
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // I-verify ang password
        if (password_verify($password, $user['password'])) {
            
            // I-save ang session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];

            // ETO ANG REDIRECT: Siguraduhin na ang 'home.php' ay yung dashboard na may ByaHERO Pay
            header("Location: home.php"); 
            exit();
        } else {
            header("Location: login.php?error=1");
            exit();
        }
    } else {
        header("Location: login.php?error=1");
        exit();
    }
}
$conn->close();
?>
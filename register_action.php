<?php
$conn = new mysqli("localhost", "root", "", "byahero_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // I-update ang SQL query para isama ang bagong fields
    $sql = "INSERT INTO users (fullname, email, phone, username, password) 
            VALUES ('$fullname', '$email', '$phone', '$username', '$password')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?registered=1");
    } else {
        // I-check kung nag-duplicate ang username o email
        echo "Error: Email or Username might already exist.";
    }
}
$conn->close();
?>
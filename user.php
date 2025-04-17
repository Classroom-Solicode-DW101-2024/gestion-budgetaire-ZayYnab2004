<?php

function addUser($user,$connection){

    $fullName = $user['nom'];
    $email = $user['email'];
    $password = $user['password'];

    $registerSql = "INSERT INTO users (nom, email, password) VALUES(:fullName,:email,:password)";
    $registerStmt = $connection-> prepare($registerSql);
    $registerStmt-> bindParam(':fullName',$fullName);
    $registerStmt-> bindParam(':email',$email);
    $registerStmt-> bindParam(':password',$password);
    $registerStmt-> execute();

    $_SESSION['user'] = $user;

    header('Location:index.php');



}

function checkUser($email,$connection){

    $isAvailableEmail = false;

    $email = htmlspecialchars($email);
    $checkSql = "SELECT * FROM `users` WHERE email = :email";
    $checkStmt = $connection-> prepare($checkSql);
    $checkStmt-> bindParam(':email',$email);
    $checkStmt->execute();
    $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if(!empty($checkResult)){

        $isAvailableEmail = true;

    }

    return $isAvailableEmail;

}
function loginUser($email, $password, $connection) {
    $sql = 'SELECT * FROM users WHERE email = :email';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        return true;
    }

    return false;
}

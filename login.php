<?php
session_start();
require 'config.php';
include 'user.php';

$errors = [];

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email)) {
        $errors['email'] = 'Email requis.';
    }

    if (empty($password)) {
        $errors['password'] = 'Mot de passe requis.';
    }

    if (empty($errors)) {
        if (loginUser($email, $password, $pdo)) {
            header('Location: index.php');
            exit;
        } else {
            $errors['login'] = 'Email ou mot de passe incorrect.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="form">
<h2>Login  Now</h2>
    <form method="post">
        <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"><br><br>
        <?php if (isset($errors['email'])): ?>
            <p style="color:red;"><?php echo $errors['email']; ?></p>
        <?php endif; ?>

        <input type="password" name="password" placeholder="Mot de passe"><br><br>
        <?php if (isset($errors['password'])): ?>
            <p style="color:red;"><?php echo $errors['password']; ?></p>
        <?php endif; ?>

        <?php if (isset($errors['login'])): ?>
            <p style="color:red;"><?php echo $errors['login']; ?></p>
        <?php endif; ?>

        <button type="submit" name="submit">Se connecter</button>
    </form>
        </dive>
</body>
</html>

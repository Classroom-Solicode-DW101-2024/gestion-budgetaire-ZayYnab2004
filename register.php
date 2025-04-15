<?php
require 'config.php';
$errors = [];

if (isset($_POST['submit'])) {
    $nom = ($_POST['nom']);
    $email = ($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($nom)) $errors[] = "Nom requis.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if (empty($password)) $errors[] = "Mot de passe requis.";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas.";

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->rowCount() == 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (nom, email, password) VALUES (:nom, :email, :password)");
            $insert->bindParam(':nom', $nom);
            $insert->bindParam(':email', $email);
            $insert->bindParam(':password', $hashed_password);
            $insert->execute();
            header('Location: login.php');
            exit;
        } else {
            $errors[] = "Email déjà utilisé.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Inscription</title></head>
<body>

<form method="post" action="">
    <label for="">Nom</label>
    <input type="text" name="nom" placeholder="Nom"><br><br>
    <label for="">Email</label>
    <input type="email" name="email" placeholder="Email"><br><br>
    <label for="">Password</label>
    <input type="password" name="password" placeholder="Mot de passe"><br><br>
    <label for="">confirm_Password</label>
    <input type="password" name="confirm_password" placeholder="Confirmez le mot de passe"><br>
    <button type="submit" name="submit">S'inscrire</button>
</form>

</html>

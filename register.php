<?php

require 'config.php';
include 'user.php';

$errors = [];


if(isset($_POST['regBtn'])){

    $_fullName = $_POST['fullName'];
    $_email = $_POST['email'];
    $_password = $_POST['password'];
    $_confirmPassword = $_POST['conPassword'];


    if(empty($_fullName)){

        $errors['fullName'] = 'Full name is required.';

    }

    if(empty($_email)){

        $errors['email'] = 'Email address is required.';

    }

    if(empty($_password)){

        $errors['password'] = 'Password is required.';

    }

    if(empty($_confirmPassword)){

        $errors['ConPassword'] = 'Password confirmation is required.';

    }

    if (!empty($_password) && !empty($_confirmPassword) && $_password !== $_confirmPassword) {
        $errors['passwordMatch'] = 'Password and confirmation do not match.';
    }





    if(empty($errors)){

        $user = [
            'nom' =>htmlspecialchars($_fullName) ,
            'email' =>htmlspecialchars($_email) ,
            'password' => password_hash($_password,PASSWORD_DEFAULT),
        ];

        addUser($user,$pdo);


    }

}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Register.css">
    <title>Register</title>
</head>
<body>

    <div class="formContainer">
        <h2>Register Now</h2>
        <form method="post">

            <input type="text" placeholder="Full Name" name="fullName" id="registerFullName" value="<?php echo isset($_fullName) ? htmlspecialchars($_fullName) : ''; ?>">
            <?php if (isset($errors['fullName'])): ?>
                <p><?php echo $errors['fullName']; ?></p>
            <?php endif; ?>
            <input type="email" placeholder="Email" name="email" id="registerEmail" value="<?php echo isset($_email) ? htmlspecialchars($_email) : ''; ?>">
            <?php if (isset($errors['email'])): ?>
                <p><?php echo $errors['email']; ?></p>
            <?php endif; ?>
            <input type="password" name="password" id="registerPassword" placeholder="Password" >
            <?php if (isset($errors['password'])): ?>
                <p><?php echo $errors['password']; ?></p>
            <?php endif; ?>
            <input type="password" name="conPassword" id="registerConfPassword" placeholder="Confirm Password">
            <?php if (isset($errors['ConPassword'])): ?>
                <p><?php echo $errors['ConPassword']; ?></p>
            <?php endif; ?>
            <?php if (isset($errors['passwordMatch'])): ?>
                <p><?php echo $errors['passwordMatch']; ?></p>
            <?php endif; ?>
            <button name="regBtn">Register</button>
        </form>
        <a href="login.php">Have an Acoount ? Login</a>
    </div>
    
</body>
</html>

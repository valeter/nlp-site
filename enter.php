<head>
	<link rel="stylesheet" type="text/css" href="login_styles.css" media="screen" />
</head>

<form id="llf" class="form-1" method="post">
    <p class="field">
        <input type="text" name="login" placeholder="Login">
    </p>
    <p class="field">
        <input type="password" name="password" placeholder="Password">
    </p>      
    <p class="submit">
        <button type="submit" name='submit' form="llf" formmethod="post" value="Log in">Sign in</button>
    </p>
</form>

<?php
session_start();

if($_SESSION['admin']){
    header("Location: index.php");
    exit;
}

$admin = 'expert';
$pass = '5a0026b8c64f24e8b23b588eb03da344';

$user = 'admin';
$userpass = 'admin';

if($_POST['submit']){
    if(($admin == $_POST['login'] AND $pass == md5($_POST['password'])) OR
        ($user == $_POST['login'] AND $userpass == $_POST['password'])) {
        $_SESSION['admin'] = $admin;
        header("Location: index.php");
        exit;
    } else echo '<p style="width: 100%; margin: 30px auto 30px; position: relative; text-align: center; color: red;">Ошибка авторизации!</p>';
}
?>
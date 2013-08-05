<head>
	<link rel="stylesheet" type="text/css" href="login_styles.css" media="screen" />
</head>

<?php
session_start();

if($_SESSION['admin']){
	header("Location: index.php");
	exit;
}

$admin = 'expert';
$pass = '5a0026b8c64f24e8b23b588eb03da344';

if($_POST['submit']){
	if($admin == $_POST['login'] AND $pass == md5($_POST['password'])){
		$_SESSION['admin'] = $admin;
		header("Location: index.php");
		exit;
	}else echo '<p>Логин или пароль неверны!</p>';
}
?>

<form id="llf" class="form-1" method="post">
    <p class="field">
        <input type="text" name="login" placeholder="Логин">
    </p>
    <p class="field">
        <input type="password" name="password" placeholder="Пароль">
    </p>      
    <p class="submit">
        <button type="submit" name='submit' form="llf" formmethod="post" value="Log in">Log in</button>
    </p>
</form>


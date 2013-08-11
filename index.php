<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Разработка комплекса технологий и коммерческих продуктов нового поколения </title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
 A {
  text-decoration: none; /* Убирает подчеркивание для ссылок */
  color: #106BB5;
 } 
 A:hover { 
  text-decoration: underline; /* Добавляем подчеркивание при наведении курсора на ссылку */
  color: red; /* Ссылка красного цвета */
 } 
</style>

</head>

<body class="oneColLiqCtrHdr">

<?php
require "auth.php";
?>

<div id="top_wrap">

<div id="header">
  <img src="images/cloud.png" id="cloud"/>

  <h1>Разработка комплекса технологий и коммерческих продуктов нового поколения <br />
  для обработки естественного языка на основе облачных вычислений</h1>
  <h2>По заказу Фонда содействия развитию малых форм предприятий <br />
  в научно-технической сфере. Контракт №10151р/17593 от 28.04.2012.</h2>

  <p style="position: absolute; margin: -20px 70px; color: blue; font-size: 14px">
    <a href="auth.php?do=logout">Выход</a>
  </p>
  <!-- end #header --></div>
</div>

<div id="container">

<div class="inner_block1">

<div class="block_heading">Приложения для бизнеса</div>
<div class="inner_block_content">

<a href="#" id="block_mail">Система документооборота 
на основе корпоративного 
почтового сервера</a>
<a href="#" id="block_zakupki">Система конкурентной разведки, 
анализирующая данные 
о конкурентах по сайту госзакупок</a>
</div>

</div>

<div class="inner_block">
  <div class="block_heading">Приложения для СМИ</div>
  <div class="inner_block_content">

    <a href="#" id="block_smi">Система контроля за контентом 
    электронных СМИ в соответствии 
    с федеральным законом №436 
    «О защите детей от информации, 
    причиняющей вред их здоровью 
    и развитию».</a>

  </div>
</div>

<div class="inner_block1">
  <div class="block_heading">Приложения для науки</div>
  <div class="inner_block_content">
    <a href="classification/file-upload/index.html" id="block_autoclass">Система автоматической 
    классификации научных текстов 
    в соответствии с классификаторами 
    УДК/ГРНТИ.</a>
    <a href="search/index.php" id="block_2lang">Система двуязычного (русско-английский, англо-русский) поиска в массиве научных публикаций с разрешением многозначности запросов</a>
  </div>
</div>

<div class="inner_block4">
  <div class="block_heading">Статистика</div>
  <div class="inner_block_content">
    <p>Amazon</p>
    <div class="stats_block">stats</div>
    <p>Hadoop</p>
    <div class="stats_block">stats</div>
    <p>Биллинг</p>
    <div class="stats_block" id="billing"><?php include 'billing.php'; ?></div>
  </div>
</div>


<!-- end #container -->
</div>
</body>
</html>

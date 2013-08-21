<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta charset="utf-8">
	<title>Классификатор УДК/ГРНТИ</title>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript">
		var SITE = SITE || {};
		 
		SITE.fileInputs = function() {
		  var $this = $(this),
		      $val = $this.val()
		      valArray = $val.split('\\'),
		      newVal = valArray[valArray.length-1],
		      $button = $this.siblings('.button'),
		      $fakeFile = $this.siblings('.file-holder');
		  if(newVal !== '') {
		    $button.text('Статья выбрана');
		    if($fakeFile.length === 0) {
		      $button.after('<span class="file-holder">' + newVal + '</span>');
		    } else {
		      $fakeFile.text(newVal);
		    }
		  }
		};
		 
		$(document).ready(function() {
		  $('.file-wrapper input[type=file]').bind('change focus click', SITE.fileInputs);
		});
	</script>

	<style type="text/css">
		.file-wrapper {
		    position: relative;
		    display: inline-block;
		    overflow: hidden;
		    cursor: pointer;
		}
		.file-wrapper input {
		    position: absolute;
		    top: 0;
		    right: 0;
		    filter: alpha(opacity=1);
		    opacity: 0.01;
		    -moz-opacity: 0.01;
		    cursor: pointer;
		}
		.file-wrapper .button, input[type='submit'] {
		    color: #fff;
		    background: #117300;
		    padding: 4px 18px;
		    margin-right: 5px;  
		    border: 1px;
		    border-radius: 5px;
		    -moz-border-radius: 5px;
		    -webkit-border-radius: 5px;
		    display: inline-block;
		    font-weight: bold;
		    cursor: pointer;
		}
		.file-holder{
		    color: #000;
		}

		.main {
			text-align: center;
			margin-top: 10px;
		}

		.finput {
			margin-top: 20px;
		}

		.separator {
			width: 90%;
			height: 1px;
			background-color: black;
			margin: auto;
			margin-top: 15px;
			margin-bottom: 15px;
		}

		.header {
			display: block-line;
			margin-top: 1em;
			position: relative;
		}

		.lefth {
			left: 10%;
			position: relative;
		}

		.righth {
			color: red;
			margin-top: -1em;
			float:right;
			right: 10%;
			position: relative;
		}

		.footer {
			right: 10%;
			float: right;
			position: relative;
		}

		A:link {text-decoration: none; color: #117300;}
		A:visited {text-decoration: none; color: #117300;}
		A:active {text-decoration: none; color: #117300;}
		A:hover {text-decoration: underline overline; color: black;}
	</style>
</head>
<body>
	<div style="text-align: center; margin-bottom: 3em; margin-top:2em;"><b>Система автоматической классификации научных текстов в соответствии с классификаторами УДК/ГРНТИ</b></div>
	<div class='header'>
		<div class='lefth'>
			<a href="../index.php">На главную</a>
			<a href="help.pdf" style="margin-left: 1em;">Справка</a>
		</div>
		<div class='righth'>
			ВАЖНО! Останавливайте кластер после завершения работы с программой!
		</div>
	</div>
	<div class='separator'></div>
	<div class='main'>
	<?php

		$cluster_script_folder = '/var/www/classification/cluster';
		$run_cluster_form = "<form method='post'>
								<div>Запуск кластера (может длиться от 3 до 5 минут)</div>
								<p><input type='submit' name='launch2' value='2 узла'/>
								<input type='submit' name='launch5' value='5 узлов'/>
								<input type='submit' name='launch10' value='10 узлов'/></p>
							</form>";
		$stop_cluster_form = "<form method='post'>
								<input type='submit' name='stop_cluster' value='Остановить кластер'/>
							</form>";
		$load_files_form = "<form class='finput' enctype='multipart/form-data' method='post'>
								<div class='file-wrapper'>
								    <input name='userfile' type='file' />
								    <span class='button'>Выберите статью</span>
								</div>
								<input name='send_file' type='submit' value='Отправить' />
							</form>
							<div>Время обработки одного файла может составлять от 1 до 10 минут</div>";

		session_start();

		if (!isset($_SESSION['cluster'])) {
			echo $run_cluster_form;
		} else {
			$check_status_command = $cluster_script_folder.'/cluster_status.py '.trim($_SESSION['cluster']);
			$output = array();
			exec($check_status_command, $output);
			// Скрипт по ID кластера выводит 2 строки:
			// 1. true или false, в зависимости от того - готов ли кластер к работе
			// 2. Статус кластера (=none, если 1-я строка = false)
			$cluster_alive = false;
			$cluster_status = "Ошибка определения статуса";
			if (trim($output[0]) == "true") {
				$cluster_alive = true;
				$cluster_status = trim($output[1]);
			}

			if ($cluster_alive) {
				echo "<div>ID кластера: " . $_SESSION['cluster'] . "</div>";
				echo "<div>Количество узлов: " . $_SESSION['numnodes'] . "</div>";
				echo "<div>Статус кластера: " . $cluster_status ."</div>";

				if ($cluster_status == "STARTING" || $cluster_status == "BOOTSTRAPPING") {
					header('Refresh: 5; URL=index.php');
					echo "<div>Ожидание запуска кластера...</div>";
				} else {
					echo $stop_cluster_form;

					if (isset($_SESSION['files'])) {
						$files = $_SESSION['files'];
						$udk = $_SESSION['udk'];
						$grnti = $_SESSION['grnti'];
						for ($i = 0; $i < count($files); $i += 1) {	
	            			$file = $files[$i];
	            			$file_udk = $udk[$i];
	            			$file_grnti = $grnti[$i];
	            			echo "<div><br></div>";
	            			echo "<div>" . $file ."</div>";
	            			echo "<div>" . $file_udk . "</div>";
	            			echo "<div>" . $file_grnti . "</div>";
	            		}
					}

					if (isset($_SESSION['work_at_progress'])) {
						echo "<div>Дождитесь рузультатов  обработки...</div>";
						header('Refresh: 5; URL=index.php');
						echo "<div>Ожидание запуска кластера...</div>";
					} else {
						echo $load_files_form;
					}
				}
			} else {
				echo "<div>Кластер не готов к работе</div>";
				echo $run_cluster_form;
			}
			if (isset($_SESSION['error'])) {
				echo $_SESSION['error'];
				unset($_SESSION['error']);
			}
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (isset($_POST['stop_cluster'])) {
				$check_status_command = $cluster_script_folder.'/cluster_status.py '.trim($_SESSION['cluster']);
				$output = array();
				exec($check_status_command, $output);
				$cluster_alive = false;
				$cluster_status = "Ошибка определения статуса";
				if (trim($output[0]) == "true") {
					$cluster_alive = true;
					$cluster_status = trim($output[1]);
				}

				if (!$cluster_alive) {
					header ("Location: index.php");
				}

				echo "<div>Остановка кластера...</div>";
				$stop_cluster_command = $cluster_script_folder.'/terminate_cluster.py '.trim($_SESSION['cluster']);
				$output = array();
				exec($stop_cluster_command, $output);
				// Скрипт по ID кластера выполняет его остановку и выводит true/false в случае успеха/неудачи
				if (trim($output[0]) == "true") {
					unset($_SESSION['cluster']);
					unset($_SESSION['numnodes']);
					unset($_SESSION['files']);
					unset($_SESSION['udk']);
					unset($_SESSION['grnti']);
					unset($_SESSION['error']);
					echo "<div>Кластер успешно остановлен</div>";
				} else {
					echo "<div>Ошибка при остановке кластера</div>";
				}
				
				header ("Location: index.php");
			} 
			if (isset($_POST['send_file'])) {
				$check_status_command = $cluster_script_folder.'/cluster_status.py '.trim($_SESSION['cluster']);
				$output = array();
				exec($check_status_command, $output);
				$cluster_alive = false;
				$cluster_status = "Ошибка определения статуса";
				if (trim($output[0]) == "true") {
					$cluster_alive = true;
					$cluster_status = trim($output[1]);
				}

				if (!$cluster_alive) {
					header ("Location: index.php");
				}

				$real_name = $_FILES['userfile']['name'];
				$uploadfile = $_FILES['userfile']['tmp_name'];
				$_SESSION['work_at_progress'] = true;
				// Скрипт обработки файла. Добавляет шаги к кластеру и выводит две строки: УДК и ГРНТИ
				if (!isset($_SESSION['file_id'])) {
					$_SESSION['file_id'] = 1;
				} else {
					$_SESSION['file_id'] = $_SESSION['file_id'] + 1;
				}
				$process_file_command = $cluster_script_folder .'/process_file.py ' . trim($_SESSION['cluster']) . " " . $uploadfile . " " . $_SESSION['file_id'] . " " . $_SESSION['numnodes'];
				$output = array();
				exec($process_file_command, $output);
				$errors = array("error i"=>"Ошибка загрузки файла в Amazon S3", 
								"error c"=>"Ошибка работы кластера", 
								"error w"=>"Ошибка БД",
								"error d"=>"Ошибка удаления файлов из S3",
								"error e"=>"Ошибка модуля IE");
				if (array_search(trim($output[0]), $errors) != false) {
					$_SESSION['error'] = $errors[trim($output[0])];
				} else {
					if (!isset($_SESSION['files'])) {
						$_SESSION['files'] = array();
						$_SESSION['udk'] = array();
						$_SESSION['grnti'] = array();
					}
					array_push($_SESSION['files'], $real_name);
					array_push($_SESSION['udk'], $output[0]);
					array_push($_SESSION['grnti'], $output[1]);
				}
				unset($_SESSION['work_at_progress']);
			} 

			$check_status_command = $cluster_script_folder.'/cluster_status.py '.trim($_SESSION['cluster']);
			$output = array();
			exec($check_status_command, $output);
			$cluster_alive = false;
			$cluster_status = "Ошибка определения статуса";
			if (trim($output[0]) == "true") {
				$cluster_alive = true;
				$cluster_status = trim($output[1]);
			}

			if ($cluster_alive) {
				header ("Location: index.php");
			}

			$run_cluster_command = $cluster_script_folder.'/run_cluster.py ';
			$output = array();
			$need_to_run = false;
			if (isset($_POST['launch2'])) {
				$_SESSION['numnodes'] = '2';
				$need_to_run = true;
			} elseif (isset($_POST['launch5'])) {
				$_SESSION['numnodes'] = '5';
				$need_to_run = true;
			} elseif (isset($_POST['launch10'])) {
				$_SESSION['numnodes'] = '10';
				$need_to_run = true;
			}
			if ($need_to_run == true) {
				echo "<div>Запуск кластера. Узлов: " . $_SESSION['numnodes'] . "</div>";
				$run_cluster_command = $run_cluster_command . $_SESSION['numnodes'];
				$output = array();
				exec($run_cluster_command, $output);
				$cluster_id = trim($output[0]);
				if ($cluster_id != "none") {
					$_SESSION['cluster'] = $cluster_id;
				} else {
					echo "<div>Ошибка запуска кластера</div>";
				}
				header ("Location: index.php");
			}
        }
	?>
	</div>
	<div class='separator'></div>
	<div class='footer'>Copyright, 2013, NLP@Cloud</div>
</body>
</html>
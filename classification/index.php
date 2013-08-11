<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta charset="utf-8">
	<title>Классификатор УДК/ГРНТИ</title>
</head>
<body>
	<?php
		$cluster_script_folder = '/var/www/classification/cluster';
		$run_cluster_form = "<form method='post'>
								<div>Запуск кластера</div>
								<p><input type='submit' name='launch2' value='2 узла'/>
								<input type='submit' name='launch5' value='5 узлов'/>
								<input type='submit' name='launch10' value='10 узлов'/></p>
							</form>";
		$stop_cluster_form = "<form method='post'>
								<input type='submit' name='stop_cluster' value='Остановить кластер'/>
							</form>";
		$load_files_form = "<form enctype='multipart/form-data' method='post'>
							    Статья: <input name='userfile' type='file' />
							    <input name='send_file' type='submit' value='Отправить' />
							</form>";
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

				if ($cluster_status == "STARTING") {
					header('Refresh: 5; URL=index.php');
					echo "<div>Ожидание запуска кластера...</div>";
					exit;
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
	            			echo "<div>" . $file . "  " . $file_udk . "  " . $file_grnti . "</div>";
	            		}
					}

					if (isset($_SESSION['work_at_progress'])) {
						echo "<div>Дождитесь рузультатов  обработки...</div>";
						header('Refresh: 5; URL=index.php');
						echo "<div>Ожидание запуска кластера...</div>";
						exit;
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
				$process_file_command = $cluster_script_folder .'/process_file.py ' . trim($_SESSION['cluster']) . " " . $uploadfilename;
				$output = array();
				exec($process_file_command, $output);
				unset($_SESSION['work_at_progress']);
				$errors = array("error i"=>"Ошибка загрузки файла в Amazon S3", 
								"error c"=>"Ошибка работы кластера", 
								"error w"=>"Ошибка БД",
								"error d"=>"Ошибка удаления файлов из S3");
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
					array_push($_SESSION['grnti'], $output[0]);
				}
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
				echo "<div>Запуск кластера. Узлов: " . $_SESSION['numnodes' . "</div>"];
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
</body>
</html>
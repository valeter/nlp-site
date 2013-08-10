<?php
//<div align="center" >
echo <<<END
<div class="query_string">
<a href="index.php">
<img src="picture/mabi87.png" width="25%" alt="nlp-cloud"/>
</a><br/>
Система двуязычного (русско-английский, англо-русский) поиска <br />
в массиве научных публикаций с разрешением многозначности запросов <br />
END;
if (isset($_GET['q'])) {
    $q1 = $_GET['q'];
} else {
    $q1 = "";
}
echo <<<END
<form action="searchlog.php" method="get" name="searchsh">
<p>
 <input type="text" name="q" size="64" value="$q1"/>
 <input type="submit" value="Поиск"/> 
 <input type="reset" value="Сброс"/> </p>
END;


echo <<<END
<table width="100%" border="0" cellspacing="1" cellpadding="4">
 <tr id="focus_head">
    <td colspan="4" align="left" >Фокусировка запроса</td>
  </tr>
</table> 
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr id="focus_body">
		<td class="focus_element">Значение</td>
        <td class="focus_element">Словосочетание</td>
		<td class="focus_element">Тема</td>
		<td class="focus_element" align="left">
        <input type="checkbox" name="l" value="l" checked="checked" />Результаты на русском <br/>
        <input type="checkbox" name="e" value="e" checked="checked" />Результаты на английском
END;

echo <<<END
        </td>
	</tr>
</table>   

END;
  
echo '</form>';
echo "</div>";
?>

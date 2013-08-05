<?php
//<div align="center" >
echo <<<END
<div class="query_string">
<a href="index.php">
<img src="picture/mabi87.png" width="40%" alt="nlp-cloud"/>
</a><br/>
Система двуязычного (русско-английский) поиска <br />
в массиве научных публикаций с разрешением многозначности запросов <br />
END;
if (isset($_GET['q'])) {
    $q1 = $_GET['q'];
} else {
    $q1 = "";
}
echo <<<END
<form action="searchlog.php" method="get" name="searchsh">
 <p> <input type="text" name="q" size="64" value="$q1"/>
 <input type="submit" value="Поиск"/> 
 <input type="reset" value="Сброс"/> <br>
END;


echo <<<END
<table width="100%" border="0" cellspacing="1" cellpadding="4">
  <tr align="left" width="100%" bgcolor="#999999">
    <td colspan="3" width="25%" align="left" >Фокусировка запроса</td>
  </tr>
  <tr align="left" width="100%">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr align="left" width="100%">
        <td colspan="3" width="25%">Значение</td>
        <td colspan="3" width="25%">Словосочетание</td>
        <td colspan="3" width="25%">Тема</td>
        <td colspan="3" width="25%" align="left">
        <input type="checkbox" name="l" value="l" checked="checked" />Результаты на русском <br/>
        <input type="checkbox" name="e" value="e" checked="checked" />Результаты на английском'
END;

echo <<<END
        </td>
      </tr>
    </table>   
  </tr>
</table>
END;
  
echo '</p>';
echo '</form>';
echo "</div>";
?>

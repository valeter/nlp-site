<?php
require_once( 'rtools.php' );
require_once( "tools.php" );
require_once( '../Apache/Solr/Service2.php' );

if (isset($_GET['q'])) {
    $q1 = $_GET['q'];
} else {
    $q1 = "";
}
$state=0;
if (isset($_GET['m'])) {
	$m1=$_GET['m'];
	if ($m1!='нет'){
		$state+=4;
	}
}
if (isset($_GET['c'])) {
	$c1=$_GET['c'];
	if ($c1!='нет'){
		$state+=2;
	}
}
if (isset($_GET['t'])) {
	$t1=$_GET['t'];
	if ($t1!='нет'){
		$state+=1;
	}
}
switch ($state){
	case 0:
		$meanings=suggest_by_meaning($q1);
		$flag=suggest_from_dictionary($q1,$collocations,$themes);
		if ($flag===false){
			$collocations=array();
			$themes=array();
		}
	break;
	case 1:
		$themes=array();
		$themes[]=$t1;
		$flag=suggest_by_using_theme($q1,$t1,$meanings,$collocations);
		if ($flag===false){
			$collocations=array();
			$meanings=array();
		}
	break;
	case 2:
		$collocations=array();
		$collocations[]=$c1;
		$flag=suggest_by_using_collocation($q1,$c1,$meanings,$themes);
		if ($flag===false){
			$meanings=array();
			$themes=array();
		}
	break;
	case 3:
		$themes=array();
		$themes[]=$t1;
		$collocations=array();
		$collocations[]=$c1;
		$flag=suggest_meaning($q1,$c1,$t1,$meanings);
		if ($flag===false){
			$meanings=array();
		}
	break;
	case 4:
		$meanings=array();
		$meanings[]=$m1;
		$flag=suggest_from_dictionary($m1,$collocations,$themes);
		if ($flag===false){
			$collocations=array();
			$themes=array();
		}
	break;
	case 5:
		$themes=array();
		$themes[]=$t1;
		$meanings=array();
		$meanings[]=$m1;
		$flag=suggest_by_using_theme1($m1,$t1,$collocations);
		if ($flag===false){
			$collocations=array();
		}
	break;
	case 6:	
		$collocations=array();
		$collocations[]=$c1;
		$meanings=array();
		$meanings[]=$m1;
		$flag=suggest_by_using_collocation1($m1,$c1,$themes);
		if ($flag===false){
			$themes=array();
		}
	break;
	case 7:
		$themes=array();
		$themes[]=$t1;
		$meanings=array();
		$meanings[]=$m1;
		$collocations=array();
		$collocations[]=$c1;
	break;
}


//$collocations=$meanings;
//$themes=$meanings;
//print_r($meanings);

echo <<<END
<div class="query_string">
<a href="index.php">
<img src="picture/mabi87.png" width="40%" alt="nlp-cloud"/>
</a><br/>
Система двуязычного (русско-английский, англо-русский) поиска <br />
в массиве научных публикаций с разрешением многозначности запросов <br />
END;
echo <<<END
<form action="searchlog.php" method="get" name="searchsh">
<p>
 <input type="text" name="q" size="64" value="$q1" id="quer"/>
 <input type="submit" value="Поиск"/> 
 <input type="button" value="Сброс" onclick="window.location='index.php'"/>
 <a href="help.pdf">?</a>
 </p>
END;


echo <<<END
<table width="100%" border="0" cellspacing="1" cellpadding="4">
  <tr id="focus_head">
    <td align="left" colspan="4">Фокусировка запроса</td>
  </tr>
</table> 
<table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr id="focus_body">
END;
# 
#display meanings
#
echo '<td class="focus_element">'."\n";
echo '<fieldset>'."\n";
echo '   <legend>Значение</legend>'."\n";
echo '<select size="7" name="m" class="soption" id="mean" onchange="this.form.submit();">'."\n";
$vbp[0]=0;
if (isset($_GET['m'])) {
	$m1=$_GET['m'];
	foreach ($meanings as $meaning) {
		$vbp[0]=($vbp[0]||($meaning==$m1));
	}
}

if ($vbp[0]==0){
	echo '   <option selected="selected" value="нет">нет</option>'."\n";
	foreach ($meanings as $meaning) {
		echo '   <option value="'.$meaning.'">'.$meaning.'</option>'."\n";
	}
}else{
	echo '   <option value="нет">нет</option>'."\n";
	foreach ($meanings as $meaning) {
		if ($meaning==$m1){
			echo '   <option selected="selected" value="'.$meaning.'">'.$meaning.'</option>'."\n";
		}else{
			echo '   <option value="'.$meaning.'">'.$meaning.'</option>'."\n";
		}
	}
}


echo '</select>'."\n";
echo '</fieldset>'."\n";
echo '</td>'."\n";
#
#
#

# 
#display collocations
#
echo '<td class="focus_element">'."\n";
echo '<fieldset>'."\n";
echo '   <legend>Словосочетание</legend>'."\n";
echo '<select size="7" name="c" class="soption" id="colloc" onchange="this.form.submit();">'."\n";
$vbp[1]=0;
if (isset($_GET['c'])) {
	$c1=$_GET['c'];
	foreach ($collocations as $collocation) {
		$vbp[1]=($vbp[1]||($collocation==$c1));
	}
}
if ($vbp[1]==0){
	echo '   <option selected="selected" value="нет">нет</option>'."\n";
	foreach ($collocations as $collocation) {
		echo '   <option value="'.$collocation.'">'.$collocation.'</option>'."\n";
	}
}else{
	echo '   <option value="нет">нет</option>'."\n";
	foreach ($collocations as $collocation) {
		if ($collocation==$c1){
			echo '   <option selected="selected" value="'.$collocation.'">'.$collocation.'</option>'."\n";
		}else{
			echo '   <option value="'.$collocation.'">'.$collocation.'</option>'."\n";
		}
	}
}


echo "</select>\n";
echo "</fieldset>\n";
echo "</td>\n";
#
#
#

# 
#display themes
#
echo '<td class="focus_element">'."\n";
echo '<fieldset>'."\n";
echo '   <legend>Тема</legend>'."\n";
echo '<select size="7" name="t" class="soption" id="theme" onchange="this.form.submit();">'."\n";
$vbp[2]=0;
if (isset($_GET['t'])) {
	$t1=$_GET['t'];
	foreach ($themes as $theme) {
		$vbp[2]=($vbp[2]||($theme==$t1));
	}
}
if ($vbp[2]==0){
	echo '   <option selected="selected" value="нет">нет</option>'."\n";
	foreach ($themes as $theme) {
		echo '   <option value="'.$theme.'">'.$theme.'</option>'."\n";
	}
}else{
	echo '   <option value="нет">нет</option>'."\n";
	foreach ($themes as $theme) {
		if ($theme==$t1){
			echo '   <option selected="selected" value="'.$theme.'">'.$theme.'</option>'."\n";
		}else{
			echo '   <option value="'.$theme.'">'.$theme.'</option>'."\n";
		}
	}
}


echo "</select>\n";
echo "</fieldset>\n";
echo "</td>\n";
#
#
#

echo '<td class="focus_element" align="left">';

if (isset($_GET['l'])) {
	if ($_GET['l']=='l'){
		echo '<input type="checkbox" name="l" value="l" checked="checked" />Результаты на русском <br/>'."\n";
	}else{
		echo '<input type="checkbox" name="l" value="l" />Результаты на русском <br/>'."\n";
	}
} else {
	echo '<input type="checkbox" name="l" value="l" />Результаты на русском <br/>'."\n";
}
if (isset($_GET['e'])) {
	if ($_GET['e']=='e'){
		echo '<input type="checkbox" name="e" value="e" checked="checked" />Результаты на английском'."\n";
	}else{
		echo '<input type="checkbox" name="e" value="e" />Результаты на английском'."\n";
	}
} else {
	echo '<input type="checkbox" name="e" value="e" />Результаты на английском'."\n";
}  

echo <<<END
        </td>
      </tr>
    </table>   
END;

echo '</form>'."\n";
echo "</div>"."\n";

echo <<<END
<script type="text/javascript">
var syncList1 = new syncList;
syncList1.selectList = new Array( 'quer','mean', 'colloc', 'theme');
syncList1.dataList = {
'mean':'0',
'colloc':'1',
'theme':'2'
};
syncList1.sync("quer");
</script>
END;
?>

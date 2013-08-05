<?php
function suggest_by_meaning($query)
{
	$vbp=0;	
	$num=0;
	$wnsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection4' );
	$lang=detectlanguage($query);
	$word=$query;
	if ($lang=="e"){
		$wquery="synset_label:(".$word.") OR sense_label:(".$word.") OR lf_label:(".$word.")";
	} else {
		$wquery="synset_label_RUS:(".$word.") OR sense_label_RUS:(".$word.") OR lf_label_RUS:(".$word.")";
	}
	$response = $wnsolr->search( $wquery, 0, 10);
	if ($response->response->numFound>0){
		foreach ( $response->response->docs as $doc ) { 
			if ($lang=="e"){
				$wlist[$num]=$doc->synset_label_RUS;
				$num++;
				$wlist[$num]=$doc->sense_label_RUS;
				$num++;
				$wlist[$num]=$doc->lf_label_RUS;
				$num++;
			}else{
				$wlist[$num]=$doc->synset_label;
				$num++;
				$wlist[$num]=$doc->sense_label;
				$num++;
				$wlist[$num]=$doc->lf_label;
				$num++;
			}
		}
	}else{
		$wlist="";
		$vbp=1;
	}
	if ($vbp==1) {
		return "error38";
	} else {
		$num=0;
		$w2list=array_unique($wlist);
		//print_r($w2list);
		foreach ( $w2list as $word ) { 
			if ($lang=="r"){
				$wquery="synset_label:(".$word.") OR sense_label:(".$word.") OR lf_label:(".$word.")";
			} else {
				$wquery="synset_label_RUS:(".$word.") OR sense_label_RUS:(".$word.") OR lf_label_RUS:(".$word.")";
			}
			$response = $wnsolr->search( $wquery, 0, 10);
			foreach ( $response->response->docs as $doc ) { 
				if ($lang=="r"){
					$rlist[$num]=$doc->synset_label_RUS;
					$num++;
					$rlist[$num]=$doc->sense_label_RUS;
					$num++;
					$rlist[$num]=$doc->lf_label_RUS;
					$num++;
				}else{
					$rlist[$num]=$doc->synset_label;
					$num++;
					$rlist[$num]=$doc->sense_label;
					$num++;
					$rlist[$num]=$doc->lf_label;
					$num++;
				}
			}		
		}
	}
	if ($vbp==1) {
		return "error68";
	} else {
		return array_unique($rlist);
	}
}


//<select size="7" name="m">

//<div align="center" >
require_once( '../Apache/Solr/Service2.php' );

if (isset($_GET['q'])) {
    $q1 = $_GET['q'];
} else {
    $q1 = "";
}
$meanings=suggest_by_meaning($q1);
$collocations=$meanings;
//print_r($meanings);

echo <<<END
<div class="query_string">
<a href="index.php">
<img src="picture/mabi87.png" width="40%" alt="nlp-cloud"/>
</a><br/>
Система двуязычного (русско-английский) поиска <br />
в массиве научных публикаций с разрешением многозначности запросов <br />
END;
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
#echo '<select size="7" name="m" class="soption" id="mean" onchange="var country=document.getElementById("colloc"); alert(country.options[country.selectedIndex].value); country.selectedIndex = 0; this.form.submit()">'."\n";
echo '<select size="7" name="m" class="soption" id="mean">'."\n";
$vbp=0;
if (isset($_GET['m'])) {
	$m1=$_GET['m'];
	foreach ($meanings as $meaning) {
		$vbp=($vbp||($meaning==$m1));
	}
}

if ($vbp==0){
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
# onchange="this.form.submit()"
echo '<td class="focus_element">'."\n";
echo '<fieldset>'."\n";
echo '   <legend>Словосочетание</legend>'."\n";
echo '<select size="7" name="c" class="soption" id="colloc" disabled="disabled">'."\n";
$vbp=0;
if (isset($_GET['c'])) {
	$c1=$_GET['c'];
	foreach ($collocations as $collocation) {
		$vbp=($vbp||($collocation=$c1));
	}
}
if ($vbp==0){
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


echo <<<END
        <td class="focus_element">Тема</td>
        <td class="focus_element" align="left">
END;

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
// Создаем новый объект связанных списков
var syncList1 = new syncList;
syncList1.dataList = {
'mean':'0',
'colloc':'1'
};
// Включаем синхронизацию связанных списков
syncList1.sync("mean","colloc");
</script>
END;
?>

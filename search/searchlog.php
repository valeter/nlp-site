<?php
require_once("./tools.php");
//http://localhost:8983/solr/select?shards=localhost:8983/solr/collection5,localhost:8983/solr/collection6

function write2tmplog($text)
{
 //$ip=$_SERVER['REMOTE_ADDR'];
 //$ua=$_SERVER['HTTP_USER_AGENT'];
 //$today = date("D M j G:i:s T Y");  
 $myFile = "/var/www/search_i.log";
 $fh = fopen($myFile, 'a+');
 $stringData = $text . "\n";
 echo $stringData;
 fwrite($fh, $stringData); 
 fclose($fh); 
}
function showHeader()
{
	echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>
		Поиск по математическим статьям
	</title>
<meta http-equiv='Content-Type' content='application/xhtml+xml; charset=utf-8'/>
<link type="text/css" rel="stylesheet" href="../css/results.css" />
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
	 jax: ["input/TeX", "output/HTML-CSS"],
	showProcessingMessages: true,
  tex2jax: {inlineMath: [['$','$']],
  displayMath: [ ['$$','$$']],
  processEnvironments: false}
});
</script>
<script type="text/javascript"
   src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
<script type="text/javascript" src="./js/linkedselect.js"></script>
</head>
<body>
END;
  echo "<div id=\"wrapper\">\n";
  showSideBar();
  echo "<div id=\"content1\">\n"; 
  require_once( 'requestlog.php' );
}
/*
*/
function showFooter()
{
	require_once( 'footer.php' );
}

function showFullResponseSize($size)
{	
	$sd10=$size % 10;
	$sm10=($size-$sd10)/10;
	if ($sm10==1)
	{
	  echo "Найдено: ".$size." статей  \n";  
	} 
	else 
	{
	  if ($sd10==1)
	  {
		echo "Найдено: ".$size." статья  \n";  
	  } 
	  else 
	  {
		if (($sd10>1)&&($sd10<5))
		{
			echo "Найдено: ".$size." статьи  \n";  
		} 
		else 
		{
		  echo "Найдено: ".$size." статей  \n";  
		}
	  }
	}
}
//function generatequery($query2,$sort)
//{
	//if ($sort!="solr"){
		//$query3 = '+'.$query2.'+_val_:"'.$sort.'"^100';
    //} else {
		//$query3=$query2;
	//}		
    //return $query3;
//}
function generatesort($query2,$sort)
{
	//echo "<pre>$query2</pre>";
	$ar1= array("(", ")"," ");
	$ar2= array("AND", "OR");	
	$query3='sum(div('.$sort.',10),product(strdist(\''.str_replace($ar1,'',str_replace($ar2,'+',$query2)).'\',term,ngram),termlength)) desc';
	return $query3;
	//product(strdist('КВАДРАТИЧНЫЙ+ФУНКЦИЯ'%2Cterm%2Cngram),termlength)+desc
}
function showResponseSize($size)
{	
	$sd10=$size % 10;
	$sm10=($size-$sd10)/10;
	if ($sm10==1)
	{
		return $size." вхождений";  
	} else {
	if ($sd10==1)
	{
		return $size." вхождение";  
	} else {
		if (($sd10>1)&&($sd10<5))
		{
			return $size." вхождения";  
		} else {
			return $size." вхождений";  
		}
	}		}
}
function showFooterSearch()
{
	echo "<div id=\"clear\"></div>";
  echo "</div>";  //content1
   echo "<div id=\"footer-push\"></div>";
  echo "</div>"; //wrapper
}
function showSideBar()
{	
echo <<<END
 <div id="content" class="sidebar">
    <div id="sidebar">
    
    </div>
    </div>
END;
}
/*
  <div class="sidebarpic">
    <a href="http://kpfu.ru/"><img src="picture/kfu.jpg" width="200px" alt="Казанский (Приволжский) Федеральный Университет"/></a>
    </div>
    <div class="sidebarpic">  
    <a href="http://mathnet.ru/ivm"><img src="picture/ivm.jpg" width="200px" alt="Math-Net.Ru"/></a>
    </div>
    <div class="sidebarpic">
    <a href="http://www.ksu.ru/journals/izv_vuz/"><img src="picture/ivm2.jpg" width="200px" alt="Журнал &quot;Известия высших учебных заведений. Математика&quot;"/></a>
    </div>
    <div class="sidebarpic">
    <a href="http://cll.niimm.ksu.ru/"><img src="picture/cll.png" width="200px" alt="Лаборатория математической и компьютеной лингвистики"/></a>
    </div>
    */


function write2log($query,$query2,$offset)
{
 $ip=$_SERVER['REMOTE_ADDR'];
 $ua=$_SERVER['HTTP_USER_AGENT'];
 $today = date("D M j G:i:s T Y");  
 $myFile = "phprequest.log";
 $fh = fopen($myFile, 'a');
 $stringData = $ip . ' ' . $today . ' ' . $query . ' ' . $query2 . '&o=' . $offset . ' ' . $ua . "\n";
 //echo $stringData;
 fwrite($fh, $stringData); 
 fclose($fh); 
}
function showNavigation($dz,$offset,$limit,$query,$query2)
{
    //print_r($dz);
    echo "<div class=\"result_info\">\n";    
	$nextp=$offset+$limit;
    $prevp=$offset-$limit;
    if ($dz[2]) {
		  echo "  <a href=\"searchlog.php?q=$query&amp;o=$prevp\">&lt; Предыдущая </a>"; 
	  } 
	  else {
		  echo "  &lt; Предыдущая "; 
	  }
	 echo " Страница ". $dz[0];
    if ($dz[1]) {
		  echo "<a href=\"searchlog.php?q=$query&amp;o=$nextp\"> Следующая &gt;</a> \n";
	  }
	  else {
		  echo " Следующая &gt;\n";
	  }	
	  echo "  <div class=\"clear\"></div>\n";
	echo "</div>\n";
}
function navigation($solr,$query3,$offset,$limit, $params)
{
	  //echo "$offset1,$numfound1";
      $dz[0]=(integer)($offset/$limit)+1;  
      $nextp=$offset+$limit;
      $prevp=$offset-$limit;
      //echo "<br><br>";
      try {
        $response=$solr->search( $query3, $nextp, $limit, $params);
        //print_r($response);
        //print_r($response->getHttpStatus());
        if ($response->getHttpStatus() == 200 )
          {$dz[1]=(isset($response->response->docs[0]));}
          else {$dz[1]=0;}
      }
      catch (Exception $e) {
        //echo $e;
        $dz[1]=0;
      }
      try {
        $response=$solr->search( $query3, $prevp, $limit, $params);
        //print_r($response->link);
        if ($response->getHttpStatus() == 200 )
          {$dz[2]=(isset($response->response->docs[0]));}
          else {$dz[2]=0;}
	  }
	  catch (Exception $e) {
		$dz[2]=0;
        //echo $e;
      }
      return $dz;
}
//2,2385
//27,394
function showOtherSE($query,$output2)
{
			echo " <div id=\"result_list\">\n";
			echo "<div class=\"result\">\n";	
			echo "<pre>$output2[1]</pre>\n";
			echo "</div>\n";
			echo "<div class=\"result\">\n";	
			echo "<a href=\"http://yandex.ru/yandsearch?site=mathnet.ru&amp;text=".str_replace(' ','+',$query)."\">Воспользоваться поиском Яндекс</a>\n";
			echo "</div>\n";
			echo "<div class=\"result\">\n";	
			echo "<a href=\"http://google.ru/?q=site:mathnet.ru+".str_replace(' ','+',$query)."\">Воспользоваться поиском Google</a>\n";
			echo "</div>\n";
			echo "<div class=\"result\">\n";	
			echo "<a href=\"http://citeseerx.ist.psu.edu/search?q=".str_replace(' ','+',$query)."\">Воспользоваться поиском CiteSeerX</a>\n";
			echo "</div>\n";
			echo "</div>\n";
}

function morphsh($mquery)
{
	$query=str_replace('\'','',$mquery);
    $cmd1='bash searchsh/morph_s.sh \''.$query.'\'';
    //echo $cmd1;
    $output = shell_exec($cmd1);   
	  return $output;
}  



function generatequery($query,$focus)
{
	if ($focus==0){
	$list=explode(" ",$query);
	$vbp=0;	
	$wnsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection4' );
	//$params['df']=
	$lang=detectlanguage($query);
	//echo $lang;
	$num=0;
	foreach ( $list as $word ) { 
		if ($lang=="e"){
			$wquery="synset_label:".$word." OR sense_label:".$word." OR lf_label:".$word;
		} else {
			$wquery="synset_label_RUS:".$word." OR sense_label_RUS:".$word." OR lf_label_RUS:".$word;
		}
		$response = $wnsolr->search( $wquery, 0, 1);
		if ($response->response->numFound>0){
			if ($lang=="e"){
				$wlist[$num]="(".$response->response->docs[0]->synset_label_RUS." OR ".$response->response->docs[0]->sense_label_RUS." OR ".$response->response->docs[0]->lf_label_RUS.")";
			}else{
				$wlist[$num]="(".$response->response->docs[0]->synset_label." OR ".$response->response->docs[0]->sense_label." OR ".$response->response->docs[0]->lf_label.")";
			}
		}else{
			$wlist[$num]="ERROR!";
			$vbp=1;
		}
		$num++;
		
	}
	if ($vbp==0){
		$q2=implode(" AND ",$list);
		$q3=implode(" AND ",$wlist);
		$q="((".$q2.") OR (".$q3."))";
		return $q;
	}else{
		return "error286";	
	}
}else{
	$vbp=0;	
	$wnsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection4' );
	$lang=detectlanguage($query);
	$num=0;
	$word=$query;
	$list[0]=$word;
	if ($lang=="e"){
			$wquery="synset_label:(".$word.") OR sense_label:(".$word.") OR lf_label:(".$word.")";
		} else {
			$wquery="synset_label_RUS:(".$word.") OR sense_label_RUS:(".$word.") OR lf_label_RUS:(".$word.")";
		}
	$response = $wnsolr->search( $wquery, 0, 1);
	if ($response->response->numFound>0){
		if ($lang=="e"){
			$wlist[$num]="(".$response->response->docs[0]->synset_label_RUS." OR ".$response->response->docs[0]->sense_label_RUS." OR ".$response->response->docs[0]->lf_label_RUS.")";
		}else{
			$wlist[$num]="(".$response->response->docs[0]->synset_label." OR ".$response->response->docs[0]->sense_label." OR ".$response->response->docs[0]->lf_label.")";
		}
	}else{
		$wlist[$num]="ERROR!";
		$vbp=1;
	}
	$num++;
	if ($vbp==0){
		$q2=implode(" AND ",$list);
		$q3=implode(" AND ",$wlist);
		$q="((".$q2.") OR (".$q3."))";
		return $q;
	}else{
		return "error317";	
	}
}
}








  require_once( '../Apache/Solr/Service2.php' );
  $solr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection6/' );
  if (isset($_GET['q'])){
    $query=$_GET['q'];
  }else{
    $query = "";
  }
  $focus=0;
  if (isset($_GET['m'])){
	$m1=$_GET['m'];
	if ($m1!='нет'){
		$query=$m1;
		$focus=1;
	}
  }
  $query2=$query;
  $sort="s";
  if (isset($_GET['o'])){
    $offset=$_GET['o'];
    if ($offset==""){
      $offset=0;
    }
  }else{
    $offset = 0;
  }   
  if (isset($_GET['r'])){
    $retry=$_GET['r'];
  }else{
	  $retry=0;
  } 
  if ( ! $solr->ping()){
	if ($retry>2){
			showHeader();
	        echo "<pre>Solr service not responding.</pre>";
	        showFooterSearch();
            showFooter();
            exit;
	}else{
		    //sleep(2);
		    //write2tmplog("Ssnr: r=".($retry+1)."&amp;q=".str_replace(' ','+',$query)."&amp;s=".$sort);
			header("Location: searchlog.php?r=".($retry+1)."&q=".str_replace(' ','+',$query)."&s=".$sort);
			exit;
	}
  }  
  showHeader();
  $query2=generatequery($query2,$focus);
  //echo $query2;
  if ($query2 != "")
  {
  write2log($query,$query2,$offset);
  //$params['group']='true';
  //$params['sort']=$query3s;
  //$params['group.field']='link';
  $params['shards']='';
  $e1=$GLOBALS['e1'];
  $l1=$GLOBALS['l1'];
  #echo "$e1"."|"."$l1";
  if ($e1=='e') {$params['shards'].='localhost:8983/solr/collection6';}
  if (($e1=='e')&&($l1=='l')){$params['shards'].=',';}
  if ($l1=='l') {$params['shards'].='localhost:8983/solr/collection5';}
  if ($params['shards']=="") {$query2.='akdjfvabcjhabsdhcbgsdvcghs';}
  #$params['shards']='localhost:8983/solr/collection5,localhost:8983/solr/collection6';
  $params['indent']='true';
  $params['hl']='true';
  $params['hl.fl']='text';
  $params['hl.fragsize']='500';
  $params['q.op']='AND';
  #$query2='"'.$query2.'"~10';
  #$params['group.limit']='1';
  #$params['group.format']='simple';
  $limit=10;
  //echo "<pre>$query3s</pre>";
  $response = $solr->search( $query2, $offset, $limit, $params);
  //echo "<pre>$response</pre>"
  if ( $response->getHttpStatus() == 200 ) { 	
	  echo "<div>\n";
      //echo "Результаты поиска по запросу: ".$query2." <br/>\n";      
      showFullResponseSize($response->response->numFound); 
      echo "</div>\n";
/*
      echo "<div><pre>";
      print_r($response->highlighting);
      //print_r($response);
      echo "</pre></div>";
  */  
      if ( $response->response->numFound > 0 ) {
		$nav=navigation($solr,$query2,$offset,$limit, $params);
		showNavigation($nav,$offset,$limit,$query,$query2,$sort);
		echo "<div id=\"result_list\">\n";
		$inum=$offset;
        foreach ( $response->response->docs as $doc ) { 
		  #print_r( $group->doclist->docs[0]->preview[0]);
          $inum++;
	      echo "  <div class=\"result\"><h3>\n";
          echo "    ".$inum.". <a href=\"" . $doc->link . "\">" . htmlspecialchars($doc->title) . "</a></h3> \n";
          echo "    <div class=\"pubinfo\">\n";
          echo "      ".$doc->author."\n";
          echo "      ".$doc->year ."\n";
          echo "    </div>\n";
          #echo "    <div class=\"pubinfo\">\n";
          #echo "      УДК: " . $doc->udk."\n";
          #echo "    </div>\n";
          echo "    <div class=\"snippet\">\n";          
          $docid=$doc->id;
          #if ()
          #echo "<div><pre>";
          if (isset($response->highlighting->$docid->text[0])){
			 echo "      ".str_replace(array('&lt;br/','&lt;br','r/&gt;','br/&gt;','&lt;b','/&gt;','&lt;','&gt;'),array('','','','','','','',''),str_replace(array('&lt;em&gt;','&lt;/em&gt;','&lt;br/&gt;'),array('<em>','</em>','<br/>'),htmlspecialchars($response->highlighting->$docid->text[0])))."\n"; 
		  }else {
		      #echo "</pre></div>"; 
              echo "      No preview\n" ;
			}
          echo "    </div>\n";
          //echo "    <a href=\"adlog.php?d=" . $doc->data . "\">Читать полный текст лога</a> \n";
          echo "  </div>\n";
        }
        echo "</div>\n";//result_list<em> </em> 
        showNavigation($nav,$offset,$limit,$query,$query2,$sort);
    } else {
		if (($query2=="error286")||($query2=="error317"))
		   echo "[WordNet]Error: not found in dictionary";
	}
  }
  else {
    echo $response->getHttpStatusMessage();
  }
  } 
  showFooterSearch();
  showFooter();
?>

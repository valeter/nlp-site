<?php
require_once("tools.php");
require_once("interface.php");
require_once("query.php");


function preparequery($query2)
{
	$list=explode(") OR (",$query2);	
	$list2=explode(') AND (',$list[1]);
	$trans = array("(" => " ", ")" => " ", " AND " => " ");
	$oquery=trim(strtr($list[0],$trans)).' | ';
	foreach ($list2 as $word) {
		if (strpos($word,' OR ')===FALSE){
			$oquery.=trim(strtr($word,$trans)).' ';
		}else{
			$oquery.=trim(strtr(substr($word,0,strpos($word,' OR ')),$trans)).' ';
		}
	}
	return $oquery;
}



  require_once( '../Apache/Solr/Service2.php' );
  $solr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection6' );
  if (isset($_GET['q'])){
    $query=$_GET['q'];
  }else{
    $query = "";
  }
  $focus=0;
  $query2=$query;

  
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
 
  if (isset($_GET['o'])){
    $offset=$_GET['o'];
    if ($offset==""){
      $offset=0;
    }
  }else{
    $offset = 0;
  } 
  if ( ! $solr->ping()){	
			showHeader();
	        echo "<pre>Solr service not responding.</pre>";
	        showFooterSearch();
            showFooter();
            exit;	
  }  
  showHeader();
  $query2=generatequery($query,$state,$m1,$c1,$t1,$oquery);
  $trans = array("(" => "", ")" => "");
  $oquery=trim(strtr($oquery,$trans));
  //$oquery=preparequery($query2);
  //echo $query2;
  if ($query2 != "")
  {
  write2log($query,$query2,$offset);
  //$params['group']='true';
  //$params['sort']=$query3s;
  //$params['group.field']='link';
  $params['shards']='';
  if (isset($_GET['l'])) {
	  $l1=$_GET['l'];
  }else{
	  $l1='';
  }
  if (isset($_GET['e'])) {
	  $e1=$_GET['e'];
  }else{
	  $e1='';
  }
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
  $limit=10;
  //echo "<pre>$query2</pre>";
  $response = $solr->search( $query2, $offset, $limit, $params);
  //echo "<pre>$response</pre>";
  if ( $response->getHttpStatus() == 200 ) { 	
	  echo "<div>\n";
      echo "Результаты поиска по запросу: ".$oquery." <br/>\n";      
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
		showNavigation($nav,$offset,$limit,$query,$m1,$l1,$e1);
		echo "<div id=\"result_list\">\n";
		$inum=$offset;
        foreach ( $response->response->docs as $doc ) { 		  
          $inum++;
	      echo "  <div class=\"result\"><h3>\n";
          echo "    ".$inum.". <a href=\"" . $doc->link . "\">" . htmlspecialchars($doc->title) . "</a></h3> \n";
          echo "    <div class=\"pubinfo\">\n";
          echo "      ".$doc->author."\n";
          echo "      ".$doc->year ."\n";
          echo "    </div>\n";
          echo "    <div class=\"snippet\">\n";          
          $docid=$doc->id;
          if (isset($response->highlighting->$docid->text[0])){
			 echo "      ".str_replace(array('&lt;br/','&lt;br','r/&gt;','br/&gt;','&lt;b','/&gt;','&lt;','&gt;'),array('','','','','','','',''),str_replace(array('&lt;em&gt;','&lt;/em&gt;','&lt;br/&gt;'),array('<em>','</em>','<br/>'),htmlspecialchars($response->highlighting->$docid->text[0])))."\n"; 
		  }else {		    
              echo "      No preview\n" ;
			}
          echo "    </div>\n";          
          echo "  </div>\n";
        }
        echo "</div>\n";
        showNavigation($nav,$offset,$limit,$query,$m1,$l1,$e1);
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

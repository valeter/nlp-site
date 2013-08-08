<?php
function ordutf8($string, &$offset) {
    $code = ord(substr($string, $offset,1));
    if ($code >= 128) {
        if ($code < 224) $bytesnumber = 2;
        else if ($code < 240) $bytesnumber = 3;
        else if ($code < 248) $bytesnumber = 4;
        $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
        for ($i = 2; $i <= $bytesnumber; $i++) {
            $offset ++;
            $code2 = ord(substr($string, $offset, 1)) - 128;
            $codetemp = $codetemp*64 + $code2;
        }
        $code = $codetemp;
    }
    $offset += 1;
    if ($offset >= strlen($string)) $offset = -1;
    return $code;
}

function detectlanguage($text)
{ 
	$eng=1;
	$offset = 0;
    while ($offset >= 0) {
		$a=ordutf8($text, $offset);
		$eng=($eng&&(($a==32)||(($a>=65)&&($a<=90))||(($a>=97)&&($a<=122))));
	}
	if ($eng==1){
		return "e";
	}else{
		return "r";
	}
}

function write2log($query,$query2,$offset)
{
 $ip=$_SERVER['REMOTE_ADDR'];
 $ua=$_SERVER['HTTP_USER_AGENT'];
 $today = date("D M j G:i:s T Y");  
 $myFile = "phprequest.log";
 $fh = fopen($myFile, 'a');
 $stringData = $ip . ' ' . $today . ' ' . $query . ' ' . $query2 . '&o=' . $offset . ' ' . $ua . "\n";
 fwrite($fh, $stringData); 
 fclose($fh); 
}

function navigation($solr,$query3,$offset,$limit, $params)
{
      $dz[0]=(integer)($offset/$limit)+1;  
      $nextp=$offset+$limit;
      $prevp=$offset-$limit;
      try {
        $response=$solr->search( $query3, $nextp, $limit, $params);
          if ($response->getHttpStatus() == 200 )
          {$dz[1]=(isset($response->response->docs[0]));}
          else {$dz[1]=0;}
      }
      catch (Exception $e) {
        $dz[1]=0;
      }
      try {
        $response=$solr->search( $query3, $prevp, $limit, $params);
        if ($response->getHttpStatus() == 200 )
          {$dz[2]=(isset($response->response->docs[0]));}
          else {$dz[2]=0;}
	  }
	  catch (Exception $e) {
		$dz[2]=0;
      }
      return $dz;
}
?>

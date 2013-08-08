<?php
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
    <div class="sidebarpic">
    <a href="http://nlp-systems.net/"><img src="picture/cloud.png" width="200px" alt="NLP@Cloud"/></a>
    </div>
    <div class="sidebarpic">  
    <a href="http://mathnet.ru/ivm"><img src="picture/ivm.jpg" width="200px" alt="Math-Net.Ru Журнал &quot;Известия высших учебных заведений. Математика&quot;"/></a>
    </div>
    <div class="sidebarpic">
    <a href="http://arxiv.org/"><img src="picture/arxiv.png" width="200px" alt="Лаборатория математической и компьютеной лингвистики"/></a>
    </div>
    </div>
    </div>
END;
}

function showNavigation($dz,$offset,$limit,$query,$m1,$l1,$e1)
{
    //print_r($dz);
    echo "<div class=\"result_info\">\n";    
	$nextp=$offset+$limit;
    $prevp=$offset-$limit;
    if ($dz[2]) {
		  echo "  <a href=\"searchlog.php?q=$query&amp;o=$prevp&amp;m=$m1&amp;l=$l1&amp;e=$e1\">&lt; Предыдущая </a>"; 
	  } 
	  else {
		  echo "  &lt; Предыдущая "; 
	  }
	 echo " Страница ". $dz[0];
    if ($dz[1]) {
		  echo "<a href=\"searchlog.php?q=$query&amp;o=$nextp&amp;m=$m1&amp;l=$l1&amp;e=$e1\"> Следующая &gt;</a> \n";
	  }
	  else {
		  echo " Следующая &gt;\n";
	  }	
	  echo "  <div class=\"clear\"></div>\n";
	echo "</div>\n";
}
?>

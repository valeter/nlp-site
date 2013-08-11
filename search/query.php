<?php
require_once( "tools.php" );
require_once( '../Apache/Solr/Service2.php' );

function translate_collocation_by_dictionary($collocation){
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lang=detectlanguage($collocation);
	if ($lang=="e"){
			$dquery="collocation_en:(".$collocation.")";
		} else {
			$dquery="collocation:(".$collocation.")";
		}
	$response = $dsolr->search( $dquery, 0, 100);
	$nlist=array();
	//print_r($response->response->docs);
	if ($response->response->numFound>0){
		foreach ($response->response->docs as $doc){
			//print_r($doc);echo '<br/>';
			if ($lang=="e"){
				$nlist[]=$doc->collocation;
			}else{
				$nlist[]=$doc->collocation_en;
			}
		//print_r($nlist);
		$nlist2=clear_empty_fields(array_unique($nlist));
		//print_r($nlist2);
		return $nlist2;	
		}
	}else{
		return false;
	}	
}

function translate_word_by_wordnet($word){
	$wnsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection4');
	$lang=detectlanguage($word);
	if ($lang=="e"){
			$wquery="synset_label:(".$word.") OR sense_label:(".$word.") OR lf_label:(".$word.")";
		} else {
			$wquery="synset_label_RUS:(".$word.") OR sense_label_RUS:(".$word.") OR lf_label_RUS:(".$word.")";
		}
	$response = $wnsolr->search( $wquery, 0, 1);
	$nlist=array();
	if ($response->response->numFound>0){
	if ($lang=="e"){
			$nlist[]=$response->response->docs[0]->synset_label_RUS;
			$nlist[]=$response->response->docs[0]->sense_label_RUS;
			$nlist[]=$response->response->docs[0]->lf_label_RUS;
		}else{
			$nlist[]=$response->response->docs[0]->synset_label;
			$nlist[]=$response->response->docs[0]->sense_label;
			$nlist[]=$response->response->docs[0]->lf_label;
		}
		$nlist=clear_empty_fields(array_unique($nlist));
		return $nlist;
	}else{
		return false;
	}
}

function generatequery($query,$state,$m1,$c1,$t1,&$oquery){
switch ($state){
	case 0:
		$list=explode(" ",$query);
		$wlist=array();
		foreach ( $list as $word ) { 
			$nlist=translate_word_by_wordnet($word);
			if ($nlist!==false){
				$wlist[]="(".implode(" OR ",$nlist).")";
			}
		}
		$wlist2=clear_empty_fields(array_unique($wlist));
		$q2=implode(" AND ",$list);
		$q3=implode(" AND ",$wlist2);
		$oquery=$list[0].' | '.$wlist2[0];
		if (strlen($q3)>3){
			$q="((".$q2.") OR (".$q3."))";
		}else{
			$q="((".$q2."))";
		}
		return $q;
	break;
	case 1:
		$list=explode(" ",$query);
		$wlist=array();
		foreach ( $list as $word ) { 
			$nlist=translate_word_by_wordnet($word);
			if ($nlist!==false){
				$wlist[]="(".implode(" OR ",$nlist).")";
			}
		}		
		$lang=detectlanguage($query);
		$nlist=translate_word_by_wordnet($t1);
		if ($lang=="e"){//$list - eng, $wlist - rus, $t1 - rus, $nlist - eng
			if ($nlist!==false){
				$list[]="(".implode(" OR ",$nlist).")";
			}
			$wlist[]="(".$t1.")";
			$oquery=$list[0].' AND '.$nlist[0].' | '.$wlist[0].' AND '.$t1;
		}else{//$list - rus, $wlist - eng, $t1 - rus, $nlist - eng
			if ($nlist!==false){
				$wlist[]="(".implode(" OR ",$nlist).")";
			}
			$list[]="(".$t1.")";
			$oquery=$list[0].' AND '.$t1.' | '.$wlist[0].' AND '.$nlist[0];
		}
		$wlist2=clear_empty_fields(array_unique($wlist));
		$q2=implode(" AND ",$list);
		$q3=implode(" AND ",$wlist2);
		if (strlen($q3)>3){
			$q="((".$q2.") OR (".$q3."))";
		}else{
			$q="((".$q2."))";
		}
		return $q;
	break;
	case 2:
		$nlist=translate_collocation_by_dictionary($c1);
		//print_r($nlist);
		if ($nlist!==false){
			$q3="\"(".implode(")\"~3 OR \"(",$nlist).")\"~3";
		}
		$q2=$c1;
		$oquery='"'.$c1.'" | "'.$nlist[0].'"';
		if (strlen($q3)>3){
			$q="(\"(".$q2.")\"~3 OR (".$q3."))";
		}else{
			$q="(\"(".$q2.")\"~3)";
		}
		return $q;
	break;
	case 3:
		$nlist=translate_collocation_by_dictionary($c1);		
		if ($nlist!==false){
			$q3="\"(".implode(")\"~3 OR \"(",$nlist).")\"~3";
		}
		$q2=$c1;
		$lang=detectlanguage($c1);
		$tlist=translate_word_by_wordnet($t1);
		if ($lang=='e'){//$q2 - eng, $q3 - rus, $t1 - rus, $tlist - eng
			if ($tlist!==false){
				$oquery='"'.$c1.'" AND '.$tlist[0].' | "'.$nlist[0].'" AND '.$t1;
				$wlist="((".implode(") OR (",$tlist)."))";
				if (strlen($q3)>3){
					$q='((("('.$q2.')"~3) AND '.$wlist.') OR (('.$q3.') AND '.$t1.'))';
				}else{
					$q='((("('.$q2.')"~3) AND '.$wlist.'))';
				}
			}else{
				$oquery='"'.$c1.'" | "'.$nlist[0].'" AND '.$t1;
				if (strlen($q3)>3){
					$q='(("('.$q2.')"~3) OR (('.$q3.') AND '.$t1.'))';
				}else{
					$q='(("('.$q2.')"~3))';
				}
			}
		}else{
			if ($tlist!==false){
				$oquery='"'.$c1.'" AND '.$t1.' | "'.$nlist[0].'" AND '.$tlist[0];
				$wlist="((".implode(") OR (",$tlist)."))";
				if (strlen($q3)>3){
					$q='((("('.$q2.')"~3) AND '.$t1.') OR (('.$q3.') AND '.$wlist.'))';
				}else{
					$q='((("('.$q2.')"~3) AND '.$t1.'))';
				}
			}else{
				$oquery='"'.$c1.'" AND '.$t1.' | "'.$nlist[0].'"';
				if (strlen($q3)>3){
					$q='((("('.$q2.')"~3) AND '.$t1.') OR ('.$q3.'))';
				}else{
					$q='((("('.$q2.')"~3) AND '.$t1.'))';
				}
			}
		}
		return $q;
	break;
	case 4:	
		$word=$m1;
		$nlist=translate_word_by_wordnet($word);
		if ($nlist!==false){
			$wlist[]="(".implode(" OR ",$nlist).")";
		}
		$wlist2=clear_empty_fields(array_unique($wlist));
		$q2=$m1;
		$q3=implode(" AND ",$wlist2);
		$oquery=$m1.' | '.$nlist[0];
		if (strlen($q3)>3){
			$q="((".$q2.") OR (".$q3."))";
		}else{
			$q="((".$q2."))";
		}
		return $q;
	break;
	case 5:
		$list=array();
		$list[]=$m1;
		$wlist=array();
		$word=$m1;
			$nlist=translate_word_by_wordnet($word);
			if ($nlist!==false){
				$wlist[]="(".implode(" OR ",$nlist).")";
			}
		$lang=detectlanguage($query);
		$nlist=translate_word_by_wordnet($t1);
		if ($lang=="e"){//$list - eng, $wlist - rus, $t1 - rus, $nlist - eng
			if ($nlist!==false){
				$list[]="(".implode(" OR ",$nlist).")";
			}
			$wlist[]="(".$t1.")";
			$oquery=$list[0].' AND '.$nlist[0].' | '.$wlist[0].' AND '.$t1;
		}else{//$list - rus, $wlist - eng, $t1 - rus, $nlist - eng
			if ($nlist!==false){
				$wlist[]="(".implode(" OR ",$nlist).")";
			}
			$list[]="(".$t1.")";
			$oquery=$list[0].' AND '.$t1.' | '.$wlist[0].' AND '.$nlist[0];
		}
		$wlist2=clear_empty_fields(array_unique($wlist));
		$q2=implode(" AND ",$list);
		$q3=implode(" AND ",$wlist2);
		if (strlen($q3)>3){
			$q="((".$q2.") OR (".$q3."))";
		}else{
			$q="((".$q2."))";
		}
		return $q;
	break;
	case 6:
		$nlist=translate_collocation_by_dictionary($c1);
		//print_r($nlist);
		if ($nlist!==false){
			$q3="\"(".implode(")\"~3 OR \"(",$nlist).")\"~3";
		}
		$q2=$c1;
		$oquery='"'.$c1.'" | "'.$nlist[0].'"';
		if (strlen($q3)>3){
			$q="(\"(".$q2.")\"~3 OR (".$q3."))";
		}else{
			$q="(\"(".$q2.")\"~3)";
		}
		return $q;
	break;
	case 7:
		$nlist=translate_collocation_by_dictionary($c1);		
		if ($nlist!==false){
			$q3="\"(".implode(")\"~3 OR \"(",$nlist).")\"~3";
		}
		$q2=$c1;
		$lang=detectlanguage($c1);
		$tlist=translate_word_by_wordnet($t1);
		if ($lang=='e'){//$q2 - eng, $q3 - rus, $t1 - rus, $tlist - eng
			if ($tlist!==false){
				$oquery='"'.$c1.'" AND '.$tlist[0].' | "'.$nlist[0].'" AND '.$t1;
				$wlist="((".implode(") OR (",$tlist)."))";
				if (strlen($q3)>3){
					$q='((("('.$q2.')"~3) AND '.$wlist.') OR (('.$q3.') AND '.$t1.'))';
				}else{
					$q='((("('.$q2.')"~3) AND '.$wlist.'))';
				}
			}else{
				$oquery='"'.$c1.'" | "'.$nlist[0].'" AND '.$t1;
				if (strlen($q3)>3){
					$q='(("('.$q2.')"~3) OR (('.$q3.') AND '.$t1.'))';
				}else{
					$q='(("('.$q2.')"~3))';
				}
			}
		}else{
			if ($tlist!==false){
				$oquery='"'.$c1.'" AND '.$t1.' | "'.$nlist[0].'" AND '.$tlist[0];
				$wlist="((".implode(") OR (",$tlist)."))";
				if (strlen($q3)>3){
					$q='((("('.$q2.')"~3) AND '.$t1.') OR (('.$q3.') AND '.$wlist.'))';
				}else{
					$q='((("('.$q2.')"~3) AND '.$t1.'))';
				}
			}else{
				$oquery='"'.$c1.'" AND '.$t1.' | "'.$nlist[0].'"';
				if (strlen($q3)>3){
					$q='((("('.$q2.')"~3) AND '.$t1.') OR ('.$q3.'))';
				}else{
					$q='((("('.$q2.')"~3) AND '.$t1.'))';
				}
			}
		}
		return $q;
	break;
	}
}


?>

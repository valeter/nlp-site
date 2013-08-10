<?php
require_once( "tools.php" );
function suggest_from_dictionary($query,&$clist,&$tlist)
{
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection7' );
	$clist1=array();
	$tlist1=array();
	$lang=detectlanguage($query);	
	if ($lang=="e"){
		$wquery="word_en:(".$query.")";
	} else {
		$wquery="word:(".$query.")";
	}
	
	$response = $dsolr->search( $wquery, 0, 100);
	if ($response->response->numFound>0){
		foreach ( $response->response->docs as $doc ) { 
			$tlist1[]=$doc->label;
			if ($lang=="e"){
				$clist1[]=$doc->collocation_en;
			}else{
				$clist1[]=$doc->collocation;
			}
		}
		$clist=clear_empty_fields(array_unique($clist1));
		$tlist2=clear_empty_fields(array_unique($tlist1));
		foreach ($tlist2 as $label){
			$lquery="label:".$label;
			$response = $lsolr->search( $lquery, 0, 1);
			$tlist[]=$doc->text;
		return true;
		}
	}else{
		return false;
	}
	
}
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
	//print_r($response);
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
		$w2list=clear_empty_fields(array_unique($wlist));
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
		return "error70";
	} else {
		return clear_empty_fields(array_unique($rlist));
	}
}
?>

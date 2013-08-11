<?php
require_once( "tools.php" );
require_once( '../Apache/Solr/Service2.php' );

function suggest_meaning($query, $collocation, $theme, &$mlist){
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection7' );
	$mlist1=array();
	$lquery="text:".$theme;
	$response = $lsolr->search( $lquery, 0, 1);
	$label=$response->response->docs[0]->label;
	$lang=detectlanguage($query);
	if ($lang=="e"){
		$wquery="word_en:(".$query.") AND col_label:(".$label.") AND collocation_en:(".$collocation.")";
	} else {
		$wquery="word:(".$query.") AND col_label:(".$label.") AND collocation:(".$collocation.")";
	}
	$response = $dsolr->search( $wquery, 0, 100);
	if ($response->response->numFound>0){
		foreach ( $response->response->docs as $doc ) {
			if ($lang=="e"){
				$mlist1[]=$doc->word_en;
			}else{
				$mlist1[]=$doc->word;
			}
		}
		$mlist=clear_empty_fields(array_unique($mlist1));
		return true;
	}else{
		return false;
	}
}

function suggest_by_using_collocation($query, $collocation, &$mlist, &$tlist){
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection7' );
	$tlist1=array();
	$tlist3=array();
	$mlist1=array();
	$lang=detectlanguage($query);
	if ($lang=="e"){
		$wquery="word_en:(".$query.") AND collocation_en:(".$collocation.")";
	} else {
		$wquery="word:(".$query.") AND collocation:(".$collocation.")";
	}
	$response = $dsolr->search( $wquery, 0, 100);
	if ($response->response->numFound>0){
		foreach ( $response->response->docs as $doc ) {
			$tlist1[]=$doc->col_label;
			if ($lang=="e"){
				$mlist1[]=$doc->word_en;
			}else{
				$mlist1[]=$doc->word;
			}
		}
		$mlist=clear_empty_fields(array_unique($mlist1));
		$tlist2=clear_empty_fields(array_unique($tlist1));
		foreach ($tlist2 as $label){
			$lquery="label:".$label;
			$response = $lsolr->search( $lquery, 0, 1);
			if ($response->response->numFound>0){$tlist3[]=$response->response->docs[0]->text;}
		}
		$tlist=clear_empty_fields(array_unique($tlist3));
		return true;
	}else{
		return false;
	}
}

function suggest_by_using_collocation1($meaning, $collocation, &$tlist){
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection7' );
	$tlist1=array();
	$tlist3=array();
	$query=$meaning;
	$lang=detectlanguage($query);
	if ($lang=="e"){
		$wquery="word_en:(".$query.") AND collocation_en:(".$collocation.")";
	} else {
		$wquery="word:(".$query.") AND collocation:(".$collocation.")";
	}
	$response = $dsolr->search( $wquery, 0, 100);
	if ($response->response->numFound>0){
		foreach ( $response->response->docs as $doc ) {
			$tlist1[]=$doc->col_label;
		}		
		$tlist2=clear_empty_fields(array_unique($tlist1));
		foreach ($tlist2 as $label){
			$lquery="label:".$label;
			$response = $lsolr->search( $lquery, 0, 1);
			$tlist3[]=$response->response->docs[0]->text;
		}
		$tlist=clear_empty_fields(array_unique($tlist3));
		return true;
	}else{
		return false;
	}
}

function suggest_by_using_theme($query, $theme, &$mlist, &$clist){
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection7' );
	$clist1=array();
	$mlist1=array();
	$lquery="text:".$theme;
	$response = $lsolr->search( $lquery, 0, 1);
	$label=$response->response->docs[0]->label;
	$lang=detectlanguage($query);
	if ($lang=="e"){
		$wquery="word_en:(".$query.") AND label:(".$label.")";
	} else {
		$wquery="word:(".$query.") AND label:(".$label.")";
	}
	$response = $dsolr->search( $wquery, 0, 100);
	if ($response->response->numFound>0){
		foreach ( $response->response->docs as $doc ) {
			if ($lang=="e"){
				$clist1[]=$doc->collocation_en;
				$mlist1[]=$doc->word_en;
			}else{
				$clist1[]=$doc->collocation;
				$mlist1[]=$doc->word;
			}
		}
		$clist=clear_empty_fields(array_unique($clist1));
		$mlist=clear_empty_fields(array_unique($mlist1));
		return true;
	}else{
		return false;
	}
}

function suggest_by_using_theme1($meaning, $theme, &$clist){
	$query=$meaning;
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection7' );
	$clist1=array();
	$lquery="text:".$theme;
	$response = $lsolr->search( $lquery, 0, 1);
	$label=$response->response->docs[0]->label;
	$lang=detectlanguage($query);
	if ($lang=="e"){
		$wquery="word_en:(".$query.") AND label:(".$label.")";
	} else {
		$wquery="word:(".$query.") AND label:(".$label.")";
	}
	$response = $dsolr->search( $wquery, 0, 100);
	if ($response->response->numFound>0){
		foreach ( $response->response->docs as $doc ) {
			if ($lang=="e"){
				$clist1[]=$doc->collocation_en;
			}else{
				$clist1[]=$doc->collocation;
			}
		}
		$clist=clear_empty_fields(array_unique($clist1));
		return true;
	}else{
		return false;
	}
}

function suggest_from_dictionary($query,&$clist,&$tlist){
	$dsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection8' );
	$lsolr = new Apache_Solr_Service( 'localhost', '8983', '/solr/collection7' );
	$clist1=array();
	$tlist1=array();
	$tlist3=array();
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
			$tlist3[]=$response->response->docs[0]->text;
		}
		$tlist=clear_empty_fields(array_unique($tlist3));
		return true;
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

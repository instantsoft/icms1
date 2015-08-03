<?php
/*
// JoomlaWorks "Tabs & Slides" Plugin for Joomla! 1.0.x - Version 2.3
// License: http://www.gnu.org/copyleft/gpl.html
// Authors: Fotis Evangelou - George Chouliaras
// Copyright (c) 2006 - 2007 JoomlaWorks.gr - http://www.joomlaworks.gr
// Project page at http://www.joomlaworks.gr - Demos at http://demo.joomlaworks.gr
// Support forum at http://forum.joomlaworks.gr
// ***Last update: August 30th, 2007***
*/

function jwHeader(){
	$header = "
<style type=\"text/css\" media=\"screen\">
	@import \"/includes/jwtabs/tabs_slides.css\";
</style>
<style type=\"text/css\" media=\"print\">.jwts_tabbernav{display:none;}</style>
<script type=\"text/javascript\">var jwts_slideSpeed=30; var jwts_timer=10;</script>
<script type=\"text/javascript\" src=\"/includes/jwtabs/tabs_slides_comp.js\"></script>
";
	$header .= "<script type=\"text/javascript\" src=\"/includes/jwtabs/tabs_slides_def_loader.js\"></script>";
	return $header;
}

function jwTabs( $text, $published = true ) {
	//$text = 'test'.$text;
	$enable_tabs = 1;
  	$enable_slides = 1;
// JS loader selection
$use_optimized_loader = 0; // Use optimized JS code loader? 0=no and 1=yes. Default is 0.

  if (!$published) {
    if (preg_match_all("/{tab=.+?}/u", $text, $matches, PREG_PATTERN_ORDER) > 0) {
     foreach ($matches[0] as $match) {
      $match = str_replace("{tab=", "", $match);
      $match = str_replace("}", "", $match);
      $text = str_replace( "{tab=".$match."}", "", $text );
      $text = str_replace( "{/tabs}", "", $text );
     }     
    }
    if (preg_match_all("/{slide=.+?}/u", $text, $matches, PREG_PATTERN_ORDER) > 0) {
      foreach ($matches[0] as $match) {
        $match = str_replace("{slide=", "", $match);
        $match = str_replace("}", "", $match);
        $text = str_replace( "{slide=".$match."}", "", $text );
        $text = str_replace( "{/slide}", "", $text );
      } 
    }  	
    return;
  }

  $path = $_SERVER['DOCUMENT_ROOT'];
  $live_site = 'http://'.$_SERVER['HTTP_HOST'];
  static $tabid;   
  
  // Start Tabs Replacement
  // index.php
  if($enable_tabs) {
	   $b=1;
	   if (preg_match_all("/{tab=.+?}{tab=.+?}|{tab=.+?}|{\/tabs}/u", $text, $matches, PREG_PATTERN_ORDER) > 0) { 	
	    foreach ($matches[0] as $match) {	
	      if($b==1 && $match!="{/tabs}") {
	    	$tabs[] = 1;
	    	$b=2;
	      }
	      elseif($match=="{/tabs}"){
	      	$tabs[]=3;
	      	$b=1;
	      }
	      elseif(preg_match("/{tab=.+?}{tab=.+?}/u", $match)){
	      	$tabs[]=2;
	      	$tabs[]=1;
	      	$b=2;
	      }
	      else {
	      	$tabs[]=2;
	      }
	    }
	   }
	   @reset($tabs);
	   $tabscount = 0;
	  if (preg_match_all("/{tab=.+?}|{\/tabs}/u", $text, $matches, PREG_PATTERN_ORDER) > 0) {
	    foreach ($matches[0] as $match) {
	      if($tabs[$tabscount]==1) {
	      	$match = str_replace("{tab=", "", $match);
	        $match = str_replace("}", "", $match);
	        $text = str_replace( "{tab=".$match."}", "
			<div class=\"jwts_tabber\" id=\"jwts_tab".$tabid."\"><div class=\"jwts_tabbertab\" title=\"".$match."\"><h2><a href=\"javascript:void(null);\" name=\"advtab\">".$match."</a></h2>", $text );        
	        $tabid++;
	      } elseif($tabs[$tabscount]==2) {
	      	$match = str_replace("{tab=", "", $match);
	        $match = str_replace("}", "", $match);
	      	$text = str_replace( "{tab=".$match."}", "</div><div class=\"jwts_tabbertab\" title=\"".$match."\"><h2><a href=\"javascript:void(null);\" name=\"advtab\">".$match."</a></h2>", $text );
	      } elseif($tabs[$tabscount]==3) {
	      	$text = str_replace( "{/tabs}", "</div></div><div class=\"jwts_clr\"></div>", $text );
	      }
	      $tabscount++;
	    }     
	  }    	
  }
  // End Tabs Replacement
  
  // Start Slides Replacement
  // index.php
  if($enable_slides) {
	   if (preg_match_all("/{slide=.+?}/u", $text, $matches, PREG_PATTERN_ORDER) > 0) {
	    foreach ($matches[0] as $match) {
	      $match = str_replace("{slide=", "", $match);
	      $match = str_replace("}", "", $match);
	      $text = str_replace( "{slide=".$match."}", "<div class=\"jwts_title\"><div class=\"jwts_title_left\"><a href=\"javascript:void(null);\" title=\"Нажмите чтобы открыть\" class=\"jwts_title_text\">".$match."</a></div></div><div class=\"jwts_slidewrapper\"><div>", $text );
	      $text = str_replace( "{/slide}", "</div></div>", $text );
	    }   
	   }
  }
  // End Slides Replacement
  return $text;
}

?>

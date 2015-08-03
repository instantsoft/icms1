<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.6                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

function icon($icon, $link, $title, $onClick=''){

	if ($onClick==''){
		$html = '<a class="icon" title="'.$title.'" href="'.$link.'"><img border="0" src="/images/icons/'.$icon.'.png" alt="'.$title.'"></a>';
	} else {
		$html = '<a class="icon" title="'.$title.'" href="'.$link.'" onClick="'.$onClick.'"><img border="0" src="/images/icons/'.$icon.'.png" alt="'.$title.'"></a>';
	}
	return $html;

}

function inArray($array, $item){

	$found = false;
	foreach($array as $key=>$value){
		if ($value == $item) {
			$found = true;
		}
	}
	return $found;

}

?>
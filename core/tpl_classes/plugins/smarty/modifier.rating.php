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
function smarty_modifier_rating($rating, $with_icon=false){
	if ($rating==0) {
		$html = '<span class="color_gray">0</span>';
	} elseif ($rating>0){
		$html = '<span class="color_green">'.($with_icon ? '<i class="fa fa-thumbs-up fa-lg"></i> ' : '').'+'.$rating.'</span>';
	} else {
		$html = '<span class="color_red">'.($with_icon ? '<i class="fa fa-thumbs-down fa-lg"></i> ' : '').$rating.'</span>';
	}
	return $html;
}
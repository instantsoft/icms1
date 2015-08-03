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

	define('PATH', $_SERVER['DOCUMENT_ROOT']);
	include(PATH.'/core/ajax/ajax_core.php');

    $q = mb_strtolower(cmsCore::request('q', 'str', ''));
    if (!$q) { cmsCore::halt(); }

	$sql = "SELECT tag FROM cms_tags WHERE LOWER(tag) LIKE '{$q}%' GROUP BY tag";
	$rs  = $inDB->query($sql);

	while ($item = $inDB->fetch_assoc($rs)){
		echo $item['tag']."\n";
	}

    cmsCore::halt();

?>
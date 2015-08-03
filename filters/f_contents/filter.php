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
	function f_contents(&$text){

        $phrase = 'СТРАНИЦА';

		if (mb_strpos($text, $phrase) === false){
			return true;
		}

 		$regex = '/{('.$phrase.'=)\s*(.*?)}/ui';
		$matches = array();
		preg_match_all( $regex, $text, $matches, PREG_SET_ORDER );
		$GLOBALS['pt'] = array();
		foreach ($matches as $elm) {
			$elm[0] = str_replace('{', '', $elm[0]);
			$elm[0] = str_replace('}', '', $elm[0]);
			mb_parse_str( $elm[0], $args );
			$title=@$args[$phrase];
			if ($title){
				$GLOBALS['pt'][] = $title;
			}
			$text = str_replace('{'.$phrase.'='.$title.'}', '', $text );
		}

		return true;

	}
?>
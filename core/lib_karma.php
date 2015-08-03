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

function cmsKarma($target, $item_id){ //returns array with total votes and total points of karma

    if (!preg_match('/^([a-zA-Z0-9\_]+)$/ui', $target)) { return; }

    $item_id = intval($item_id);

    $inDB = cmsDatabase::getInstance();

    $item = $inDB->get_fields('cms_ratings_total', "item_id = '$item_id' AND target='$target'", 'total_rating, total_votes');

	if (!$item){ return array('points'=>0, 'votes'=>0); }

	return array('points'=>$item['total_rating'], 'votes'=>$item['total_votes']);

}

function cmsAlreadyKarmed($target, $item_id, $user_id){
    $inDB = cmsDatabase::getInstance();
	return $inDB->rows_count('cms_ratings', "target='$target' AND item_id = $item_id AND user_id = '$user_id'");
}

function cmsAlreadyKarmedIP($target, $item_id, $ip){
    $inDB = cmsDatabase::getInstance();
	return $inDB->rows_count('cms_ratings', "target='$target' AND item_id = $item_id AND ip = '$ip'");
}

function cmsSubmitKarma($target, $item_id, $points){

    $inUser  = cmsUser::getInstance();
    $inDB    = cmsDatabase::getInstance();
	$ip      = cmsCore::strClear($_SERVER['REMOTE_ADDR']);
	$target  = cmsCore::strClear($target);
	$points  = (int)$points;
	$item_id = (int)$item_id;

	if(cmsAlreadyKarmed($target, $item_id, $inUser->id)){ return false; }

    //вставляем новый голос
    $sql = "INSERT INTO cms_ratings (item_id, points, ip, target, user_id, pubdate)
            VALUES ('$item_id', '$points', '$ip', '$target', '{$inUser->id}', NOW())";
    $inDB->query($sql);

    //проверяем была ли сделана агрегация для этой цели ранее
    $is_agr = $inDB->rows_count('cms_ratings_total', "target='$target' AND item_id = '$item_id'", 1);

    //если была, то обновляем
    if ($is_agr) { $agr_sql = "UPDATE cms_ratings_total
                               SET  total_rating = total_rating + ({$points}),
                                    total_votes  = total_votes + 1
                               WHERE target='$target' AND item_id = '$item_id'"; }

    //если не было, то вставляем
    if (!$is_agr) { $agr_sql = "INSERT INTO cms_ratings_total (target, item_id, total_rating, total_votes)
                                VALUES ('{$target}', '{$item_id}', '{$points}', '1')"; }

    $inDB->query($agr_sql);

    //получаем информацию о цели
    $info = $inDB->get_fields('cms_rating_targets', "target='{$target}'", '*');

    //если нужно, изменяем рейтинг автора цели
    if ($info['is_user_affect'] && $info['user_weight'] && $info['target_table']){

        $user_sql = "UPDATE cms_users u,
                            {$info['target_table']} t
                     SET u.rating = u.rating + ({$points}*{$info['user_weight']})
                     WHERE t.user_id = u.id AND t.id = '$item_id'";

        $inDB->query($user_sql);

    }

    //проверяем наличие метода updateRatingHook(target, item_id, points) в модели
    //компонента, ответственного за цель
    if ($info['component']){
        cmsCore::loadModel($info['component']);
        if (class_exists('cms_model_'.$info['component'])){
			$model_class  = 'cms_model_'.$info['component'];
			$model = new $model_class();
            // если метод есть, пользуемся им
            if (method_exists($model, 'updateRatingHook')){
                $model->updateRatingHook($target, $item_id, $points);
            } else {
                if ($inDB->isFieldExists($info['target_table'], 'rating')){
                    $sql = "UPDATE {$info['target_table']}
                            SET rating = rating + ({$points})
                            WHERE id = '{$item_id}'";
                    $inDB->query($sql);
                }
            }
        }
    }

	return true;

}

function cmsKarmaFormat($points){
	if ($points==0) {
		$html = '<span style="color:silver;">0</span>';
	} elseif ($points>0){
		$html = '<span style="color:green;">+'.$points.'<span style="color:silver">&uarr;</span></span>';
	} else {
		$html = '<span style="color:red;">'.$points.'<span style="color:silver">&darr;</span></span>';
	}
	return $html;
}

function cmsKarmaFormatSmall($points){
	if ($points==0) {
		$html = '<span style="color:gray;">0</span>';
	} elseif ($points>0){
		$html = '<span style="color:green">+'.$points.'</span>';
	} else {
		$html = '<span style="color:red">'.$points.'</span>';
	}
	return $html;
}

function cmsKarmaForm($target, $target_id, $points, $is_author = false){

    $inUser = cmsUser::getInstance();
    $inPage = cmsPage::getInstance();
	$html   = '';

    global $_LANG;

	$postkarma = cmsKarma($target, $target_id);

	$points = cmsKarmaFormat($postkarma['points']);

	$control = '';

	//PREPARE RATING FORM
	if ($inUser->id && !$is_author){
		if(!cmsAlreadyKarmed($target, $target_id, $inUser->id)){
			$inPage->addHeadJS('core/js/karma.js');
			$control .= '<div style="text-align:center;margin-top:10px;">';
				$control .= '<a href="javascript:void(0);" onclick="plusKarma(\''.$target.'\', \''.$target_id.'\')" title="'.$_LANG['LIKE'].'"><img src="/templates/'.TEMPLATE.'/images/icons/karma_up.png" border="0" alt="Карма+"/></a> ';
				$control .= '<a href="javascript:void(0);" onclick="minusKarma(\''.$target.'\', \''.$target_id.'\')" title="'.$_LANG['UNLIKE'].'"><img src="/templates/'.TEMPLATE.'/images/icons/karma_down.png" border="0" alt="Карма-"/></a>';
			$control .= '</div>';
		}
	}
	$html .= '<div class="karma_form">';
		$html .= '<div id="karmapoints" style="font-size:24px">'.$points.'</div>';
		$html .= '<div id="karmavotes">Голосов: '.$postkarma['votes'].'</div>';
		$html .= '<div id="karmactrl">'.$control.'</div>';
	$html .= '</div>';
	return $html;
}

function cmsKarmaButtons($target, $target_id, $points = 0, $is_author = false){

    $inUser = cmsUser::getInstance();
    $inPage = cmsPage::getInstance();
	$html    = '';
    $control = '';
    global $_LANG;

	if (!$points) {
        $postkarma = cmsKarma($target, $target_id);
        $points    = cmsKarmaFormat($postkarma['points']);
	} else {
		$points = $points;
	}

	//PREPARE RATING FORM
	if ($inUser->id && !$is_author){
		if(!cmsAlreadyKarmed($target, $target_id, $inUser->id)){
			$inPage->addHeadJS('core/js/karma.js');

			$control .= '<div style="text-align:center">';
				$control .= '<a href="javascript:void(0);" onclick="plusKarma(\''.$target.'\', '.$target_id.');" title="'.$_LANG['LIKE'].'"><img src="/templates/'.TEMPLATE.'/images/icons/karma_up.png" border="0" alt="Карма+"/></a> ';
				$control .= '<a href="javascript:void(0);" onclick="minusKarma(\''.$target.'\', '.$target_id.');" title="'.$_LANG['UNLIKE'].'"><img src="/templates/'.TEMPLATE.'/images/icons/karma_down.png" border="0" alt="Карма-"/></a>';
			$control .= '</div>';
		}
	}

    if ($control){
        $html .= '<div class="karma_buttons">';
            $html .= '<div id="karmactrl">'.$control.'</div>';
        $html .= '</div>';
    }

	return $html;

}
function cmsKarmaButtonsText($target, $target_id, $points = 0, $is_author = false){

    $inUser = cmsUser::getInstance();
    $inPage = cmsPage::getInstance();
	$html = '';

	if (!$points) {
	$postkarma = cmsKarma($target, $target_id);
	$points = cmsKarmaFormat($postkarma['points']);
	} else {
		$points    = $points;
	}

	$control = '';
	//PREPARE RATING FORM
	if ($inUser->id && !$is_author){
		if(!cmsAlreadyKarmed($target, $target_id, $inUser->id)){
			$inPage->addHeadJS('core/js/karma.js');
			$control .= '<span>';
				$control .= '<a href="javascript:void(0);" onclick="plusKarma(\''.$target.'\', '.$target_id.');" style="color:green">Нравится</a> &uarr; ';
				$control .= '<a href="javascript:void(0);" onclick="minusKarma(\''.$target.'\', '.$target_id.');" style="color:red">Не нравится</a> &darr;';
			$control .= '</span>';
			$html .= '<span class="karma_buttons">';
					$html .= '<span id="karmactrl">'.$control.'</span>';
				$html .= '</span>';
		}
	}
	return $html;
}
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

function cmsKarma($target, $item_id){

    if (!preg_match('/^([a-zA-Z0-9\_]+)$/ui', $target)) { return; }

    $item_id = intval($item_id);

    $inDB = cmsDatabase::getInstance();

    $item = $inDB->get_fields('cms_ratings_total', "item_id = '$item_id' AND target='$target'", 'total_rating, total_votes');

	if (!$item){ return array('points'=>0, 'votes'=>0); }

	return array('points'=>$item['total_rating'], 'votes'=>$item['total_votes']);

}

function cmsAlreadyKarmed($target, $item_id, $user_id){
	return cmsDatabase::getInstance()->rows_count('cms_ratings', "target='$target' AND item_id = '$item_id' AND user_id = '$user_id'");
}

function cmsAlreadyKarmedIP($target, $item_id, $ip){
	return cmsDatabase::getInstance()->rows_count('cms_ratings', "target='$target' AND item_id = '$item_id' AND ip = '$ip'");
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
		$html = '<span class="color_gray">0</span>';
	} elseif ($points>0){
		$html = '<span class="color_green color_transition"><i class="fa fa-thumbs-up fa-lg"></i> +'.$points.'</span>';
	} else {
		$html = '<span class="color_red color_transition"><i class="fa fa-thumbs-down fa-lg"></i> '.$points.'</span>';
	}
	return $html;
}

function cmsKarmaFormatSmall($points){
	if ($points==0) {
		$html = '<span class="color_gray">0</span>';
	} elseif ($points>0){
		$html = '<span class="color_green">+'.$points.'</span>';
	} else {
		$html = '<span class="color_red">'.$points.'</span>';
	}
	return $html;
}

function cmsKarmaForm($target, $target_id, $points, $is_author = false){

    $inUser = cmsUser::getInstance();
    $inPage = cmsPage::getInstance();

    global $_LANG;

	$postkarma = cmsKarma($target, $target_id);

	$points = cmsKarmaFormat($postkarma['points']);

	$control = '';

	if ($inUser->id && !$is_author){

		if(!cmsAlreadyKarmed($target, $target_id, $inUser->id)){

			$inPage->addHeadJS('core/js/karma.js');

			$control .= '<div class="ratings_control">';
            $control .= '<a class="color_green color_transition" href="#" onclick="return plusKarma(\''.$target.'\', \''.$target_id.'\')" title="'.$_LANG['LIKE'].'"><i class="fa fa-thumbs-up fa-lg"></i></a> ';
            $control .= '<a class="color_red color_transition" href="#" onclick="return minusKarma(\''.$target.'\', \''.$target_id.'\')" title="'.$_LANG['UNLIKE'].'"><i class="fa fa-thumbs-down fa-lg"></i></a>';
			$control .= '</div>';

		}

	}

	return '<div class="karma_form">'.
           '<div id="karmapoints">'.$points.'</div>'.
		   '<div id="karmavotes">'.$_LANG['RATING_VOTES_COUNT'].': '.$postkarma['votes'].'</div>'.
		   '<div id="karmactrl">'.$control.'</div></div>';

}

function cmsKarmaButtons($target, $target_id, $points = 0, $is_author = false){

    $inUser = cmsUser::getInstance();
    $inPage = cmsPage::getInstance();
	$html    = '';

    global $_LANG;

	if ($inUser->id && !$is_author){

		if(!cmsAlreadyKarmed($target, $target_id, $inUser->id)){

			$inPage->addHeadJS('core/js/karma.js');

            return '<div class="karma_buttons"><div id="karmactrl">'.
                   '<div class="ratings_control"><a class="color_green color_transition" href="#" onclick="return plusKarma(\''.$target.'\', \''.$target_id.'\')" title="'.$_LANG['LIKE'].'"><i class="fa fa-thumbs-up fa-lg"></i></a> <a class="color_red color_transition" href="#" onclick="return minusKarma(\''.$target.'\', \''.$target_id.'\')" title="'.$_LANG['UNLIKE'].'"><i class="fa fa-thumbs-down fa-lg"></i></a></div>'.
                   '</div></div>';

		}

	}

	return '';

}
function cmsKarmaButtonsText($target, $target_id, $points = 0, $is_author = false){

    $inUser = cmsUser::getInstance();
    $inPage = cmsPage::getInstance();
	$html = '';

	if ($inUser->id && !$is_author){

		if(!cmsAlreadyKarmed($target, $target_id, $inUser->id)){

            global $_LANG;

			$inPage->addHeadJS('core/js/karma.js');

            $control  = ' <span><a class="color_green color_transition" href="#" onclick="return plusKarma(\''.$target.'\', '.$target_id.');"><i class="fa fa-thumbs-up fa-lg"></i> '.$_LANG['LIKE'].'</a> ';
            $control .= '<a class="color_red color_transition" href="#" onclick="return minusKarma(\''.$target.'\', '.$target_id.');"><i class="fa fa-thumbs-down fa-lg"></i> '.$_LANG['UNLIKE'].'</a></span>';

			$html .= '<span class="karma_buttons">';
            $html .= '<span id="karmactrl">'.$control.'</span>';
            $html .= '</span>';

		}

	}

	return $html;

}
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

function mod_uc($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    cmsCore::loadModel('catalog');

    if(!in_array(@$cfg['sort'], array('rating','hits','pubdate'))){
        $cfg['sort'] = 'pubdate';
    }

    global $_LANG;

    if (@$cfg['cat_id']>0){

        if (!@$cfg['subs']){
            //select from category
            $catsql = ' AND i.category_id = '.$cfg['cat_id'];
        } else {
            //select from category and subcategories
            $rootcat  = $inDB->get_fields('cms_uc_cats', "id='{$cfg['cat_id']}'", 'NSLeft, NSRight');
            if(!$rootcat) { return false; }
            $catsql   = "AND (c.NSLeft >= {$rootcat['NSLeft']} AND c.NSRight <= {$rootcat['NSRight']})";
        }

    } else {
        $catsql = '';
    }

    $sql = "SELECT i.* , IFNULL(AVG( r.points ), 0) AS rating, c.view_type as viewtype
            FROM cms_uc_items i
            LEFT JOIN cms_uc_cats c ON c.id = i.category_id
            LEFT JOIN cms_uc_ratings r ON r.item_id = i.id
            WHERE i.published = 1 $catsql
            GROUP BY i.id
            ORDER BY {$cfg['sort']} DESC
            LIMIT ".$cfg['num'];

    $result = $inDB->query($sql);

    $items = array();

    if (!$inDB->num_rows($result)){ return false; }

    cmsCore::includeFile('components/catalog/includes/shopcore.php');

    if ($cfg['showtype']=='thumb'){
        while($item = $inDB->fetch_assoc($result)){
            if (mb_strlen($item['imageurl'])<4) {
                $item['imageurl'] = 'nopic.jpg';
            } elseif (!file_exists(PATH.'/images/catalog/small/'.$item['imageurl'])) {
                $item['imageurl'] = 'nopic.jpg';
            }
            if ($item['viewtype']=='shop'){
                $item['price'] = number_format(shopDiscountPrice($item['id'], $item['category_id'], $item['price']), 2, '.', ' ');
            }
            $items[] = 	$item;
        }
    }

    if ($cfg['showtype']=='list'){
        while($item = $inDB->fetch_assoc($result)){
            $item['fieldsdata'] = cmsCore::yamlToArray($item['fieldsdata']);
            $item['title'] = mb_substr($item['title'], 0, 40);

            for($f = 0; $f<$cfg['showf']; $f++){
                $item['fdata'][] = cms_model_catalog::getUCSearchLink($item['category_id'], $item['fieldsdata'][$f]);
            }

            if($cfg['sort']=='rating') {
                $item['key'] = '<a href="/catalog/item'.$item['id'].'.html" title="'.$_LANG['UC_MODULE_RATING'].': '.round($item['rating'], 2).'">'.cms_model_catalog::buildRating(round($item['rating'], 2)).'</a>';
            } elseif($cfg['sort']=='hits') {
                $item['key'] = $_LANG['UC_MODULE_VIEWS'].': <a href="/catalog/item'.$item['id'].'.html" title="'.$_LANG['UC_MODULE_VIEWS'].'">'.$item['hits'].'</a>';
            } else {
                $item['key'] = cmsCore::dateFormat($item['pubdate']);
            }

            if ($item['viewtype']=='shop'){
                $item['price'] = number_format(shopDiscountPrice($item['id'], $item['category_id'], $item['price']), 2, '.', ' ');
            }
            $items[] = $item;
        }
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('items', $items)->
            assign('cfg', $cfg)->
            display($cfg['tpl']);

    return true;

}
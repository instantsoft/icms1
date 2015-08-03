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

function mod_uc_random($mod, $cfg){

    $inDB = cmsDatabase::getInstance();

    if ($cfg['cat_id']>0){

        if (!$cfg['subs']){
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

    $sql = "SELECT i.*, c.title as category, c.view_type as viewtype
            FROM cms_uc_items i
            LEFT JOIN cms_uc_cats c ON c.id = i.category_id
            WHERE i.published = 1 ".$catsql."
            ORDER BY RAND()
            LIMIT ".$cfg['count'];

    $result = $inDB->query($sql) ;

    $items = array();
    $is_uc = false;

    if ($inDB->num_rows($result)){
        $is_uc = true;
        while ($item=$inDB->fetch_assoc($result)){
            if (mb_strlen($item['imageurl'])<4) {
                $item['imageurl'] = 'nopic.jpg';
            } elseif (!file_exists(PATH.'/images/catalog/small/'.$item['imageurl'])) {
                $item['imageurl'] = 'nopic.jpg';
            }

            if ($item['viewtype']=='shop'){
                cmsCore::includeFile('components/catalog/includes/shopcore.php');
                $item['price'] = number_format(shopDiscountPrice($item['id'], $item['category_id'], $item['price']), 2, '.', ' ');
            }

            $items[] = $item;
        }
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('items', $items)->
            assign('cfg', $cfg)->
            assign('is_uc', $is_uc)->
            display($cfg['tpl']);

    return true;

}
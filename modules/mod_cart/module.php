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

function mod_cart($mod, $cfg){

    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();
    cmsCore::includeFile('components/catalog/includes/shopcore.php');

    $sid   = session_id();
    $items = array();
    $total_summ = 0;

    $user_sql = $inUser->id ? "c.user_id='{$inUser->id}'" : "c.session_id='$sid'";

    $sql = "SELECT i.title, i.price, i.category_id, i.id, c.itemscount
            FROM cms_uc_cart c
            INNER JOIN cms_uc_items i ON i.id = c.item_id
            WHERE $user_sql";
    $result = $inDB->query($sql);
    $items_count = $inDB->num_rows($result);

    if($items_count){
        while($con = $inDB->fetch_assoc($result)){

            $price       = shopDiscountPrice($con['id'], $con['category_id'], $con['price']);
            $totalcost   =  $con['itemscount']*$price;
            $total_summ += $totalcost;

            $con['price']     = number_format($price, 2, '.', ' ');
            $con['totalcost'] = number_format($totalcost, 2, '.', ' ');

            $items[] = $con;
        }
    }

    cmsPage::initTemplate('modules', $cfg['tpl'])->
            assign('cfg', $cfg)->
            assign('items_count', $items_count)->
            assign('total_summ', number_format($total_summ, 2, '.', ' '))->
            assign('items', $items)->
            display($cfg['tpl']);

    return true;

}
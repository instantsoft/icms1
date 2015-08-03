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

function isNew($item_id, $shownew, $newint){
    $inDB = cmsDatabase::getInstance();
    if ($shownew){
        $sql = "SELECT id FROM cms_uc_items WHERE id = $item_id AND pubdate >= DATE_SUB(NOW(), INTERVAL $newint)";
        $result = $inDB->query($sql) ;
        return $inDB->num_rows($result);
    } else { return 0; }
}

function getAlphaList($cat_id){
    $inDB = cmsDatabase::getInstance();
    global $_LANG;
    $html = '';
    $sql = "SELECT UPPER(SUBSTRING(LTRIM( title ) , 1, 1)) AS first_letter, COUNT( id ) AS num
            FROM cms_uc_items
            WHERE category_id = '$cat_id' AND published = 1
            GROUP BY first_letter";
    $result = $inDB->query($sql) ;
    if ($inDB->num_rows($result)){
        $html .= '<div class="uc_alpha_list">';
        while($a = $inDB->fetch_assoc($result)){
			if(preg_match('/^([a-zA-Zа-яёіїєґА-ЯЁІЇЄҐ0-9]+)$/ui', $a['first_letter'])){
            	$html .= '<a class="uc_alpha_link" href="/catalog/'.$cat_id.'/find-first/'.urlencode($a['first_letter']).'" title="'.$_LANG['ARTICLES'].': '.$a['num'].'">'.$a['first_letter'].'</a>';
			}
        }
        $html .= '</div>';
    }
    return $html;
}

function ratingData($item_id){
    $inDB = cmsDatabase::getInstance();
    $sql = "SELECT *, IFNULL(AVG(points), 0) as rating, COUNT(id) as votes
            FROM cms_uc_ratings
            WHERE item_id = $item_id
            GROUP BY item_id";
    $result = $inDB->query($sql) ;
    if ($inDB->num_rows($result)){
        $data = $inDB->fetch_assoc($result);
    } else {
        $data['rating'] = 0;
        $data['votes'] = 0;
    }
    return $data;
}

function alreadyVoted($item_id){
    $inDB   = cmsDatabase::getInstance();
    $ip     = $_SERVER['REMOTE_ADDR'];
    $sql    = "SELECT points FROM cms_uc_ratings WHERE item_id = $item_id AND ip = '$ip' LIMIT 1";
    $result = $inDB->query($sql) ;
    if ($inDB->num_rows($result)){
        $data = $inDB->fetch_assoc($result);
        return (int)$data['points'];
    }
    return false;
}

function ratingForm($ratingdata, $item_id){
    global $_LANG;
    $html = '';
    $html .= '<form name="rateform" action="" method="POST"><div class="uc_detailrating"><table><tr>' ."\n";
    $html .= '<td width="100">'."\n";
    $html .= '<strong>'.$_LANG['RATING'].':</strong> '.round($ratingdata['rating'], 2)."\n";
    $html .= '</td>'."\n";
    $html .= '<td width="100" valign="middle">'."\n";
    $html .= cms_model_catalog::buildRating($ratingdata['rating'])."\n";
    $html .= '</td>'."\n";
    $html .= '<td width="65">'."\n";
    $html .= '<strong>'.$_LANG['VOTES'].':</strong> '."\n";
    $html .= '</td>'."\n";
    $html .= '<td width="40" valign="middle">'."\n";
    $html .= $ratingdata['votes']."\n";
    $html .= '</td>'."\n";
    $html .= '<td width="100">'."\n";
    $html .= '<strong>'.$_LANG['YOUR_VOTE'].':</strong> '."\n";
    $html .= '</td>'."\n";
    $html .= '<td width=""> '."\n";
    $myvote = alreadyVoted($item_id);
    if (!$myvote){
        $html .= '<input type="hidden" name="rating" value="1"/>'."\n";
        $html .= '<input type="hidden" name="item_id" value="'.$item_id.'"/>'."\n";
        $html .= '<select name="points" style="width:50px" onchange="document.rateform.submit();">'."\n";
        $html .= '<option value="-1"> -- </option>'."\n";
        for($p=1; $p<=5; $p++) { $html .= '<option value="'.$p.'">'.$p.'</option>'."\n"; }
        $html .= '</select>'."\n";
    } else {
        $html .= $myvote;
    }
    $html .= '</td>'."\n";
    $html .= '</tr></table></div></form>'."\n";
    return $html;
}

function orderForm($orderby, $orderto, $shop=false){
    global $_LANG;
    $html = '';
    $html .= '<form action="'.htmlspecialchars($_SERVER['REQUEST_URI']).'" method="POST"><div class="catalog_sortform"><table cellspacing="2" cellpadding="2" >' ."\n";
    $html .= '<tr>' ."\n";
    $html .= '<td>'.$_LANG['ORDER_ARTICLES'].': </td>' ."\n";
    $html .= '<td valign="top"><select name="orderby" id="orderby">' ."\n";
    if($shop){
        $html .= '<option value="price" '; if($orderby=='price') { $html .= 'selected'; } $html .= '>'.$_LANG['ORDERBY_PRICE'].'</option>' ."\n";
    }
    $html .= '<option value="title" '; if($orderby=='title') { $html .= 'selected'; } $html .= '>'.$_LANG['ORDERBY_TITLE'].'</option>' ."\n";
    $html .= '<option value="pubdate" '; if($orderby=='pubdate') { $html .= 'selected'; } $html .= '>'.$_LANG['ORDERBY_DATE'].'</option>' ."\n";
    $html .= '<option value="rating" '; if($orderby=='rating') { $html .= 'selected'; } $html .= '>'.$_LANG['ORDERBY_RATING'].'</option>' ."\n";
    $html .= '<option value="hits" '; if($orderby=='hits') { $html .= 'selected'; } $html .= '>'.$_LANG['ORDERBY_HITS'].'</option>' ."\n";
    $html .= '</select> <select name="orderto" id="orderto">';
    $html .= '<option value="desc" '; if($orderto=='desc') { $html .= 'selected'; } $html .= '>'.$_LANG['ORDERBY_DESC'].'</option>' ."\n";
    $html .= '<option value="asc" '; if($orderto=='asc') { $html .= 'selected'; } $html .= '>'.$_LANG['ORDERBY_ASC'].'</option>' ."\n";
    $html .= '</select>';
    $html .= ' <input type="submit" value=">>" />' ."\n";
    $html .= '</td>' ."\n";
    $html .= '</tr>' ."\n";
    $html .= '</table></div></form>' ."\n";
    return $html;
}

function tagsList($cat_id){
    $inDB = cmsDatabase::getInstance();
    $html = '';
    $sql = "SELECT t.*, COUNT(t.tag) as num, c.id as cat_id
                FROM cms_tags t, cms_uc_items i, cms_uc_cats c
                WHERE t.target='catalog' AND t.item_id = i.id AND i.category_id = c.id AND c.id = $cat_id
                GROUP BY t.tag
                ORDER BY t.tag";
    $result = $inDB->query($sql) ;
    if ($inDB->num_rows($result)>0){
        while($tag = $inDB->fetch_assoc($result)){
            $html .= '<a href="#" onclick="addTag(\''.mb_strtolower($tag['tag']).'\')">'.mb_strtolower($tag['tag']).'</a> ('.$tag['num'].') ';
        }
    }
    return $html;
}

function tagLine($tagstr, $cat_id){
    $html = '';
    if (!$tagstr) { return ''; }
    $tagstr = str_replace(', ', ',', $tagstr);
    $tagstr = str_replace(' ,', ',', $tagstr);
    $tags = explode(',', $tagstr);
    $num = 0;
    foreach($tags as $key=>$value){
        $value = mb_strtolower($value);
        $html .= '<a href="/catalog/'.$cat_id.'/tag/'.urlencode($value).'">'.$value.'</a>';
        if ($num < sizeof($tags)-1) { $html .= ', '; $num++; }
    }
    return $html;
}

function getContentCount($cat_id, &$total, $inDB){

    $sql = "SELECT c.*, IFNULL(COUNT(i.id), 0) as content_count
            FROM cms_uc_cats c
            LEFT JOIN cms_uc_items i ON i.category_id = c.id AND i.published = 1
            WHERE (c.parent_id = {$cat_id}) AND c.published = 1
            GROUP BY i.category_id
            ORDER BY c.title";

    $result = $inDB->query($sql);

    if ( !$inDB->num_rows($result)>0 ){ return ''; }

    while($cat = $inDB->fetch_assoc($result)){
        $total   += $cat['content_count'];
        getContentCount($cat['id'], $total, $inDB);
    }

    return ;

}

function subCatsList($parent_id=0, $left_key=0, $right_key=0){

    $inCore = cmsCore::getInstance();
    $inDB   = cmsDatabase::getInstance();

    $html   = '';
    $model  = new cms_model_catalog();

    if (!$parent_id) { $parent_id = $inDB->get_field('cms_uc_cats', 'parent_id=0', 'id'); }

    $cats = $model->getSubCats($parent_id, $left_key, $right_key);

    if ($cats){

        ob_start();

        cmsPage::initTemplate('components', 'com_catalog_cats')->
                assign('cfg', $inCore->loadComponentConfig('catalog'))->
                assign('cats', $cats)->
                display('com_catalog_cats.tpl');

        $html = ob_get_clean();
    }

    return $html;

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function catalog(){

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

    global $_LANG;

    $model = new cms_model_catalog();

    define('IS_BILLING', $inCore->isComponentInstalled('billing'));
    if (IS_BILLING) { cmsCore::loadClass('billing'); }

    $pagetitle = $inCore->getComponentTitle();

	$inPage->addPathway($pagetitle, '/catalog');
	$inPage->setTitle($pagetitle);

	$inPage->setDescription($model->config['meta_desc'] ? $model->config['meta_desc'] : $pagetitle);
    $inPage->setKeywords($model->config['meta_keys'] ? $model->config['meta_keys'] : $pagetitle);

    $cfg = $inCore->loadComponentConfig('catalog');

    if (cmsCore::inRequest('cat_id')){
        $id = cmsCore::request('cat_id', 'int', 0);
    } else {
        $id = cmsCore::request('id', 'int', 0);
    }

    $do = $inCore->do;

    cmsCore::includeFile('components/catalog/includes/shopcore.php');

    //////////////////////////// RATING SUBMISSION ///////////////////////////////////////////////////////////////////
    if (cmsCore::inRequest('rating')){
        $points     = cmsCore::request('points', 'int', 0);
        $item_id    = cmsCore::request('item_id', 'int', 0);
        $ip         = $inUser->ip;
        if (!alreadyVoted($item_id)){
            $inDB->query("INSERT INTO cms_uc_ratings (item_id, points, ip) VALUES ($item_id, $points, '$ip')") ;
            $inDB->query("DELETE FROM cms_uc_ratings WHERE item_id = $item_id AND ip = '0.0.0.0'") ;
        }
    }

    //////////////////////////// SEARCH BY TAG ///////////////////////////////////////////////////////////////////////
    if ($do == 'tag') {

		$tag = $inCore->strClear(urldecode($inCore->request('tag', 'html', '')));

        $sql = "SELECT tag FROM cms_tags WHERE tag = '$tag' AND target='catalog' LIMIT 1";
        $result = $inDB->query($sql) ;
        if ($inDB->num_rows($result)==1){
            $item = $inDB->fetch_assoc($result);
            $query = $inCore->strClear($item['tag']);
            $findsql = "SELECT *
                        FROM cms_uc_items
                        WHERE category_id = '$id' AND published = 1 AND tags LIKE '%$query%'";
            $do = 'cat';
        } else { echo $_LANG['NO_MATCHING_FOUND']; }

    }
    //////////////////////////// ADVANCED SEARCH ////////////////////////////////////////////////////////////////////
    if ($do == 'search') {

        if (cmsCore::inRequest('gosearch')){

            $fdata = cmsCore::request('fdata', 'array', array());
            $query = cmsCore::strClear(implode('%', $fdata));
            $title = cmsCore::request('title', 'str', '');
            $tags  = cmsCore::request('tags', 'str', '');

            if ($query || $title || $tags){

                $findsql = "SELECT i.* , IFNULL(AVG(r.points),0) AS rating
                            FROM cms_uc_items i
                            LEFT JOIN cms_uc_ratings r ON r.item_id = i.id
                            WHERE i.published = 1 AND i.category_id = '$id' ";

                if($query){
                    $findsql .= " AND i.fieldsdata LIKE '%{$query}%' ";
                }
                if($title){
                    $findsql .= " AND i.title LIKE '%$title%' ";
                }
                if($tags){
                    $findsql .= "AND (i.tags LIKE '%".$tags."%')";
                }

                $findsql .=	" GROUP BY i.id";
                $advsearch = 1;
            }
            $do = 'cat';
        } else {
            //show search form
            $sql = "SELECT * FROM cms_uc_cats WHERE id = '$id'";
            $result = $inDB->query($sql) ;

            if ($inDB->num_rows($result)==1){
                $cat = $inDB->fetch_assoc($result);
                $fstruct = cmsCore::yamlToArray($cat['fieldsstruct']);

                //heading
                $inPage->addPathway($cat['title'], '/catalog/'.$cat['id']);
                $inPage->addPathway($_LANG['SEARCH'], '/catalog/'.$cat['id'].'/search.html');
                $inPage->setTitle($_LANG['SEARCH_IN_CAT']);

                $inPage->addHeadJS('components/catalog/js/search.js');

                $fstruct_ready = array();
                foreach($fstruct as $key=>$value) {
                    if (mb_strstr($value, '/~h~/')) { $ftype = 'html'; $value=str_replace('/~h~/', '', $value); }
                    elseif (mb_strstr($value, '/~l~/')) { $ftype = 'link'; $value=str_replace('/~l~/', '', $value); } else { $ftype='text'; }
                    if (mb_strstr($value, '/~m~/')) {
                        $value = str_replace('/~m~/', '', $value);
                    }
                    $fstruct_ready[stripslashes($key)] = stripslashes($value);
                }

                //searchform
                cmsPage::initTemplate('components', 'com_catalog_search')->
                        assign('id', $id)->
                        assign('cat', $cat)->
                        assign('fstruct', $fstruct_ready)->
                        display('com_catalog_search.tpl');

            } else { cmsCore::error404(); }
        }//search form

    }
    //////////////////////////// SEARCH BY FIRST LETTER OF TITLE ///////////////////////////////////////////////////////
    if ($do == 'findfirst') {

        $id = cmsCore::request('cat_id', 'int');

		$query = mb_substr(cmsCore::strClear(urldecode(cmsCore::request('text', 'html', ''))), 0, 1);

        $findsql = "SELECT i.* , IFNULL(AVG( r.points ),0) AS rating
                    FROM cms_uc_items i
                    LEFT JOIN cms_uc_ratings r ON r.item_id = i.id
                    WHERE i.published = 1 AND i.category_id = $id AND UPPER(LTRIM(i.title)) LIKE UPPER('$query%')
                    GROUP BY i.id";

        $do = 'cat';
        $advsearch = 0;

        $pagemode = 'findfirst';

    }

    //////////////////////////// SEARCH BY FIELD ////////////////////////////////////////////////////////////////////
    if ($do == 'find') {

        $id = cmsCore::request('cat_id', 'int');

        $query = cmsCore::strClear(urldecode(cmsCore::request('text', 'html', '')));

        $findsql = "SELECT i.* , IFNULL(AVG(r.points),0) AS rating
                    FROM cms_uc_items i
                    LEFT JOIN cms_uc_ratings r ON r.item_id = i.id
                    WHERE i.published = 1 AND i.category_id = $id AND i.fieldsdata LIKE '%$query%'
                    GROUP BY i.id";

        $do = 'cat';
        $advsearch = 0;

		$query = stripslashes($query);

        $pagemode = 'find';
    }

    //////////////////////////// LIST OF CATEGORIES ////////////////////////////////////////////////////////////////////
    if ($do == 'view'){ //List of all categories

        $cats_html = subCatsList();
        $inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.$_LANG['CATALOG'].'" href="'.HOST.'/rss/catalog/all/feed.rss">');
        cmsPage::initTemplate('components', 'com_catalog_index')->
                assign('cfg', $cfg)->
                assign('title', $pagetitle)->
                assign('cats_html', $cats_html)->
                display('com_catalog_index.tpl');

    }

    //////////////////////////// VIEW CATEGORY ///////////////////////////////////////////////////////////////////////
    if ($do == 'cat'){

        //get category data
        $sql = "SELECT * FROM cms_uc_cats WHERE id = $id";
        $catres = $inDB->query($sql);
        if (!$inDB->num_rows($catres)){ cmsCore::error404(); }

        $cat     = $inDB->fetch_assoc($catres);
        $fstruct = cmsCore::yamlToArray($cat['fieldsstruct']);

        $inPage->addHead('<link rel="alternate" type="application/rss+xml" title="'.$_LANG['CATALOG'].'" href="'.HOST.'/rss/catalog/'.$cat['id'].'/feed.rss">');

        //heading
        //PATHWAY ENTRY
        $path_list = $model->getCategoryPath($cat['NSLeft'], $cat['NSRight']);
        if ($path_list){
            foreach($path_list as $pcat){
                $inPage->addPathway($pcat['title'], '/catalog/'.$pcat['id']);
            }
        }
        $inPage->setTitle($cat['pagetitle'] ? $cat['pagetitle'] : $cat['title']);

        //subcategories
        $subcats = subCatsList($cat['id'], $cat['NSLeft'], $cat['NSRight']);

        //alphabetic list
        if ($cat['showabc']){ $alphabet = getAlphaList($cat['id']);	} else { $alphabet = ''; }

        //Tool links
        $shopcartlink = shopCartLink();

        //get items SQL
        if (!isset($findsql)){
            $sql = "SELECT i.* , IFNULL(AVG( r.points ), 0) AS rating, i.price as price
                    FROM cms_uc_items i
                    LEFT JOIN cms_uc_ratings r ON r.item_id = i.id
                    WHERE i.published = 1 AND i.category_id = $id
                    GROUP BY i.id";
        } else {
            $sql = $findsql;
            if (!$advsearch){ $inPage->addPathway(icms_ucfirst($query)); } else
            { $inPage->addPathway($_LANG['SEARCH_RESULT']); }
        }

        // сортировка
        if(cmsCore::inRequest('orderby')){
            $orderby = cmsCore::request('orderby', array('hits','rating','pubdate','title','price'), $cat['orderby']);
            cmsUser::sessionPut('uc_orderby', $orderby);
        } elseif(cmsUser::sessionGet('uc_orderby')){
            $orderby = cmsUser::sessionGet('uc_orderby');
        } else {
            $orderby = $cat['orderby'];
        }
        if(cmsCore::inRequest('orderto')){
            $orderto = cmsCore::request('orderto', array('asc','desc'), $cat['orderto']);
            cmsUser::sessionPut('uc_orderto', $orderto);
        } elseif(cmsUser::sessionGet('uc_orderto')){
            $orderto = cmsUser::sessionGet('uc_orderto');
        } else {
            $orderto = $cat['orderto'];
        }

        $sql .=  " ORDER BY ".$orderby." ".$orderto;

        //get total items count
        $result = $inDB->query($sql);
        $itemscount = $inDB->num_rows($result);

        //can user add items here?
        $is_cat_access = $model->checkCategoryAccess($cat['id'], $cat['is_public'], $inUser->group_id);
        $is_can_add = $is_cat_access || $inUser->is_admin;

        $tpl = cmsPage::initTemplate('components', 'com_catalog_view')->
                assign('id', $id)->
                assign('cat', $cat)->
                assign('subcats', $subcats)->
                assign('alphabet', $alphabet)->
                assign('shopcartlink', $shopcartlink)->
                assign('itemscount', $itemscount)->
                assign('is_can_add', $is_can_add)->
                assign('orderform', orderForm($orderby, $orderto, ($cat['view_type']=='shop')));

        //pagination
        if (!@$advsearch) { $perpage = $cat['perpage']; } else { $perpage='100'; }
        $page = $inCore->request('page', 'int', 1);

        //request items using pagination
        $sql .= " LIMIT ".(($page-1)*$perpage).", $perpage";
        $result = $inDB->query($sql) ;

        //search details, if needed
        $search_details = '';
        if (isset($findsql)){
            if ($advsearch){
                $search_details = '<div class="uc_queryform"><strong>'.$_LANG['SEARCH_RESULT'].' - </strong> '.$_LANG['FOUNDED'].': '.$itemscount.' | <a href="/catalog/'.$cat['id'].'">'.$_LANG['CANCEL_SEARCH'].'</a></div>';
            } else {
                $search_details = '<div class="uc_queryform"><strong>'.$_LANG['SEARCH_BY_TAG'].'</strong> "'.htmlspecialchars(icms_ucfirst(stripslashes($query))).'" ('.$_LANG['MATCHES'].': '.$itemscount.') <a href="/catalog/'.$cat['id'].'">'.$_LANG['CANCEL_SEARCH'].'</a></div>';
            }
        }

        $items = array();
        while($item = $inDB->fetch_assoc($result)){
            $item['ratingdata'] = ratingData($item['id']);
            $item['fdata'] = cmsCore::yamlToArray($item['fieldsdata']);
            $item['price'] = number_format(shopDiscountPrice($item['id'], $item['category_id'], $item['price']), 2, '.', ' ');
            $item['rating'] = cms_model_catalog::buildRating($item['ratingdata']['rating']);
            $item['is_new'] = isNew($item['id'], $cat['shownew'], $cat['newint']);
            $item['tagline'] = tagLine($item['tags'], $cat['id']);

            $item['can_edit'] = ($cat['can_edit'] && $is_cat_access && ($inUser->id == $item['user_id'])) || $inUser->is_admin;

            $item['fields'] = array();

            if (sizeof($fstruct)>0){
                $fields_show = 0;
                foreach($fstruct as $key=>$value){
                    if ($fields_show < $cat['fields_show']){

                        if ($item['fdata'][$key]){

                            if (mb_strstr($value, '/~h~/')){ $value = str_replace('/~h~/', '', $value); $is_html = true; } else { $is_html = false; }
                            if (mb_strstr($value, '/~m~/')){
                                $value = str_replace('/~m~/', '', $value);
                                $makelink = true;
                            } else {$makelink = false; }
                            if (!$is_html){
                                if (mb_strstr($value, '/~l~/')){
                                    if (@$item['fdata'][$key]!=''){
                                        $field = '<a class="uc_fieldlink" href="/load/url=-'.base64_encode($item['fdata'][$key]).'" target="_blank">'.str_replace('/~l~/', '', $value).'</a> ('.$inCore->fileDownloadCount($item['fdata'][$key]).')';
                                    }
                                } else {
                                    if ($makelink){
                                        $field = $model->getUCSearchLink($cat['id'], $item['fdata'][$key]);
                                    } else {
                                        $field = $item['fdata'][$key];
                                    }
                                }
                            } else {
                                $field = $item['fdata'][$key];
                            }

                            if (isset($query)) { if (mb_stristr($field, $query)) { $field .= '<span class="uc_findsame"> &larr; <i>'.$_LANG['MATCHE'].'</i></span>';} }
                            $fields_show++;

                            $item['fields'][stripslashes($value)] = stripslashes($field);

                        }

                    } else { break; }
                }
            }

            $items[] = $item;
        }

        if (!@$pagemode){
            $pagebar = cmsPage::getPagebar($itemscount, $page, $perpage, '/catalog/'.$id.'-%page%');
        } else {

            if ($pagemode=='findfirst'){
                $pagebar = cmsPage::getPagebar($itemscount, $page, $perpage, '/catalog/'.$id.'-%page%/find-first/'.urlencode(urlencode($query)));
            }

            if ($pagemode=='find'){
                $pagebar = cmsPage::getPagebar($itemscount, $page, $perpage, '/catalog/'.$id.'-%page%/find/'.urlencode(urlencode($query)));
            }

        }

        // SEO
        if($cat['NSLevel'] > 0){

            // meta description
            if($cat['meta_desc']){
                $meta_desc = $cat['meta_desc'];
            } elseif(mb_strlen(strip_tags($cat['description']))>=250){
                $meta_desc = crop($cat['description']);
            } else {
                $meta_desc = $cat['title'];
            }
            $inPage->setDescription($meta_desc);
            // meta keywords
            if($cat['meta_keys']){
                $meta_keys = $cat['meta_keys'];
            } elseif($items){
                foreach($items as $c){
                    $k[] = $c['title'];
                }
                $meta_keys = implode(', ', $k);
            } else {
                $meta_keys = $cat['title'];
            }
            $inPage->setKeywords($meta_keys);

        }

        $tpl->assign('cfg', $cfg)->
              assign('page', $page)->
              assign('search_details', $search_details)->
              assign('fstruct', $fstruct)->
              assign('items', $items)->
              assign('pagebar', $pagebar)->
              display('com_catalog_view.tpl');

        return true;

    }

    //////////////////////////// VIEW ITEM DETAILS ///////////////////////////////////////////////////////////////////////
    if ($do == 'item'){

        $id  = $inCore->request('id', 'int');
        $sql = "SELECT * FROM cms_uc_items WHERE id = '$id'";
        $itemres = $inDB->query($sql) ;

        if (!$inDB->num_rows($itemres)){ cmsCore::error404(); }

        $item = $inDB->fetch_assoc($itemres);

        if ((!$item['published'] || $item['on_moderate']) && !$inUser->is_admin){
            cmsCore::error404();
        }

        $fdata = cmsCore::yamlToArray($item['fieldsdata']);

        if ($item['meta_keys']) { $inPage->setKeywords($item['meta_keys']); }
        if ($item['meta_desc']) { $inPage->setDescription($item['meta_desc']); }

        $ratingdata = ratingData($id);

        $sql = "SELECT * FROM cms_uc_cats WHERE id = '{$item['category_id']}'";
        $catres = $inDB->query($sql) ;
        $cat = $inDB->fetch_assoc($catres);
        $fstruct = cmsCore::yamlToArray($cat['fieldsstruct']);

        $is_cat_access = $inUser->id ?
                            $model->checkCategoryAccess($cat['id'], $cat['is_public'], $inUser->group_id) : false;
        $item['can_edit'] = ($cat['can_edit'] && $is_cat_access && ($inUser->id == $item['user_id'])) || $inUser->is_admin;

        //PATHWAY ENTRY
        $path_list  = $model->getCategoryPath($cat['NSLeft'], $cat['NSRight']);

        if ($path_list){
            foreach($path_list as $pcat){
                $inPage->addPathway($pcat['title'], '/catalog/'.$pcat['id']);
            }
        }
        $inPage->addPathway($item['title'], '/catalog/item'.$item['id'].'.html');

        $inPage->setTitle($item['title']);


        if ($cat['view_type']=='shop'){

            $shopCartLink=shopCartLink();

        }

        //update hits
        $inDB->query("UPDATE cms_uc_items SET hits = hits + 1 WHERE id = '$id'") ;

        //print item details
        $fields = array();

        if (sizeof($fstruct)>0){
            foreach($fstruct as $key=>$value){
                if (@$fdata[$key]){
                    if (mb_strstr($value, '/~h~/')){
                        $value = str_replace('/~h~/', '', $value);
                        $htmlfield = true;
                    }
                    if (mb_strstr($value, '/~m~/')){
                        $value = str_replace('/~m~/', '', $value);
                        $makelink = true;
                    } else {$makelink = false; }
                    $field = (string)str_replace('<p>', '<p style="margin-top:0px; margin-bottom:5px">', $fdata[$key]);
                    if (mb_strstr($value, '/~l~/')){
                        $field = '<a class="uc_detaillink" href="/load/url=-'.base64_encode($field).'" target="_blank">'.str_replace('/~l~/', '', $value).'</a> ('.$inCore->fileDownloadCount($field).')';

                    } else {

                        if (isset($htmlfield)) {
                            if ($makelink) {
                                 $field = $model->getUCSearchLink($cat['id'], $field);
                            } else {
                                //PROCESS FILTERS, if neccessary
                                if ($cat['filters']){
                                    $filters = $inCore->getFilters();
                                    if ($filters){
                                        foreach($filters as $id=>$_data){
                                            require_once PATH.'/filters/'.$_data['link'].'/filter.php';
                                            $_data['link']($field);
                                        }
                                    }
                                }
                                $field =  stripslashes($field);
                            }
                        } else {
                            if ($makelink) {
                                 $field =  $model->getUCSearchLink($cat['id'], $field);
                            }
                        }

                    }
                    $fields[stripslashes($value)] = stripslashes($field);
                }
            }
        }
        if ($cat['view_type']=='shop'){
            $item['price'] = number_format(shopDiscountPrice($item['id'], $item['category_id'], $item['price']), 2, '.', ' ');
        }

        $user = $inDB->get_fields('cms_users', "id='{$item['user_id']}'", 'login, nickname');
        $getProfileLink = cmsUser::getProfileLink($user['login'], $user['nickname']);

        if ($cat['is_ratings']){
            $ratingForm = ratingForm($ratingdata, $item['id']);
        }

        cmsPage::initTemplate('components', 'com_catalog_item')->
                assign('shopCartLink', (isset($shopCartLink) ? $shopCartLink : ''))->
                assign('getProfileLink', $getProfileLink)->
                assign('tagline', tagLine($item['tags'], $cat['id']))->
                assign('item', $item)->
                assign('cat', $cat)->
                assign('fields', $fields)->
                assign('ratingForm', (isset($ratingForm) ? $ratingForm : ''))->
                display('com_catalog_item.tpl');

        if($item['is_comments'] && $inCore->isComponentInstalled('comments')){
            cmsCore::includeComments();
            comments('catalog', $item['id'], array(), ($inUser->id == $item['user_id']));
        }

        return true;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////// S H O P /////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////// ADD TO CART /////////////////////////////////////////////////////////////////////////////
    if ($do == 'addcart'){
        shopAddToCart($id, 1);
		$inCore->redirect('/catalog/viewcart.html');
    }
    ///////////////////////// VIEW CART /////////////////////////////////////////////////////////////////////////////
    if ($do == 'viewcart'){
        shopCart();
    }
    ///////////////////////// DELETE FROM CART /////////////////////////////////////////////////////////////////////////////
    if ($do == 'cartremove'){
        shopRemoveFromCart($id);
		$inCore->redirectBack();
    }
    ///////////////////////// CLEAR CART /////////////////////////////////////////////////////////////////////////////
    if ($do == 'clearcart'){
        shopClearCart();
        $inCore->redirectBack();
    }
    ///////////////////////// CLEAR CART /////////////////////////////////////////////////////////////////////////////
    if ($do == 'savecart'){
        $itemcounts =  $inCore->request('kolvo', 'array_int');
        if (is_array($itemcounts)){
            shopUpdateCart($itemcounts);
        }
        $inCore->redirectBack();
    }
    ///////////////////////// ORDER //////////////////////////////////////////////////////////////////////////////////
    if ($do == 'order'){
        shopOrder($cfg);
    }
    ///////////////////////// ORDER //////////////////////////////////////////////////////////////////////////////////
    if ($do == 'finish'){
        shopFinishOrder($cfg);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ($do == 'add_item' || $do == 'edit_item'){

        $cat_id  = cmsCore::request('cat_id', 'int');
        $item_id = cmsCore::request('item_id', 'int', 0);

        if ($do == 'add_item'){

            $cat = $inDB->get_fields('cms_uc_cats', "id='$cat_id'", '*');
            if (!$cat){ cmsCore::error404(); }

            $inPage->setTitle($_LANG['ADD_ITEM']);

            if (!($model->checkCategoryAccess($cat['id'], $cat['is_public'], $inUser->group_id) || $inUser->is_admin)){
                cmsCore::error404();
            }

            $item  = array();
            $fdata = array();

            if ($cat['cost']=='') { $cat['cost'] = false; }
            if (IS_BILLING){
                cmsBilling::checkBalance('catalog', 'add_catalog_item', false, $cat['cost']);
            }

            $item['is_comments'] = 1;

        }

        if ($do == 'edit_item'){

            $inPage->setTitle($_LANG['EDIT_ITEM']);

            $item = $inDB->get_fields('cms_uc_items', "id='$item_id'", '*');
            if (!$item) { cmsCore::error404(); }

            $cat = $inDB->get_fields('cms_uc_cats', "id='{$item['category_id']}'", '*');
            if (!$cat){ cmsCore::error404(); }

            $is_cat_access  = $model->checkCategoryAccess($cat['id'], $cat['is_public'], $inUser->group_id);
            $is_can_edit    = ($cat['can_edit'] && $is_cat_access && ($inUser->id == $item['user_id'])) || $inUser->is_admin;
            if (!$is_can_edit) { cmsCore::error404(); }

            $fdata = cmsCore::yamlToArray($item['fieldsdata']);

        }

        $path_list  = $model->getCategoryPath($cat['NSLeft'], $cat['NSRight']);
        if ($path_list){
            foreach($path_list as $pcat){
                $inPage->addPathway($pcat['title'], '/catalog/'.$pcat['id']);
            }
        }
        if($do == 'add_item'){
            $inPage->addPathway($_LANG['ADD_ITEM']);
        } else {
            $inPage->addPathway($_LANG['EDIT_ITEM']);
        }

		$cats = $inCore->getListItems('cms_uc_cats', $cat['id'], 'id', 'ASC', 'parent_id > 0 AND published = 1');

        $fields = array();

        $fstruct = cmsCore::yamlToArray($cat['fieldsstruct']);

        foreach($fstruct as $f_id=>$value){

            if (mb_strstr($value, '/~h~/')) { $ftype = 'html'; $value=str_replace('/~h~/', '', $value); }
            elseif (mb_strstr($value, '/~l~/')) { $ftype = 'link'; $value=str_replace('/~l~/', '', $value); } else { $ftype='text'; }

            if (mb_strstr($value, '/~m~/')) { $makelink = true; $value=str_replace('/~m~/', '', $value); }
            else { $makelink = false; }

            $next['ftype']    = stripslashes($ftype);
            $next['title']    = stripslashes($value);
            $next['makelink'] = stripslashes($makelink);

            if (!empty($fdata[$f_id])){
                $next['value']  = stripslashes($fdata[$f_id]);
            } else {
                $next['value']  = '';
            }

            $fields[$f_id] = $next;

        }

        cmsPage::initTemplate('components', 'com_catalog_add')->
                assign('do', $do)->
                assign('item', $item)->
                assign('fields', $fields)->
                assign('cat', $cat)->
                assign('cats', $cats)->
                assign('cfg', $cfg)->
                assign('is_admin', $inUser->is_admin)->
                assign('cat_id', $cat['id'])->
                display('com_catalog_add.tpl');

        return;

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ($do == 'submit_item'){

        $opt     = cmsCore::request('opt', 'str', 'add');
        $new_cat_id = cmsCore::request('new_cat_id', 'int', 0);
        $cat_id = $new_cat_id ? $new_cat_id : cmsCore::request('cat_id', 'int', 0);

        $item_id = cmsCore::request('item_id', 'int', 0);

        $cat = $inDB->get_fields('cms_uc_cats', "id='$cat_id'", '*');
        if(!$cat){ cmsCore::error404(); }

        if ($opt == 'add'){

            if(!$inUser->is_admin &&
                    !$model->checkCategoryAccess($cat['id'], $cat['is_public'], $inUser->group_id)){
                cmsCore::error404();
            }

        } else {

            $item = $inDB->get_fields('cms_uc_items', "id='{$item_id}'", '*');
            if(!$item){ cmsCore::error404(); }

            if(!$inUser->is_admin &&
                    !($cat['can_edit'] && ($inUser->id == $item['user_id']) &&
                        $model->checkCategoryAccess($cat['id'], $cat['is_public'], $inUser->group_id))){
                cmsCore::error404();
            }

        }

        $item['title'] = cmsCore::request('title', 'str');
        if (!$item['title']) { cmsCore::addSessionMessage($_LANG['NEED_TITLE'], 'error'); cmsCore::redirectBack(); }

        $item['category_id']    = $cat_id;
        $item['published']      = ($cfg['premod'] && !$inUser->is_admin ? 0 : 1);
        $item['on_moderate']    = ($cfg['premod'] && !$inUser->is_admin ? 1 : 0);

        $item['fdata']          = cmsCore::request('fdata', 'array', array());;
        foreach($item['fdata'] as $key=>$value) {
			$item['fdata'][$key] = cmsCore::badTagClear($value);
		}
        $item['fieldsdata']     = $inDB->escape_string(cmsCore::arrayToYaml($item['fdata']));

        $item['is_comments']    = $inUser->is_admin ? cmsCore::request('is_comments', 'int', 0) : $cfg['is_comments'];
        $item['tags']           = cmsCore::request('tags', 'str', '');
        $item['canmany']        = 1;
        $item['imageurl']       = ($opt == 'add' ? '' : $item['imageurl']);
        $item['price']          = 0;
        $item['canmany']        = 1;

        if($inUser->is_admin){
            $meta_desc = cmsCore::request('meta_desc', 'str', '');
            $meta_keys = cmsCore::request('meta_keys', 'str', '');
            $item['meta_desc']  = $meta_desc ? $meta_desc : $item['title'];
            $item['meta_keys']  = $meta_keys ? $meta_keys : $item['tags'];
        } else {
            $item['meta_desc']  = @$item['meta_desc'] ? $item['meta_desc'] : $item['title'];
            $item['meta_keys']  = @$item['meta_keys'] ? $item['meta_keys'] : $item['tags'];
        }

		if (cmsCore::inRequest('price')) {
			$price          = cmsCore::request('price', 'str', '');
			$price          = str_replace(',', '.', $price);
            $item['price']  = round($price, 2);
            $item['canmany']= cmsCore::request('canmany', 'int', 0);
		}

        if (cmsCore::request('delete_img', 'int', 0)){

            @unlink(PATH."/images/catalog/".$item['imageurl']);
            @unlink(PATH."/images/catalog/small/".$item['imageurl']);
            @unlink(PATH."/images/catalog/medium/".$item['imageurl']);

            $item['imageurl'] = '';

        }
        $file = $model->uploadPhoto($item['imageurl']);
        if($file){
            $item['imageurl'] = $file['filename'];
        }

        if ($opt=='add'){

            $item['pubdate'] = date('Y-m-d H:i');
            $item['user_id'] = $inUser->id;

            $item['id'] = $model->addItem($item);

            if (IS_BILLING){
                if ($cat['cost']=='') { $cat['cost'] = false; }
                cmsBilling::process('catalog', 'add_catalog_item', $cat['cost']);
            }

            if (!$cfg['premod'] || $inUser->is_admin) {

                cmsCore::callEvent('ADD_CATALOG_DONE', $item);

                //регистрируем событие
                cmsActions::log('add_catalog', array(
                    'object' => $item['title'],
                    'object_url' => '/catalog/item'.$item['id'].'.html',
                    'object_id' => $item['id'],
                    'target' => $cat['title'],
                    'target_url' => '/catalog/'.$cat['id'],
                    'target_id' => $cat['id'],
                    'description' => ''
                ));
            }
		}
        if ($opt=='edit'){
			$model->updateItem($item['id'], $item);
			cmsActions::updateLog('add_catalog', array('object' => $item['title']), $item['id']);
		}

        if ($inUser->id != 1 && $cfg['premod'] && $cfg['premod_msg']){

            $link = '<a href="/catalog/item'.$item['id'].'.html">'.$item['title'].'</a>';
            $user = '<a href="'.cmsUser::getProfileURL($inUser->login).'">'.$inUser->nickname.'</a>';

            if ($opt=='add')  { $message = $_LANG['MSG_ITEM_SUBMIT']; }
            if ($opt=='edit') { $message = $_LANG['MSG_ITEM_EDITED']; }
            $message = str_replace('%user%', $user, $message);
            $message = str_replace('%link%', $link, $message);

            cmsUser::sendMessage(USER_UPDATER, 1, $message);

            cmsCore::addSessionMessage($_LANG['ITEM_PREMOD_NOTICE'], 'info');

            cmsCore::redirect('/catalog/'.$item['category_id']);

        }

        cmsCore::redirect('/catalog/item'.$item['id'].'.html');

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ($do == 'accept_item'){

        $item_id = cmsCore::request('item_id', 'int');

        $item = $inDB->get_fields('cms_uc_items', "id='{$item_id}'", 'title, user_id, category_id');
        if (!$item || !$inUser->is_admin){ cmsCore::error404(); }

        $inDB->query("UPDATE cms_uc_items SET published=1, on_moderate=0 WHERE id='{$item_id}'");

		$cat = $inDB->get_fields('cms_uc_cats', 'id='.$item['category_id'], 'id, title');

        cmsCore::callEvent('ADD_CATALOG_DONE', $item);

		//регистрируем событие
		cmsActions::log('add_catalog', array(
				'object' => $item['title'],
				'user_id' => $item['user_id'],
				'object_url' => '/catalog/item'.$item_id.'.html',
				'object_id' => $item_id,
				'target' => $cat['title'],
				'target_url' => '/catalog/'.$cat['id'],
				'target_id' => $cat['id'],
				'description' => ''
		));

        $item_link  = '<a href="/catalog/item'.$item_id.'.html">'.$item['title'].'</a>';

        $message = str_replace('%link%', $item_link, $_LANG['MSG_ITEM_ACCEPTED']);

        cmsUser::sendMessage(USER_UPDATER, $item['user_id'], $message);

        cmsCore::redirectBack();

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ($do == 'delete_item'){

        $item_id = cmsCore::request('item_id', 'int');

        $item = $inDB->get_fields('cms_uc_items', "id='{$item_id}'", '*');
        if(!$item){ cmsCore::error404(); }

        if (!($item['user_id']==$inUser->id || $inUser->is_admin)){ cmsCore::error404(); }

        $model->deleteItem($item_id);

        $message = str_replace('%item%', $item['title'], $_LANG['MSG_ITEM_REJECTED']);
        cmsUser::sendMessage(USER_UPDATER, $item['user_id'], $message);

        cmsCore::redirect('/catalog/'.$item['category_id']);

    }

}
?>
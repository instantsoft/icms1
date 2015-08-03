<?php
if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }
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

function cpPriceInput($item){
	$inDB = cmsDatabase::getInstance();
	$sql = "SELECT view_type FROM cms_uc_cats WHERE id = '{$item['category_id']}'";
	$rs = $inDB->query($sql) ;
	$show = $inDB->fetch_assoc($rs);

	if ($show['view_type'] == 'shop'){
		$price = number_format($item['price'], 2, '.', '');
		$html  = '<input type="text" name="price['.$item['id'].']" value="'.$price.'" id="priceinput"/>';
	} else {
		$html = '&mdash;';
	}

	return $html;
}
//=================================================================================================//
cmsCore::loadModel('catalog');
$model = new cms_model_catalog();

$cfg = $inCore->loadComponentConfig('catalog');
$opt = cmsCore::request('opt', 'str', 'list_cats');

define('IS_BILLING', $inCore->isComponentInstalled('billing'));
if (IS_BILLING) { cmsCore::loadClass('billing'); }

$GLOBALS['cp_page_head'][] = '<script type="text/javascript" src="/admin/components/catalog/js/common.js"></script>';

echo '<script>';
echo cmsPage::getLangJS('AD_HOW_MANY_COPY');
echo '</script>';
//=================================================================================================//
//=================================================================================================//

$toolmenu = array();

if ($opt=='list_items' || $opt=='list_cats' || $opt=='list_discount'){

    $toolmenu[] = array('icon'=>'newfolder.gif', 'title'=>$_LANG['AD_NEW_CAT'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add_cat');
    $toolmenu[] = array('icon'=>'newstuff.gif', 'title'=>$_LANG['ADD_ITEM'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add_item');
    $toolmenu[] = array('icon'=>'newdiscount.gif', 'title'=>$_LANG['AD_NEW_COEFFICIENT'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add_discount');
    $toolmenu[] = array('icon'=>'folders.gif', 'title'=>$_LANG['AD_ALL_CAT'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_cats');
    $toolmenu[] = array('icon'=>'liststuff.gif', 'title'=>$_LANG['AD_ALL_ITEM'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_items');
    $toolmenu[] = array('icon'=>'listdiscount.gif', 'title'=>$_LANG['AD_ALL_COEFFICIENTS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=list_discount');
    $toolmenu[] = array('icon'=>'excel.gif', 'title'=>$_LANG['AD_MS_EXCEL_IMPORT'], 'link'=>'?view=components&do=config&id='.$id.'&opt=import_xls');
    if($opt == 'list_items'){

        $toolmenu[] = array('icon'=>'show.gif', 'title'=>$_LANG['AD_ALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=show_item&multiple=1');");
        $toolmenu[] = array('icon'=>'hide.gif', 'title'=>$_LANG['AD_DISALLOW_SELECTED'], 'link'=>"javascript:checkSel('?view=components&do=config&id=".$id."&opt=hide_item&multiple=1');");
        $toolmenu[] = array('icon'=>'saveprices.gif', 'title'=>$_LANG['AD_SAVE_COSTS'], 'link'=>"javascript:sendForm('index.php?view=components&do=config&id=".$id."&opt=saveprices');");

    }
    $toolmenu[] = array('icon'=>'config.gif', 'title'=>$_LANG['AD_SETTINGS'], 'link'=>'?view=components&do=config&id='.$id.'&opt=config');

} else {

    $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
    $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);

}

cpToolMenu($toolmenu);

//=================================================================================================//
//=================================================================================================//

if ($opt == 'go_import_xls'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $item['category_id'] = cmsCore::request('cat_id', 'int', 0);
    $item['user_id']     = cmsCore::request('user_id', 'int', 1);
    $item['published']   = cmsCore::request('published', 'int', 0);
    $item['is_comments'] = cmsCore::request('is_comments', 'int', 0);
    $item['tags']        = cmsCore::request('tags', 'str', '');
    $item['canmany']     = cmsCore::request('canmany', 'int', 0);
    $item['meta_keys']   = $item['tags'];
    $item['pubdate']     = date('Y-m-d H:i');
    $item['imageurl']    = '';

    $rows    = cmsCore::request('xlsrows', 'int', 0);
    $sheet   = cmsCore::request('xlslist', 'int', 1);
    $cells   = cmsCore::request('cells', 'array_str', array());
    $charset = cmsCore::request('charset', 'str', 'cp1251');

    if(empty($_FILES['xlsfile']['name'])){
        cmsCore::addSessionMessage($_LANG['AD_NO_LOAD_EXCEL_FILE'], 'error');
        cmsCore::redirectBack();
    }

    $xls_file = PATH.'/upload/'. md5(microtime().uniqid()). '.xls';
    if(!cmsCore::moveUploadedFile($_FILES['xlsfile']['tmp_name'], $xls_file, $_FILES['xlsfile']['error'])){
        cmsCore::addSessionMessage($_LANG['AD_NO_LOAD_EXCEL_FILE'], 'error');
        cmsCore::redirectBack();
    }

    $file = $model->uploadPhoto();
    if($file){
        $item['imageurl'] = $file['filename'];
    }

    cmsCore::includeFile('includes/excel/excel_reader2.php');
    $data = new Spreadsheet_Excel_Reader($xls_file, true, $charset);

    for($r=0; $r<$rows; $r++){

        $fields = array();
        $title  = '';
        $item['price'] = '';

        foreach($cells as $cell_id=>$pos){
            if (isset($pos['ignore'])){
                $celldata = $pos['other'];
            } else {
                $celldata = ($charset == 'cp1251') ?
                iconv('cp1251', 'UTF-8', $data->val($r+$pos['row'],$pos['col'],$sheet-1)) :
                $data->val($r+$pos['row'],$pos['col'],$sheet-1);
            }

            if ($cell_id === 'title'){
                $title = $celldata;
            } elseif ($cell_id === 'price'){
                $item['price'] = $celldata;
            } else {
                $fields[] = $celldata;
            }
        }

        $item['fieldsdata'] = $inDB->escape_string(cmsCore::arrayToYaml($fields));
        $item['title']      = $inDB->escape_string($title);

        if ($item['title'] && $item['fieldsdata']){

            $model->addItem($item);

        }
    }

    @unlink($xls_file);

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&opt=list_items&id='.$id);

}

//=================================================================================================//
//=================================================================================================//

if ($opt=='saveprices'){

    $prices = cmsCore::request('price', 'array_str', array());
    if (is_array($prices)){
        foreach($prices as $id=>$price){
            $price = str_replace(',', '.', $price);
            $price = number_format($price, 2, '.', '');
            $sql = "UPDATE cms_uc_items SET price='$price' WHERE id = $id";
            $inDB->query($sql);
        }
    }
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirectBack();
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'show_item'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){
            dbShow('cms_uc_items', $_REQUEST['item_id']);
            $inDB->query('UPDATE cms_uc_items SET on_moderate = 0 WHERE id='.(int)$_REQUEST['item_id']);
        }
        echo '1'; exit;
    } else {
        dbShowList('cms_uc_items', $_REQUEST['item']);
        foreach($_REQUEST['item'] as $k=>$id){
            $inDB->query('UPDATE cms_uc_items SET on_moderate = 0 WHERE id='.(int)$id);
        }
        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
        cmsCore::redirectBack();
    }
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'hide_item'){
    if (!isset($_REQUEST['item'])){
        if (isset($_REQUEST['item_id'])){ dbHide('cms_uc_items', $_REQUEST['item_id']);  }
        echo '1'; exit;
    } else {
        dbHideList('cms_uc_items', $_REQUEST['item']);
        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
        cmsCore::redirectBack();
    }
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'renew_item'){
    $model->renewItem(cmsCore::request('item_id', 'int', 0));
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_items');
}

//=================================================================================================//
//=================================================================================================//

if($opt == 'delete_item'){

    $model->deleteItem(cmsCore::request('item_id', 'int', 0));
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_items');

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'submit_discount' || $opt == 'update_discount'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $item['title']      = cmsCore::request('title', 'str');
    $item['cat_id']     = cmsCore::request('cat_id', 'int');
    $item['sign']       = cmsCore::request('sign', 'str');
    $item['value']      = cmsCore::request('value', 'str');
    $item['unit']       = cmsCore::request('unit', 'str');
    $item['if_limit']   = cmsCore::request('if_limit', 'int', 0);

    if($opt == 'update_discount'){
        $model->updateDiscount(cmsCore::request('item_id', 'int', 0), $item);
    } else {
        $model->addDiscount($item);
    }
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&opt=list_discount&id='.$id);

}

if($opt == 'delete_discount'){

    $model->deleteDiscount(cmsCore::request('item_id', 'int', 0));
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_discount');

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'show_cat'){
    $item_id = cmsCore::request('item_id', 'int');
    $sql = "UPDATE cms_uc_cats SET published = 1 WHERE id = '$item_id'";
    $inDB->query($sql) ;
    echo '1'; exit;
}

if ($opt == 'hide_cat'){
    $item_id = cmsCore::request('item_id', 'int');
    $sql = "UPDATE cms_uc_cats SET published = 0 WHERE id = '$item_id'";
    $inDB->query($sql) ;
    echo '1'; exit;
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'submit_cat' || $opt == 'update_cat'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cat['parent_id']      = cmsCore::request('parent_id', 'int');
    $cat['title']          = cmsCore::request('title', 'str', $_LANG['AD_UNTITLED']);
    $cat['description']    = cmsCore::request('description', 'html');
    $cat['description']    = $inDB->escape_string($cat['description']);
    $cat['published']      = cmsCore::request('published', 'int');
    $cat['view_type']      = cmsCore::request('view_type', 'str');
    $cat['fields_show']    = cmsCore::request('fieldsshow', 'int');
    $cat['showmore']       = cmsCore::request('showmore', 'int');
    $cat['perpage']        = cmsCore::request('perpage', 'int');
    $cat['showtags']       = cmsCore::request('showtags', 'int');
    $cat['showabc']        = cmsCore::request('showabc', 'int');
    $cat['showsort']       = cmsCore::request('showsort', 'int');
    $cat['is_ratings']     = cmsCore::request('is_ratings', 'int');
    $cat['filters']        = cmsCore::request('filters', 'int');
    $cat['orderby']        = cmsCore::request('orderby', 'str');
    $cat['orderto']        = cmsCore::request('orderto', 'str');
    $cat['shownew']        = cmsCore::request('shownew', 'int');
    $cat['newint']         = cmsCore::request('int_1', 'int') . ' ' . cmsCore::request('int_2', 'str');
    $cat['is_public']      = cmsCore::request('is_public', 'int', 0);
    $cat['can_edit']       = cmsCore::request('can_edit', 'int', 0);
    $cat['cost']           = cmsCore::request('cost', 'str', '');
    $cat['pagetitle']      = cmsCore::request('pagetitle', 'str', '');
    $cat['meta_desc']      = cmsCore::request('meta_desc', 'str', '');
    $cat['meta_keys']      = cmsCore::request('meta_keys', 'str', '');
    if (!is_numeric($cat['cost'])) { $cat['cost'] = ''; }

    if (cmsCore::request('copy_parent_struct')){
        $fstruct = $inDB->get_field('cms_uc_cats', "id='{$cat['parent_id']}'", 'fieldsstruct');
    } else {
        $fstruct = cmsCore::request('fstruct', 'array', array());
        foreach ($fstruct as $key=>$value) {
            if ($value=='') { unset($fstruct[$key]); continue; }
            if ($_REQUEST['fformat'][$key]=='html') { $fstruct[$key] .= '/~h~/'; }
            if ($_REQUEST['fformat'][$key]=='link') { $fstruct[$key] .= '/~l~/'; }
            if ($_REQUEST['flink'][$key]) { $fstruct[$key] .= '/~m~/'; }
        }
        $fstruct = cmsCore::arrayToYaml($fstruct);
    }
    $cat['fieldsstruct'] = $inDB->escape_string($fstruct);

    if ($opt == 'submit_cat'){

        $cat_id = $inDB->addNsCategory('cms_uc_cats', cmsCore::callEvent('ADD_CATALOG_CAT', $cat));

    } else {

        $cat_id = cmsCore::request('item_id', 'int', 0);
        $model->updateCategory($cat_id, $cat);

    }

    if ($cat['is_public']){
        $showfor = cmsCore::request('showfor', 'array_int', array());
        if ($showfor){
            $model->setCategoryAccess($cat_id, $showfor);
        }
    } else {
        $model->clearCategoryAccess($cat_id);
    }

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');

}

//=================================================================================================//
//=================================================================================================//

if($opt == 'delete_cat'){
    $model->deleteCategory(cmsCore::request('item_id', 'int', 0));
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'list_cats'){

    echo '<h3>'.$_LANG['AD_CATALOG_RUBRICS'].'</h3>';

    $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_cat&item_id=%id%');
    $fields[] = array('title'=>$_LANG['AD_PARENT'], 'field'=>'parent_id', 'width'=>'200', 'prc'=>'cpCatalogCatById');
    $fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'100', 'do'=>'opt', 'do_suffix'=>'_cat');

    $actions[] = array('title'=>$_LANG['AD_CONTENT_VIEW'], 'icon'=>'explore.gif', 'link'=>'javascript:openCat(%id%)');
    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_cat&item_id=%id%');
    $actions[] = array('title'=>$_LANG['AD_DO_COPY'], 'icon'=>'copy.gif', 'link'=>"javascript:copyCat(".$id.", %id%);");
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_IF_RUBRIC_DELETE'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_cat&item_id=%id%');

    echo '<script type="text/javascript">function openCat(id){ $("#catform input").val(id); $("#catform").submit(); } </script>';
    echo '<form id="catform" method="post" action="index.php?view=components&do=config&id='.$id.'&opt=list_items"><input type="hidden" id="filter[category_id]" name="filter[category_id]" value=""></form>';

    cpListTable('cms_uc_cats', $fields, $actions, 'parent_id>0', 'NSLeft');

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'list_items'){

    $GLOBALS['cp_page_head'][] = '<script type="text/javascript" src="/admin/components/catalog/js/common.js"></script>';
    cpAddPathway($_LANG['AD_ITEMS']);

    if (cmsCore::inRequest('on_moderate')){
        echo '<h3>'.$_LANG['AD_ITEMS_TO_MODERATION'].'</h3>';
    } else {
        echo '<h3>'.$_LANG['AD_ITEMS'].'</h3>';
    }

    $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>(cmsCore::inRequest('on_moderate') ? '/catalog/item%id%.html' : '/catalog/edit%id%.html'), 'filter'=>15);
    $fields[] = array('title'=>$_LANG['AD_IS_PUBLISHED'], 'field'=>'published', 'width'=>'100', 'do'=>'opt', 'do_suffix'=>'_item');
    $fields[] = array('title'=>$_LANG['AD_CAT_BOARD'], 'field'=>'category_id', 'width'=>'200', 'prc'=>'cpCatalogCatById', 'filter'=>1, 'filterlist'=>cpGetList('cms_uc_cats'));
    $fields[] = array('title'=>$_LANG['PRICE'], 'field'=>array('id', 'category_id', 'price'), 'width'=>'150', 'prc'=>'cpPriceInput');

    $actions[] = array('title'=>$_LANG['AD_NEW_CALENDAR_DATA'], 'icon'=>'date.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=renew_item&item_id=%id%');
    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'/catalog/edit%id%.html');
    $actions[] = array('title'=>$_LANG['AD_DO_COPY'], 'icon'=>'copy.gif', 'link'=>"javascript:copyItem(".$id.", %id%);");
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_IF_ITEM_DELETE'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_item&item_id=%id%');

    if (cmsCore::inRequest('on_moderate')){ $where = 'on_moderate=1'; } else { $where = ''; }

    cpListTable('cms_uc_items', $fields, $actions, $where);

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'list_discount'){

    cpAddPathway($_LANG['AD_COEFFICIENTS']);
    echo '<h3>'.$_LANG['AD_COEFFICIENTS'].'</h3>';

    $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_discount&item_id=%id%');
    $fields[] = array('title'=>$_LANG['AD_CAT_BOARD'], 'field'=>'cat_id', 'width'=>'200', 'prc'=>'cpCatalogCatById');
    $fields[] = array('title'=>$_LANG['AD_TYPE'], 'field'=>'sign', 'width'=>'40');
    $fields[] = array('title'=>$_LANG['AD_SIZE'], 'field'=>'value', 'width'=>'80');
    $fields[] = array('title'=>$_LANG['AD_UNITS'], 'field'=>'unit', 'width'=>'80');
    $fields[] = array('title'=>$_LANG['AD_LIMIT'], 'field'=>'if_limit', 'width'=>'80');

    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit_discount&item_id=%id%');
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_IF_COEFFICIENT_DELETE'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete_discount&item_id=%id%');

    cpListTable('cms_uc_discount', $fields, $actions);

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'copy_item'){

    $item_id = cmsCore::request('item_id', 'int', 0);
    $copies  = cmsCore::request('copies', 'int', 0);
    if ($copies){
        $model->copyItem($item_id, $copies);
    }
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_items');
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'copy_cat'){

    $item_id = cmsCore::request('item_id', 'int', 0);
    $copies  = cmsCore::request('copies', 'int', 0);
    if ($copies){
        $model->copyCategory($item_id, $copies);
    }
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list_cats');

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'add_item'){

    echo '<h3>'.$_LANG['ADD_ITEM'].'</h3>';
    echo '<h4>'.$_LANG['AD_SELECT_CAT'].':</h4>';
    cpAddPathway($_LANG['ADD_ITEM']);

    $sql = "SELECT id, title, NSLeft, NSLevel, parent_id
            FROM cms_uc_cats
            WHERE parent_id > 0
            ORDER BY NSLeft";
    $result = $inDB->query($sql);

    if ($inDB->num_rows($result)){
        echo '<div style="padding:10px">';
            while ($cat = $inDB->fetch_assoc($result)){
                echo '<div style="padding:2px;padding-left:18px;margin-left:'.(($cat['NSLevel']-1)*15).'px;background:url(/admin/images/icons/hmenu/cats.png) no-repeat">
                          <a href="/catalog/'.$cat['id'].'/add.html">'.$cat['title'].'</a>
                      </div>';
            }
        echo '</div>';
    }

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'add_cat' || $opt == 'edit_cat'){

    require('../includes/jwtabs.php');
    $GLOBALS['cp_page_head'][] = jwHeader();

    if ($opt=='add_cat'){
        echo '<h3>'.$_LANG['AD_NEW_CAT'].'</h3>';
        cpAddPathway($_LANG['AD_NEW_CAT']);
    } else {
        $item_id = cmsCore::request('item_id', 'int', 0);
        $mod = $inDB->get_fields('cms_uc_cats', "id = '$item_id'", '*');
        if(!$mod){ cmsCore::error404(); }
        $fstruct = cmsCore::yamlToArray($mod['fieldsstruct']);
        echo '<h3>'.$_LANG['AD_CAT_BOARD'].': '.$mod['title'].'</h3>';
        cpAddPathway($mod['title']);
    } ?>

    <form id="addform" name="addform" method="post" action="index.php?view=components&do=config&id=<?php echo $id;?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <table class="proptable" width="100%" cellpadding="15" cellspacing="2">
        <tr>
            <!-- главная ячейка -->
            <td valign="top">
                <div><strong><?php echo $_LANG['AD_CAT_NAME'];?></strong></div>
                <div><input name="title" type="text" id="title" style="width:99%" value="<?php echo htmlspecialchars($mod['title']);?>" /></div>
                <div style="margin-top:10px"><strong><?php echo $_LANG['AD_ITEMS_FEATURES'];?></strong></div>
                <div><span class="hinttext">
                    <?php echo $_LANG['AD_FIELDS_NAME'];?>
                </span></div>
                <div style="margin-top:2px;margin-bottom:12px">
                    <div><span class="hinttext">
                        <?php echo $_LANG['AD_WHAT_MAKING_AUTOSEARCH'];?>
                    </span></div>
                </div>
                <div>
                    <script type="text/javascript">
                        function toggleFields(){
                            var copy = $('#copy_parent_struct').prop('checked');

                            if (copy){
                                $('.field, .fformat, .flink').prop('disabled', true);
                            } else {
                                $('.field, .fformat, .flink').prop('disabled', false);
                            }
                        }
                        function toggleAutosearch(id){
                            fformat = $('#fformat'+id+' option:selected').val();
                            if(fformat == 'text'){
                                $('.flink'+id).prop('disabled', false)
                                              .css('color', '');
                            } else {
                                $('.flink'+id).prop('disabled', true)
                                              .css('color', '#CCC');
                            }
                        }
                    </script>
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="16"><input type="checkbox" id="copy_parent_struct" name="copy_parent_struct" onchange="toggleFields()" value="1" /></td>
                            <td>
                                <label for="copy_parent_struct"><?php echo $_LANG['AD_COPY_PARENT_FEATURES'];?></label>
                            </td>
                        </tr>
                    </table>
                </div>

                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <?php for($f=0; $f<15; $f++) { ?>
                    <?php
                        if(@$fstruct[$f]) {
                            if (mb_strstr($fstruct[$f], '/~h~/')) {
                                $ftype = 'html';
                                $fstruct[$f] = str_replace('/~h~/', '', $fstruct[$f]);
                            } elseif(mb_strstr($fstruct[$f], '/~l~/')) {
                                $ftype = 'link';
                                $fstruct[$f] = str_replace('/~l~/', '', $fstruct[$f]);
                            } else {
                                $ftype = 'text';
                            }

                            if (mb_strstr($fstruct[$f], '/~m~/')) {
                                $makelink = true;  $fstruct[$f] = str_replace('/~m~/', '', $fstruct[$f]);
                            } else { $makelink = false; }
                        }
                    ?>
                    <tr>
                        <td width="105" style="padding-bottom:4px">
                            <select name="fformat[]" class="fformat" id="fformat<?php echo $f;?>" style="width:100px" onchange="toggleAutosearch('<?php echo $f;?>');">
                                <option value="text" <?php if(@$fstruct[$f]) { if ($ftype=='text') { echo 'selected'; } } ?>><?php echo $_LANG['AD_TEXT'];?></option>
                                <option value="html" <?php if(@$fstruct[$f]) { if ($ftype=='html') { echo 'selected'; } } ?>><?php echo $_LANG['AD_HTML'];?></option>
                                <option value="link" <?php if(@$fstruct[$f]) { if ($ftype=='link') { echo 'selected'; } } ?>><?php echo $_LANG['AD_LINK'];?></option>
                            </select>
                        </td>
                        <td style="padding-bottom:4px">
                            <input name="fstruct[]" class="field" type="text" id="fstruct[]" style="width:99%" <?php if (@$fstruct[$f]) { echo 'value="'.htmlspecialchars(stripslashes($fstruct[$f])).'"'; }?> />
                        </td>
                        <td width="70" align="right" style="padding-bottom:2px">
                            <strong class="flink<?php echo $f;?>"><?php echo $_LANG['AD_AUTOSEARCH'];?>:</strong>
                        </td>
                        <td width="50" align="right">
                            <label><input name="flink[<?php echo $f;?>]" class="flink flink<?php echo $f;?>" type="radio" value="1" <?php if(@$fstruct[$f]) { if ($makelink) { echo 'checked="checked"'; } } ?>/> <?php echo $_LANG['YES'];?> </label>
                        </td>
                        <td width="50" align="right">
                            <label><input name="flink[<?php echo $f;?>]" class="flink flink<?php echo $f;?>" type="radio" value="0" <?php if(@$fstruct[$f]) { if (!$makelink) { echo 'checked="checked"'; } } else { echo 'checked="checked"';} ?>/> <?php echo $_LANG['NO'];?> </label>
                        </td>
                    </tr>
                    <script type="text/javascript">
                        toggleAutosearch('<?php echo $f;?>');
                    </script>
                <?php } ?>
                </table>

                <div style="margin-top:10px"><strong><?php echo $_LANG['AD_MAKING_HTML_FIELDS'];?> <a href="index.php?view=filters" target="_blank"><?php echo $_LANG['AD_FILTERS'];?></a>?</strong></div>
                <div>
                    <select name="filters" id="filters" style="width:100%">
                        <option value="0" <?php if (!$mod['filters']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['NO'];?></option>
                        <option value="1" <?php if ($mod['filters']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['YES'];?></option>
                    </select>
                </div>

                <div style="margin-top:12px"><strong><?php echo $_LANG['AD_CAT_DESCRIPTION'];?></strong></div>
                <div><?php $inCore->insertEditor('description', $mod['description'], '200', '100%'); ?></div>

            </td>

            <!-- боковая ячейка -->
            <td width="300" valign="top" style="background:#ECECEC;">

                <?php ob_start(); ?>

                {tab=<?php echo $_LANG['AD_TAB_PUBLISH'];?>}

                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                    <tr>
                        <td width="20"><input type="checkbox" name="published" id="published" value="1" <?php if ($mod['published'] || $do=='add') { echo 'checked="checked"'; } ?>/></td>
                        <td><label for="published"><strong><?php echo $_LANG['AD_IF_PUBLIC_CAT'];?></strong></label></td>
                    </tr>
                </table>

                <div style="margin-top:7px">
                    <select name="parent_id" size="8" id="parent_id" style="width:99%;height:200px">
                        <?php $rootid = $inDB->get_field('cms_uc_cats', 'parent_id=0', 'id'); ?>
                        <option value="<?php echo $rootid; ?>" <?php if (@$mod['parent_id']==$rootid || !isset($mod['parent_id'])) { echo 'selected'; }?>><?php echo $_LANG['AD_CATALOG_ROOT'];?></option>
                        <?php
                            if (isset($mod['parent_id'])){
                                echo $inCore->getListItemsNS('cms_uc_cats', $mod['parent_id']);
                            } else {
                                echo $inCore->getListItemsNS('cms_uc_cats');
                            }
                        ?>
                    </select>
                </div>

                <div style="margin-bottom:15px;margin-top:4px" onchange="toggleAdvert()">
                    <select name="view_type" id="view_type" style="width:99%">
                        <option value="list" <?php if (@$mod['view_type']=='list') {echo 'selected';} ?>><?php echo $_LANG['AD_LIST'];?></option>
                        <option value="thumb" <?php if (@$mod['view_type']=='thumb') {echo 'selected';} ?>><?php echo $_LANG['AD_GALERY'];?></option>
                        <option value="shop" <?php if (@$mod['view_type']=='shop') {echo 'selected';} ?>><?php echo $_LANG['AD_SHOP'];?></option>
                    </select>
                </div>

                <div class="advert" id="catalog_advert" style="line-height:16px;<?php if ($mod['view_type']!='shop') {?>display:none<?php } ?>">
                    <?php echo $_LANG['AD_ALSO'];?> <a href="http://www.instantcms.ru/blogs/InstantSoft/professionalnyi-magazin-dlja-InstantCMS.html" target="_blank"><?php echo $_LANG['AD_ISHOP'];?></a>
                </div>

                <script type="text/javascript">toggleAdvert();</script>

                <div style="margin-top:12px"><strong><?php echo $_LANG['AD_VIEW_RUBRIC'];?></strong></div>
                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                    <tr>
                        <td width="20"><input type="checkbox" name="showmore" id="showmore" value="1" <?php if ($mod['showmore']) { echo 'checked="checked"'; } ?>/></td>
                        <td><label for="showmore"><?php echo $_LANG['AD_LINK_DETAILS'];?></label></td>
                    </tr>
                    <tr>
                        <td width="20"><input type="checkbox" name="is_ratings" id="is_ratings" value="1" <?php if ($mod['is_ratings']) { echo 'checked="checked"'; } ?>/></td>
                        <td><label for="is_ratings"><?php echo $_LANG['AD_ITEMS_RATING'];?></label></td>
                    </tr>
                    <tr>
                        <td width="20"><input type="checkbox" name="showtags" id="showtags" value="1" <?php if ($mod['showtags']) { echo 'checked="checked"'; } ?>/></td>
                        <td><label for="showtags"><?php echo $_LANG['AD_TAGS_VIEW'];?></label></td>
                    </tr>
                    <tr>
                        <td width="20"><input type="checkbox" name="showsort" id="showsort" value="1" <?php if ($mod['showsort']) { echo 'checked="checked"'; } ?>/></td>
                        <td><label for="showsort"><?php echo $_LANG['AD_SORT_VIEW'];?></label></td>
                    </tr>
                    <tr>
                        <td width="20"><input type="checkbox" name="showabc" id="showabc" value="1" <?php if ($mod['showabc']) { echo 'checked="checked"'; } ?>/></td>
                        <td><label for="showabc"><?php echo $_LANG['AD_ABC'];?></label></td>
                    </tr>
                </table>

                {tab=<?php echo $_LANG['AD_ITEMS'];?>}

                <div style="margin-top:5px;">
                    <strong><?php echo $_LANG['AD_FIELDS_QUANTITY'];?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_HOW_MANY_FIELDS'];?></span>
                </div>
                <div>
                    <input class="uispin" name="fieldsshow" type="text" id="fieldsshow" style="width:100%" value="<?php if ($opt=='edit_cat') { echo $mod['fields_show']; } else { echo '10'; } ?>"/>
                </div>

                <div style="margin-top:10px;">
                    <strong><?php echo $_LANG['ORDER_ARTICLES'];?></strong>
                </div>
                <div>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:2px;">
                        <tr>
                            <td valign="top"  width="50%">
                                <select name="orderby" id="orderby" style="width:100%">
                                    <option value="title" <?php if(@$mod['orderby']=='title') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_ALPHABET'];?></option>
                                    <option value="pubdate" <?php if(@$mod['orderby']=='pubdate') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_CALENDAR'];?></option>
                                    <option value="rating" <?php if(@$mod['orderby']=='rating') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_RATING'];?></option>
                                    <option value="hits" <?php if(@$mod['orderby']=='hits') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_VIEWS'];?></option>
                                </select>
                            </td>
                            <td valign="top" style="padding-left:5px">
                                <select name="orderto" id="orderto" style="width:100%">
                                    <option value="desc" <?php if(@$mod['orderto']=='desc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_DECREMENT'];?></option>
                                    <option value="asc" <?php if(@$mod['orderto']=='asc') { echo 'selected'; } ?>><?php echo $_LANG['AD_BY_INCREMENT'];?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="margin-top:10px;">
                    <strong><?php echo $_LANG['AD_HOW_MANY_ITEMS'];?></strong>
                </div>
                <div>
                    <input class="uispin" name="perpage" type="text" id="perpage" style="width:100%" value="<?php if ($opt=='edit_cat') { echo $mod['perpage']; } else { echo '20'; } ?>"/>
                </div>

                <div style="margin-top:10px;">
                    <strong><?php echo $_LANG['AD_WHATS_NEW'];?></strong>
                </div>
                <div>
                    <select name="shownew" id="shownew" style="width:100%">
                        <option value="1" <?php if ($mod['shownew']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['YES'];?></option>
                        <option value="0" <?php if (!$mod['shownew']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['NO'];?></option>
                    </select>
                </div>

                <div style="margin-top:10px;">
                    <strong><?php echo $_LANG['AD_HOW_LONG_TIME_NEW'];?></strong>
                </div>
                <div>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:2px;">
                        <tr>
                            <td valign="top" width="100">
                                <input class="uispin" name="int_1" type="text" id="int_1" style="width:95px" value="<?php echo @(int)$mod['newint']?>"/>
                            </td>
                            <td valign="top">
                                <select name="int_2" id="int_2" style="width:100%">
                                    <option value="HOUR"  <?php if(@mb_strstr($mod['newint'], 'HOUR')) { echo 'selected'; } ?>><?php echo $_LANG['HOUR10'];?></option>
                                    <option value="DAY" <?php if(@mb_strstr($mod['newint'], 'DAY')) { echo 'selected'; } ?>><?php echo $_LANG['DAY10'];?></option>
                                    <option value="MONTH" <?php if(@mb_strstr($mod['newint'], 'MONTH')) { echo 'selected'; } ?>><?php echo $_LANG['MONTH10'];?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                {tab=SEO}
                <div style="margin-top:5px">
                    <strong><?php echo $_LANG['AD_PAGE_TITLE']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_IF_UNKNOWN_PAGETITLE']; ?></span>
                </div>
                <div>
                    <input name="pagetitle" type="text" id="pagetitle" style="width:99%" value="<?php echo htmlspecialchars(@$mod['pagetitle']); ?>" />
                </div>

                <div style="margin-top:20px">
                    <strong><?php echo $_LANG['KEYWORDS']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_FROM_COMMA']; ?></span>
                </div>
                <div>
                     <textarea name="meta_keys" style="width:97%" rows="4" id="meta_keys"><?php echo htmlspecialchars(@$mod['meta_keys']);?></textarea>
                </div>

                <div style="margin-top:20px">
                    <strong><?php echo $_LANG['DESCRIPTION']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_LESS_THAN']; ?></span>
                </div>
                <div>
                     <textarea name="meta_desc" style="width:97%" rows="6" id="meta_desc"><?php echo htmlspecialchars(@$mod['meta_desc']);?></textarea>
                </div>
                {tab=<?php echo $_LANG['AD_TAB_ACCESS'];?>}

                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:5px">
                    <tr>
                        <td width="20">
                            <?php
                                if ($opt == 'edit_cat'){

                                    $sql2 = "SELECT * FROM cms_uc_cats_access WHERE cat_id = ".$mod['id'];
                                    $result2 = $inDB->query($sql2);
                                    $ord = array();

                                    if ($inDB->num_rows($result2)){
                                        while ($r = $inDB->fetch_assoc($result2)){
                                            $ord[] = $r['group_id'];
                                        }
                                    }
                                }
                            ?>
                            <input name="is_public" type="checkbox" id="is_public" onclick="checkGroupList()" value="1" <?php if(@$mod['is_public']){ echo 'checked="checked"'; } ?> />
                        </td>
                        <td><label for="is_public"><strong><?php echo $_LANG['AD_USERS_CAN_ADD_ITEM'];?></strong></label></td>
                    </tr>
                </table>
                <div style="padding:5px">
                    <span class="hinttext">
                        <?php echo $_LANG['AD_IF_ENABLE'];?>
                    </span>
                </div>

                <div style="margin-top:10px;padding:5px;padding-right:0px;" id="grp">
                    <div>
                        <strong><?php echo $_LANG['AD_ALLOW_GROUPS'];?></strong><br />
                        <span class="hinttext">
                            <?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL'];?>
                        </span>
                    </div>
                    <div>
                        <?php
                            echo '<select style="width: 99%" name="showfor[]" id="showin" size="6" multiple="multiple" '.(@$mod['is_public']?'':'disabled="disabled"').'>';

                            $sql    = "SELECT * FROM cms_user_groups";
                            $result = $inDB->query($sql) ;

                            if ($inDB->num_rows($result)){
                                while ($item = $inDB->fetch_assoc($result)){
                                    if($item['alias'] != 'guest'){
                                        echo '<option value="'.$item['id'].'"';
                                        if ($opt=='edit_cat'){
                                            if (inArray($ord, $item['id'])){
                                                echo 'selected';
                                            }
                                        }

                                        echo '>';
                                        echo $item['title'].'</option>';
                                    }
                                }
                            }

                            echo '</select>';
                        ?>
                    </div>
                </div>

                <?php if (IS_BILLING){ ?>
                    <div style="margin:5px">
                        <strong><?php echo $_LANG['AD_ITEM_COST'];?></strong><br/>
                        <div style="color:gray"><?php echo $_LANG['AD_DEFAULT_COST'];?></div>
                        <input type="text" name="cost" value="<?php echo $mod['cost']; ?>" style="width:50px"/> <?php echo $_LANG['BILLING_POINT10'];?>
                    </div>
                <?php } ?>

                <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:5px">
                    <tr>
                        <td width="20">
                            <input name="can_edit" type="checkbox" id="can_edit" onclick="" value="1" <?php if(@$mod['can_edit']){ echo 'checked="checked"'; } ?> />
                        </td>
                        <td><label for="can_edit"><strong><?php echo $_LANG['AD_ALLOW_EDIT'];?></strong></label></td>
                    </tr>
                </table>
                <div style="padding:5px">
                    <span class="hinttext">
                        <?php echo $_LANG['AD_IF_ALLOW_EDIT'];?>
                    </span>
                </div>

                {/tabs}

                <?php echo jwTabs(ob_get_clean()); ?>

            </td>

        </tr>
    </table>
    <p>
        <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
        <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.history.back();"/>
        <input name="opt" type="hidden" id="opt" <?php if ($opt=='add_cat') { echo 'value="submit_cat"'; } else { echo 'value="update_cat"'; } ?> />
        <?php
            if ($opt=='edit_cat'){
                echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
            }
        ?>
    </p>
    </form>

 <?php
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'add_discount' || $opt == 'edit_discount'){

    if ($opt=='add_discount'){
        echo '<h3>'.$_LANG['AD_COEFFICIENT_ADD'].'</h3>';
        cpAddPathway($_LANG['AD_COEFFICIENT_ADD']);
    } else {
        $item_id = cmsCore::request('item_id', 'int', 0);
        $mod = $inDB->get_fields('cms_uc_discount', "id = '$item_id'", '*');
        if(!$mod){ cmsCore::error404(); }

        echo '<h3>'.$mod['title'].'</h3>';
        cpAddPathway($_LANG['AD_COEFFICIENTS']);
        cpAddPathway($mod['title']);
    } ?>
    <form id="addform" name="addform" method="post" action="index.php?view=components&do=config&id=<?php echo $id;?>">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="584" border="0" cellspacing="5" class="proptable">
            <tr>
                <td width="250"><strong><?php echo $_LANG['TITLE'];?>: </strong></td>
                <td width="315" valign="top"><input name="title" type="text" id="title" style="width:250px" value="<?php echo htmlspecialchars($mod['title']);?>"/></td>
            </tr>
            <tr>
                <td valign="top"><strong><?php echo $_LANG['AD_CAT_BOARD'];?>:</strong></td>
                <td valign="top">
                    <select name="cat_id" id="cat_id" style="width:250px">
                        <?php $rootid = 0; ?>
                        <option value="<?php echo $rootid; ?>" <?php if (@$mod['cat_id']==$rootid || !isset($mod['cat_id'])) { echo 'selected'; }?>><?php echo $_LANG['AD_ALL_CAT'];?></option>
                        <?php
                            if (isset($mod['cat_id'])){
                                echo $inCore->getListItemsNS('cms_uc_cats', $mod['cat_id']);
                            } else {
                                echo $inCore->getListItemsNS('cms_uc_cats', 0);
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_TYPE'];?> </strong></td>
                <td valign="top"><label>
                        <select name="sign" id="sign" style="width:200px" onchange="toggleDiscountLimit()">
                            <option value="-1" <?php if (@$mod['sign']==-1) {echo 'selected';} ?>><?php echo $_LANG['AD_PRODUCT_DISCOUNT'];?>)</option>
                            <option value="1" <?php if (@$mod['sign']==1) {echo 'selected';} ?>><?php echo $_LANG['AD_PRODUCT_ALLOWANCE'];?>)</option>
                            <option value="2" <?php if (@$mod['sign']==2) {echo 'selected';} ?>><?php echo $_LANG['AD_ORDER_ALLOWANCE'];?></option>
                            <option value="3" <?php if (@$mod['sign']==3) {echo 'selected';} ?>><?php echo $_LANG['AD_ORDER_DISCOUNT'];?></option>
                        </select>
                </label></td>
            </tr>
            <tr class="if_limit" <?php if($mod['sign']!=3){ echo 'style="display:none"'; } ?>>
                <td>
                    <strong><?php echo $_LANG['AD_MIN_COST'];?> </strong>
                </td>
                <td valign="top">
                    <input name="if_limit" type="text" id="value" size="5" value="<?php if ($opt=='edit_discount') { echo $mod['if_limit']; } else { echo '0'; }?>"/> <?php echo $_LANG['CURRENCY'];?>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_UNITS'];?>: </strong></td>
                <td valign="top"><label>
                        <select name="unit" id="unit" style="width:200px">
                            <option value="%" <?php if (@$mod['unit']=='%') {echo 'selected';} ?>><?php echo $_LANG['AD_PERCENT'];?></option>
                            <option value="<?php echo $_LANG['CURRENCY'];?>" <?php if (@$mod['unit']==$_LANG['CURRENCY']) {echo 'selected';} ?>><?php echo $_LANG['AD_CURRENCY_NAME'];?></option>
                        </select>
                </label></td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_VALUE'];?>: </strong>
                </td>
                <td valign="top">
                    <input name="value" type="text" id="value" size="5" value="<?php if ($opt=='edit_discount') { echo $mod['value']; } ?>"/>
                </td>
            </tr>
        </table>
        <p>
            <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
            <input name="back3" type="button" id="back3" value="<?php echo $_LANG['CANCEL'];?>" onclick="window.location.href='index.php?view=components';"/>
            <input name="opt" type="hidden" id="do" <?php if ($opt=='add_discount') { echo 'value="submit_discount"'; } else { echo 'value="update_discount"'; } ?> />
            <?php
            if ($opt=='edit_discount'){
                echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
            }
            ?>
        </p>
    </form>
     <?php
}

//=================================================================================================//
//=================================================================================================//

if($opt=='saveconfig'){

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $cfg = array();

    $cfg['email']       = cmsCore::request('email', 'str', '');
    $cfg['delivery']    = cmsCore::request('delivery', 'str', '');
    $cfg['notice']      = cmsCore::request('notice', 'int', 0);
    $cfg['premod']      = cmsCore::request('premod', 'int', 1);
    $cfg['premod_msg']  = cmsCore::request('premod_msg', 'int', 1);
    $cfg['is_comments'] = cmsCore::request('is_comments', 'int', 0);
    $cfg['is_rss']      = cmsCore::request('is_rss', 'int', 1);
    $cfg['watermark']   = cmsCore::request('watermark', 'int', 1);
    $cfg['small_size']  = cmsCore::request('small_size', 'int', 100);
    $cfg['medium_size'] = cmsCore::request('medium_size', 'int', 250);
    $cfg['meta_keys']   = cmsCore::request('meta_keys', 'str', '');
    $cfg['meta_desc']   = cmsCore::request('meta_desc', 'str', '');

    $inCore->saveComponentConfig('catalog', $cfg);

    cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');
    cmsCore::redirectBack();

}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'config') {

    cpAddPathway($_LANG['AD_SETTINGS']);
    cpCheckWritable('/images/catalog', 'folder');
    cpCheckWritable('/images/catalog/medium', 'folder');
    cpCheckWritable('/images/catalog/small', 'folder');
     ?>
     <form action="index.php?view=components&do=config&id=<?php echo $id; ?>" method="post" name="addform" target="_self" id="form1">
     <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
         <table width="600" border="0" cellpadding="10" cellspacing="0" class="proptable">
             <tr>
                 <td width=""><strong><?php echo $_LANG['AD_SELLER_EMAIL']; ?></strong></td>
                 <td width="260"><input name="email" type="text" id="email" style="width:250px" value="<?php echo @$cfg['email'];?>"/></td>
             </tr>
             <tr>
                 <td><strong><?php echo $_LANG['AD_USER_NOTICE']; ?> </strong></td>
                 <td>
                     <label><input name="notice" type="radio" value="1" <?php if (@$cfg['notice']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                     <label><input name="notice" type="radio" value="0"  <?php if (@!$cfg['notice']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                 </td>
             </tr>
         </table>
         <table width="600" border="0" cellpadding="10" cellspacing="0" class="proptable">
             <tr>
                 <td><strong><?php echo $_LANG['AD_USERS_MODERATION']; ?> </strong></td>
                 <td width="260">
                     <label><input name="premod" type="radio" value="1" <?php if (@$cfg['premod']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                     <label><input name="premod" type="radio" value="0"  <?php if (@!$cfg['premod']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                 </td>
             </tr>
             <tr>
                 <td><strong><?php echo $_LANG['AD_ABOUT_NEW_ITEM']; ?> </strong></td>
                 <td width="260">
                     <label><input name="premod_msg" type="radio" value="1" <?php if (@$cfg['premod_msg']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                     <label><input name="premod_msg" type="radio" value="0"  <?php if (@!$cfg['premod_msg']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                 </td>
             </tr>
             <tr>
                 <td><strong><?php echo $_LANG['AD_AUTOCOMENT']; ?> </strong></td>
                 <td width="260">
                     <label><input name="is_comments" type="radio" value="1" <?php if (@$cfg['is_comments']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                     <label><input name="is_comments" type="radio" value="0"  <?php if (@!$cfg['is_comments']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                 </td>
             </tr>
             <tr>
                 <td><strong><?php echo $_LANG['AD_ENABLE_WATERMARK']; ?></strong>  <br />
                    <?php echo $_LANG['AD_IF_PUT_IMAGE']; ?> "<a href="/images/watermark.png" target="_blank">/images/watermark.png</a>"
                 </td>
                 <td width="260">
                     <label><input name="watermark" type="radio" value="1" <?php if (@$cfg['watermark']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                     <label><input name="watermark" type="radio" value="0"  <?php if (@!$cfg['watermark']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                 </td>
             </tr>
             <tr>
                 <td width=""><strong><?php echo $_LANG['AD_MEDIUM_SIZE']; ?></strong></td>
                 <td width="260"><input class="uispin" name="medium_size" type="text" style="width:100px" value="<?php echo @$cfg['medium_size'];?>"/></td>
             </tr>
             <tr>
                 <td width=""><strong><?php echo $_LANG['AD_SMALL_SIZE']; ?></strong></td>
                 <td width="260"><input class="uispin" name="small_size" type="text" style="width:100px" value="<?php echo @$cfg['small_size'];?>"/></td>
             </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METAKEYS']; ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['AD_FROM_COMMA'] ?><br /></div>
                    <textarea name="meta_keys" rows="2" style="width:99%"><?php echo $cfg['meta_keys'] ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong style="margin:5px 0px 5px 0px"><?php echo $_LANG['AD_ROOT_METADESC']; ?></strong><br />
                    <div class="hinttext"><?php echo $_LANG['SEO_METADESCR_HINT'] ?></div>
                    <textarea name="meta_desc" rows="4" style="width:99%"><?php echo $cfg['meta_desc'] ?></textarea>
                </td>
            </tr>
         </table>
         <table width="600" border="0" cellpadding="10" cellspacing="0" class="proptable">
             <tr>
                 <td><strong><?php echo $_LANG['AD_VIEW_RSS_ICON']; ?> </strong></td>
                 <td width="260">
                     <label><input name="is_rss" type="radio" value="1" <?php if (@$cfg['is_rss']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['YES']; ?> </label>
                     <label><input name="is_rss" type="radio" value="0"  <?php if (@!$cfg['is_rss']) { echo 'checked="checked"'; } ?> /> <?php echo $_LANG['NO']; ?> </label>
                 </td>
             </tr>
         </table>
         <table width="600" border="0" cellpadding="10" cellspacing="0" class="proptable">
            <tr>
                <td><p><strong><?php echo $_LANG['AD_ABOUT_DELIVERY']; ?></strong></p>
                    <p><textarea name="delivery" style="width:568px;height:150px;border:solid 1px gray"><?php echo @$cfg['delivery'];?></textarea></p>
                </td>
            </tr>
         </table>
         <p>
             <input name="opt" type="hidden" id="opt" value="saveconfig" />
             <input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
             <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
         </p>
     </form>
    <?php
}

//=================================================================================================//
//=================================================================================================//

if ($opt == 'import_xls'){

    cpAddPathway($_LANG['AD_EXCEL_IMPORT']);
    echo '<h3>'.$_LANG['AD_EXCEL_IMPORT'].'</h3>';

    if (cmsCore::inRequest('cat_id')){

        $cat_id = cmsCore::request('cat_id', 'int', 0);
        $cat = $inDB->get_fields('cms_uc_cats', "id = '$cat_id'", '*');
        if(!$cat){ cmsCore::error404(); }
        $fstruct = cmsCore::yamlToArray($cat['fieldsstruct']);

        ?>
        <form action="index.php?view=components&do=config&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" name="addform">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <p><strong><?php echo $_LANG['AD_CAT_BOARD']; ?>:</strong> <a href="index.php?view=components&do=config&id=<?php echo $id; ?>&opt=import_xls"><?php echo $cat['title']; ?></a></p>
        <p><?php echo $_LANG['AD_CHECK_EXCEL_FILE']; ?></p>
        <table width="750" border="0" cellspacing="5" class="proptable">
            <tr>
                <td width="300">
                    <strong><?php echo $_LANG['AD_EXCEL_FILE']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_XLS_EXTENTION']; ?></span>
                </td>
                <td><input type="file" name="xlsfile" /></td>
            </tr>
            <tr>
                <td width="300">
                    <strong><?php echo $_LANG['AD_ENCODING']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_SOFTWARE']; ?></span>
                </td>
                <td>
                    <select name="charset" style="width:300px">
                        <option value="cp1251" selected><?php echo $_LANG['AD_CP1251']; ?></option>
                        <option value="UTF-8"><?php echo $_LANG['AD_UTF8']; ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_LINE_QUANTITY']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_PRESCRIPTION']; ?></span>
                </td>
                <td><input type="text" name="xlsrows" style="width:40px" /> <?php echo $_LANG['AD_PIECES']; ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_LIST_NUMBER']; ?></strong></td>
                <td><input type="text" name="xlslist" style="width:40px" value="1" /></td>
            </tr>
        </table>
        <p>
            <?php echo $_LANG['AD_DATA_NOTE_INFO']; ?>
        </p>
        <table width="750" border="0" cellspacing="5" class="proptable">
            <tr id="row_title">
                <td width="250"><strong><?php echo $_LANG['TITLE']; ?>:</strong></td>
                <td><?php echo $_LANG['AD_COLUMN'];?>:</td>
                <td><input type="text" onkeyup="xlsEditCol()" id="title_col" name="cells[title][col]" style="width:40px" /></td>
                <td><?php echo $_LANG['AD_LINE']; ?>:</td>
                <td><input type="text" onkeyup="xlsEditRow()" id="title_row" name="cells[title][row]" style="width:40px" /></td>
                <td width="90"><label><input type="checkbox" id="ignore_title" name="cells[title][ignore]" onclick="ignoreRow('title')" value="1"/> <?php echo $_LANG['AD_TEXT']; ?>: </label></td>
                <td><input type="text" class="other" name="cells[title][other]" style="width:200px" disabled="disabled" /></td>
            </tr>
        <?php
        $current = 0;
        foreach($fstruct as $key=>$value) {
            //strip special markups
            if (mb_strstr($value, '/~h~/')) { $value=str_replace('/~h~/', '', $value); }
            elseif (mb_strstr($value, '/~l~/')) { $value=str_replace('/~l~/', '', $value); } else { $ftype='text'; }
            if (mb_strstr($value, '/~m~/')) { $value=str_replace('/~m~/', '', $value); }
            //show field inputs
            ?>
                <tr id="row_<?php echo $current; ?>">
                    <td width=""><strong><?php echo stripslashes($value); ?>:</strong></td>
                    <td><?php echo $_LANG['AD_COLUMN'];?>:</td>
                    <td><input type="text" class="col" id="<?php echo $current; ?>" name="cells[<?php echo $current; ?>][col]" style="width:40px" /></td>
                    <td><?php echo $_LANG['AD_LINE']; ?>:</td>
                    <td><input type="text" class="row" name="cells[<?php echo $current; ?>][row]" style="width:40px" /></td>
                    <td><label><input type="checkbox" id="ignore_<?php echo $current; ?>" name="cells[<?php echo $current; ?>][ignore]" onclick="ignoreRow('<?php echo $current; ?>')" value="1" /> <?php echo $_LANG['AD_TEXT']; ?>: </label></td>
                    <td><input type="text" class="other" name="cells[<?php echo $current; ?>][other]" style="width:200px" disabled="disabled" /></td>
                </tr>
            <?php
            $current++;
        }

        if ($cat['view_type']=='shop'){
            ?>
                <tr id="row_price">
                    <td width=""><strong><?php echo $_LANG['PRICE'];?>:</strong></td>
                    <td><?php echo $_LANG['AD_COLUMN'];?>:</td>
                    <td><input type="text" class="col" name="cells[price][col]" style="width:40px" /></td>
                    <td><?php echo $_LANG['AD_LINE']; ?>:</td>
                    <td><input type="text" class="row" name="cells[price][row]" style="width:40px" /></td>
                    <td><label><input type="checkbox" id="ignore_price" name="cells[price][ignore]" onclick="ignoreRow('price')" value="1"/> <?php echo $_LANG['AD_TEXT']; ?>: </label></td>
                    <td><input type="text" class="other" name="cells[price][other]" style="width:200px" disabled="disabled" /></td>
                </tr>
            <?php
        }
        ?>
        </table>

        <p><?php echo $_LANG['AD_OTHER_PARAMETRS']; ?>:</p>
        <table width="750" border="0" cellspacing="5" class="proptable">
            <tr>
                <td width="250">
                    <strong><?php echo $_LANG['AD_ITEM_PUBLIC']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_ITEM_VIEW']; ?></span>
                </td>
                <td>
                    <label><input name="published" type="radio" value="1" checked="checked" /> <?php echo $_LANG['YES']; ?> </label>
                    <label><input name="published" type="radio" value="0" /> <?php echo $_LANG['NO']; ?> </label>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_ALLOW_COMENTS']; ?>:</strong></td>
                <td>
                    <label><input name="is_comments" type="radio" value="1" checked="checked" /> <?php echo $_LANG['YES']; ?> </label>
                    <label><input name="is_comments" type="radio" value="0" /> <?php echo $_LANG['NO']; ?> </label>
                </td>
            </tr>
            <?php if ($cat['view_type']=='shop'){ ?>
            <tr>
                <td>
                    <strong><?php echo $_LANG['CAN_MANY']; ?>:</strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_PRODUCT_ORDER']; ?></span>
                </td>
                <td>
                    <label><input name="canmany" type="radio" value="1" checked="checked" /> <?php echo $_LANG['YES']; ?> </label>
                    <label><input name="canmany" type="radio" value="0" /> <?php echo $_LANG['NO']; ?> </label>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_ITEMS_TAGS']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_NOT_NECESSARILY']; ?></span>
                </td>
                <td>
                    <input type="text" name="tags" style="width:300px" />
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_IMG_FILE']; ?></strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_NOT_NECESSARILY']; ?></span>
                </td>
                <td>
                    <input type="file" name="imgfile" />
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php echo $_LANG['AD_USER']; ?>:</strong><br/>
                    <span class="hinttext"><?php echo $_LANG['AD_USER_ALIAS']; ?></span>
                </td>
                <td>
                    <select name="user_id" style="width:300px">
                    <?php echo cmsUser::getUsersList(); ?>
                    </select>
                </td>
            </tr>
        </table>

        <p>
            <input name="cat_id" type="hidden" id="cat_id" value="<?php echo $cat_id; ?>" />
            <input name="opt" type="hidden" id="opt" value="go_import_xls" />
            <input name="save" type="submit" id="save" value="<?php echo $_LANG['AD_IMPORT']; ?>" />
            <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.history.go(-1);" />
        </p>

        </form><?php

    } else {


        echo '<h4>'.$_LANG['AD_CHECK_RUBRIC'].'</h4>';

        $sql = "SELECT id, title, NSLeft, NSLevel, parent_id
                FROM cms_uc_cats
                WHERE parent_id > 0
                ORDER BY NSLeft";
        $result = $inDB->query($sql);

        if ($inDB->num_rows($result)){
            echo '<div style="padding:10px">';
                while ($cat = $inDB->fetch_assoc($result)){
                    echo '<div style="padding:2px;padding-left:18px;margin-left:'.(($cat['NSLevel']-1)*15).'px;background:url(/admin/images/icons/hmenu/cats.png) no-repeat">
                              <a href="?view=components&do=config&id='.$id.'&opt=import_xls&cat_id='.$cat['id'].'">'.$cat['title'].'</a>
                          </div>';
                }
            echo '</div>';
        }

    }

}
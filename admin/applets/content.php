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

if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

function createMenuItem($menu, $id, $title){
    $inCore = cmsCore::getInstance();
	$inDB 	= cmsDatabase::getInstance();
	$rootid = $inDB->get_field('cms_menu', 'parent_id=0', 'id');

	$ns     = $inCore->nestedSetsInit('cms_menu');
	$myid   = $ns->AddNode($rootid);

    $link   = $inCore->getMenuLink('content', $id);

	$sql = "UPDATE cms_menu
			SET menu='$menu',
				title='$title',
				link='$link',
				linktype='content',
				linkid='$id',
				target='_self',
				published='1',
				template='0',
				access_list='',
				iconurl=''
			WHERE id = '$myid'";

	$inDB->query($sql);
	return true;
}

function applet_content(){

    $inCore = cmsCore::getInstance();
    $inUser = cmsUser::getInstance();
	$inDB 	= cmsDatabase::getInstance();

	global $_LANG;

	//check access
	global $adminAccess;
	if (!cmsUser::isAdminCan('admin/content', $adminAccess)) { cpAccessDenied(); }

    $cfg = $inCore->loadComponentConfig('content');

    cmsCore::loadModel('content');
    $model = new cms_model_content();

    $GLOBALS['cp_page_title'] = $_LANG['AD_ARTICLES'];
    cpAddPathway($_LANG['AD_ARTICLES'], 'index.php?view=tree');

	$do = cmsCore::request('do', 'str', 'add');
	$id = cmsCore::request('id', 'int', -1);

	if ($do == 'arhive_on'){
		$inDB->query("UPDATE cms_content SET is_arhive = 1 WHERE id = '$id'");
		cmsCore::addSessionMessage($_LANG['AD_ARTICLES_TO_ARHIVE'], 'success');
		cmsCore::redirectBack();
	}

	if ($do == 'move'){

        $item_id = cmsCore::request('id', 'int', 0);
        $cat_id  = cmsCore::request('cat_id', 'int', 0);

        $dir     = $_REQUEST['dir'];
        $step    = 1;

        $model->moveItem($item_id, $cat_id, $dir, $step);
        echo '1'; exit;

	}

    if ($do == 'move_to_cat'){

        $items      = cmsCore::request('item', 'array_int');
        $to_cat_id  = cmsCore::request('obj_id', 'int', 0);

        if ($items && $to_cat_id){

			$last_ordering = (int)$inDB->get_field('cms_content', "category_id = '{$to_cat_id}' ORDER BY ordering DESC", 'ordering');

			foreach($items as $item_id){
				$article = $model->getArticle($item_id);
				if(!$article) { continue; }
				$last_ordering++;

                $model->updateArticle($article['id'], array('category_id'=>$to_cat_id,
                                                            'ordering'=>$last_ordering,
                                                            'url'=>$article['url'],
                                                            'title'=>$inDB->escape_string($article['title']),
                                                            'id'=>$article['id'],
                                                            'user_id'=>$article['user_id']));

			}

			cmsCore::addSessionMessage($_LANG['AD_ARTICLES_TO'], 'success');

        }

        cmsCore::redirect('?view=tree&cat_id='.$to_cat_id);

    }

	if ($do == 'show'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){ dbShow('cms_content', $id);  }
			echo '1'; exit;
		} else {
			dbShowList('cms_content', cmsCore::request('item', 'array_int'));
			cmsCore::redirectBack();
		}

	}

	if ($do == 'hide'){
		if (!isset($_REQUEST['item'])){
			if ($id >= 0){ dbHide('cms_content', $id);  }
			echo '1'; exit;
		} else {
			dbHideList('cms_content', cmsCore::request('item', 'array_int'));
			cmsCore::redirectBack();
		}
	}

	if ($do == 'delete'){

		if (!isset($_REQUEST['item'])){
			if ($id >= 0){
				$model->deleteArticle($id);
				cmsCore::addSessionMessage($_LANG['AD_ARTICLE_REMOVE'], 'success');
			}
		} else {
			$model->deleteArticles(cmsCore::request('item', 'array_int'));
			cmsCore::addSessionMessage($_LANG['AD_ARTICLES_REMOVE'], 'success');
		}
		cmsCore::redirectBack();
	}

	if ($do == 'update'){
        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }
		if(isset($_REQUEST['id'])) {

			$id                     = cmsCore::request('id', 'int', 0);
			$article['category_id'] = cmsCore::request('category_id', 'int', 1);
			$article['title']       = cmsCore::request('title', 'str');
			$article['url']         = cmsCore::request('url', 'str');
			$article['showtitle']   = cmsCore::request('showtitle', 'int', 0);
			$article['description'] = cmsCore::request('description', 'html', '');
			$article['description'] = $inDB->escape_string($article['description']);
			$article['content']     = cmsCore::request('content', 'html', '');
			$article['content']    	= $inDB->escape_string($article['content']);
			$article['published']   = cmsCore::request('published', 'int', 0);

			$article['showdate']    = cmsCore::request('showdate', 'int', 0);
			$article['showlatest']  = cmsCore::request('showlatest', 'int', 0);
			$article['showpath']    = cmsCore::request('showpath', 'int', 0);
			$article['comments']    = cmsCore::request('comments', 'int', 0);
			$article['canrate']     = cmsCore::request('canrate', 'int', 0);

            $enddate                = explode('.', cmsCore::request('enddate', 'str'));
            $article['enddate']     = $enddate[2] . '-' . $enddate[1] . '-' . $enddate[0];

			$article['is_end']      = cmsCore::request('is_end', 'int', 0);
            $article['pagetitle']   = cmsCore::request('pagetitle', 'str', '');

			$article['tags']        = cmsCore::request('tags', 'str');

            $olddate                = cmsCore::request('olddate', 'str', '');
			$pubdate                = cmsCore::request('pubdate', 'str', '');

            $article['user_id']     = cmsCore::request('user_id', 'int', $inUser->id);

			$article['tpl'] 		= cmsCore::request('tpl', 'str', 'com_content_read.tpl');

            $date = explode('.', $pubdate);
            $article['pubdate'] = $date[2] . '-' . $date[1] . '-' . $date[0] . ' ' .date('H:i');

            $autokeys               = cmsCore::request('autokeys', 'int');

            switch($autokeys){
                case 1: $article['meta_keys'] = $inCore->getKeywords($article['content']);
                        $article['meta_desc'] = $article['title'];
                        break;

                case 2: $article['meta_desc'] = strip_tags($article['description']);
                        $article['meta_keys'] = $article['tags'];
                        break;

                case 3: $article['meta_desc'] = cmsCore::request('meta_desc', 'str');
                        $article['meta_keys'] = cmsCore::request('meta_keys', 'str');
                        break;
            }

			$model->updateArticle($id, $article);

			if (!cmsCore::request('is_public', 'int', 0)){
				$showfor = $_REQUEST['showfor'];
				cmsCore::setAccess($id, $showfor, 'material');
			} else {
				cmsCore::clearAccess($id, 'material');
            }


            $file = 'article'.$id.'.jpg';

            if (cmsCore::request('delete_image', 'int', 0)){
                @unlink(PATH."/images/photos/small/$file");
                @unlink(PATH."/images/photos/medium/$file");
            } else {

				// Загружаем класс загрузки фото
				cmsCore::loadClass('upload_photo');
				$inUploadPhoto = cmsUploadPhoto::getInstance();
				// Выставляем конфигурационные параметры
				$inUploadPhoto->upload_dir    = PATH.'/images/photos/';
				$inUploadPhoto->small_size_w  = $model->config['img_small_w'];
				$inUploadPhoto->medium_size_w = $model->config['img_big_w'];
				$inUploadPhoto->thumbsqr      = $model->config['img_sqr'];
				$inUploadPhoto->is_watermark  = $model->config['watermark'];
				$inUploadPhoto->input_name    = 'picture';
				$inUploadPhoto->filename      = $file;
				// Процесс загрузки фото
				$inUploadPhoto->uploadPhoto();

            }
			cmsCore::addSessionMessage($_LANG['AD_ARTICLE_SAVE'], 'success');

			if (!isset($_SESSION['editlist']) || @sizeof($_SESSION['editlist'])==0){
				cmsCore::redirect('?view=tree&cat_id='.$article['category_id']);
			} else {
				cmsCore::redirect('?view=content&do=edit');
			}
		}
	}

	if ($do == 'submit'){
        if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }
        $article['category_id'] = cmsCore::request('category_id', 'int', 1);
        $article['title']       = cmsCore::request('title', 'str');
        $article['url']         = cmsCore::request('url', 'str');
        $article['showtitle']   = cmsCore::request('showtitle', 'int', 0);
		$article['description'] = cmsCore::request('description', 'html', '');
		$article['description'] = $inDB->escape_string($article['description']);
		$article['content']     = cmsCore::request('content', 'html', '');
		$article['content']    	= $inDB->escape_string($article['content']);

        $article['published']   = cmsCore::request('published', 'int', 0);

        $article['showdate']    = cmsCore::request('showdate', 'int', 0);
        $article['showlatest']  = cmsCore::request('showlatest', 'int', 0);
        $article['showpath']    = cmsCore::request('showpath', 'int', 0);
        $article['comments']    = cmsCore::request('comments', 'int', 0);
        $article['canrate']     = cmsCore::request('canrate', 'int', 0);

		$enddate                = explode('.', cmsCore::request('enddate', 'str'));
		$article['enddate']     = $enddate[2] . '-' . $enddate[1] . '-' . $enddate[0];
        $article['is_end']      = cmsCore::request('is_end', 'int', 0);
        $article['pagetitle']   = cmsCore::request('pagetitle', 'str', '');

        $article['tags']        = cmsCore::request('tags', 'str');

        $article['pubdate']     = $_REQUEST['pubdate'];
        $date                   = explode('.', $article['pubdate']);
		$article['pubdate']     = $date[2] . '-' . $date[1] . '-' . $date[0] . ' ' .date('H:i');

		$article['user_id']     = cmsCore::request('user_id', 'int', $inUser->id);

		$article['tpl'] 		= cmsCore::request('tpl', 'str', 'com_content_read.tpl');

        $autokeys               = cmsCore::request('autokeys', 'int');

        switch($autokeys){
            case 1: $article['meta_keys'] = $inCore->getKeywords($article['content']);
                    $article['meta_desc'] = $article['title'];
                    break;

            case 2: $article['meta_desc'] = strip_tags($article['description']);
                    $article['meta_keys'] = $article['tags'];
                    break;

            case 3: $article['meta_desc'] = cmsCore::request('meta_desc', 'str');
                    $article['meta_keys'] = cmsCore::request('meta_keys', 'str');
                    break;
        }

        $article['id'] = $model->addArticle($article);

		if (!cmsCore::request('is_public', 'int', 0)){
			$showfor = $_REQUEST['showfor'];
			if (sizeof($showfor)>0  && !cmsCore::request('is_public', 'int', 0)){
				cmsCore::setAccess($article['id'], $showfor, 'material');
            }
		}

        $inmenu = cmsCore::request('createmenu', 'str', '');

		if ($inmenu){
			createMenuItem($inmenu, $article['id'], $article['title']);
		}

		// Загружаем класс загрузки фото
		cmsCore::loadClass('upload_photo');
		$inUploadPhoto = cmsUploadPhoto::getInstance();
		// Выставляем конфигурационные параметры
		$inUploadPhoto->upload_dir    = PATH.'/images/photos/';
		$inUploadPhoto->small_size_w  = $model->config['img_small_w'];
		$inUploadPhoto->medium_size_w = $model->config['img_big_w'];
		$inUploadPhoto->thumbsqr      = $model->config['img_sqr'];
		$inUploadPhoto->is_watermark  = $model->config['watermark'];
		$inUploadPhoto->input_name    = 'picture';
		$inUploadPhoto->filename      = 'article'.$article['id'].'.jpg';
		// Процесс загрузки фото
		$inUploadPhoto->uploadPhoto();

		cmsCore::addSessionMessage($_LANG['AD_ARTICLE_ADD'], 'success');

		cmsCore::redirect('?view=tree&cat_id='.$article['category_id']);

	}

   if ($do == 'add' || $do == 'edit'){

	   	require('../includes/jwtabs.php');
		$GLOBALS['cp_page_head'][] = jwHeader();

 		$toolmenu = array();
		$toolmenu[0]['icon'] = 'save.gif';
		$toolmenu[0]['title'] = $_LANG['SAVE'];
		$toolmenu[0]['link'] = 'javascript:document.addform.submit();';

		$toolmenu[1]['icon'] = 'cancel.gif';
		$toolmenu[1]['title'] = $_LANG['CANCEL'];
		$toolmenu[1]['link'] = 'javascript:history.go(-1);';

		cpToolMenu($toolmenu);
        $menu_list = cpGetList('menu');

		if ($do=='add'){
			 echo '<h3>'.$_LANG['AD_CREATE_ARTICLE'].'</h3>';
 	 		 cpAddPathway($_LANG['AD_CREATE_ARTICLE'], 'index.php?view=content&do=add');
			 $mod['category_id'] = (int)$_REQUEST['to'];
			 $mod['showpath'] = 1;
			 $mod['tpl'] = 'com_content_read.tpl';
		} else {
			if (isset($_REQUEST['item'])){
				$_SESSION['editlist'] = $_REQUEST['item'];
			}

			 $ostatok = '';

			 if (isset($_SESSION['editlist'])){
				$id = array_shift($_SESSION['editlist']);
				if (sizeof($_SESSION['editlist'])==0) { unset($_SESSION['editlist']); } else
				{ $ostatok = '('.$_LANG['AD_NEXT_IN'].sizeof($_SESSION['editlist']).')'; }
			 } else { $id = (int)$_REQUEST['id']; }

			 $sql = "SELECT *, (TO_DAYS(enddate) - TO_DAYS(CURDATE())) as daysleft, DATE_FORMAT(pubdate, '%d.%m.%Y') as pubdate, DATE_FORMAT(enddate, '%d.%m.%Y') as enddate
					 FROM cms_content
					 WHERE id = $id LIMIT 1";
			 $result = $inDB->query($sql) ;
			 if ($inDB->num_rows($result)){
				$mod = $inDB->fetch_assoc($result);
			 }

			 echo '<h3>'.$_LANG['AD_EDIT_ARTICLE'].$ostatok.'</h3>';
			 cpAddPathway($mod['title'], 'index.php?view=content&do=edit&id='.$mod['id']);
		}
	?>
    <form id="addform" name="addform" method="post" action="index.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <input type="hidden" name="view" value="content" />

        <table class="proptable" width="100%" cellpadding="5" cellspacing="2">
            <tr>

                <!-- главная ячейка -->
                <td valign="top">

                    <table width="100%" cellpadding="0" cellspacing="4" border="0">
                        <tr>
                            <td valign="top">
                                <div><strong><?php echo $_LANG['AD_ARTICLE_NAME']; ?></strong> <?php printLangPanel('content_content', @$mod['id'], 'title'); ?></div>
                                <div>
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td><input name="title" type="text" id="title" style="width:100%" value="<?php echo htmlspecialchars($mod['title']);?>" /></td>
                                            <td style="width:15px;padding-left:10px;padding-right:10px;">
                                                <input type="checkbox" title="<?php echo $_LANG['AD_VIEW_TITLE']; ?>" name="showtitle" <?php if ($mod['showtitle'] || $do=='add') { echo 'checked="checked"'; } ?> value="1">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                            <td width="130" valign="top">
                                <div><strong><?php echo $_LANG['AD_PUBLIC_DATE']; ?></strong></div>
                                <div>
                                    <input name="pubdate" type="text" id="pubdate" style="width:100px" <?php if(@!$mod['pubdate']) { echo 'value="'.date('d.m.Y').'"'; } else { echo 'value="'.$mod['pubdate'].'"'; } ?>/>

                                    <input type="hidden" name="olddate" value="<?php echo @$mod['pubdate']?>" />
                                </div>
                            </td>
                            <td width="16" valign="bottom" style="padding-bottom:10px">
                                <input type="checkbox" name="showdate" id="showdate" title="<?php echo $_LANG['AD_VIEW_DATE_AND_AUTHOR']; ?>" value="1" <?php if ($mod['showdate'] || $do=='add') { echo 'checked="checked"'; } ?>/>
                            </td>
                            <td width="160" valign="top">
                                <div><strong><?php echo $_LANG['AD_ARTICLE_TEMPLATE']; ?></strong></div>
                                <div><input name="tpl" type="text" style="width:160px" value="<?php echo @$mod['tpl'];?>"></div>
                            </td>

                        </tr>
                    </table>

                    <div><strong><?php echo $_LANG['AD_ARTICLE_NOTICE']; ?></strong> <?php printLangPanel('content_content', @$mod['id'], 'description'); ?></div>
                    <div><?php $inCore->insertEditor('description', $mod['description'], '200', '100%'); ?></div>

                    <div><strong><?php echo $_LANG['AD_ARTICLE_TEXT']; ?></strong> <?php printLangPanel('content_content', @$mod['id'], 'content'); ?></div>
                    <?php insertPanel(); ?>
                    <div><?php $inCore->insertEditor('content', $mod['content'], '400', '100%'); ?></div>

                    <div><strong><?php echo $_LANG['AD_ARTICLE_TAGS']; ?></strong></div>
                    <div><input name="tags" type="text" id="tags" style="width:99%" value="<?php if (isset($mod['id'])) { echo cmsTagLine('content', $mod['id'], false); } ?>" /></div>

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20">
                                <input type="radio" name="autokeys" id="autokeys1" <?php if ($do=='add' && $cfg['autokeys']){ ?>checked="checked"<?php } ?> value="1"/>
                            </td>
                            <td>
                                <label for="autokeys1"><strong><?php echo $_LANG['AD_AUTO_GEN_KEY']; ?></strong></label>
                            </td>
                        </tr>
                        <tr>
                            <td width="20">
                                <input type="radio" name="autokeys" id="autokeys2" value="2"/>
                            </td>
                            <td>
                                <label for="autokeys2"><strong><?php echo $_LANG['AD_TAGS_AS_KEY']; ?></strong></label>
                            </td>
                        </tr>
                        <tr>
                            <td width="20">
                                <input type="radio" name="autokeys" id="autokeys3" value="3" <?php if ($do=='edit' || !$cfg['autokeys']){ ?>checked="checked"<?php } ?>/>
                            </td>
                            <td>
                                <label for="autokeys3"><strong><?php echo $_LANG['AD_MANUAL_KEY'] ; ?></strong></label>
                            </td>
                        </tr>

                        <?php if ($cfg['af_on'] && $do=='add') { ?>
                        <tr>
                            <td width="20"><input type="checkbox" name="noforum" id="noforum" value="1" /> </td>
                            <td><label for="noforum"><strong><?php echo $_LANG['AD_NO_CREATE_THEME']; ?></strong></label></td>
                        </tr>
                        <?php } ?>
                    </table>

                </td>

                <!-- боковая ячейка -->
                <td width="300" valign="top" style="background:#ECECEC;">

                    <?php ob_start(); ?>

                    {tab=<?php echo $_LANG['AD_TAB_PUBLISH']; ?>}

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20"><input type="checkbox" name="published" id="published" value="1" <?php if ($mod['published'] || $do=='add') { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="published"><strong><?php echo $_LANG['AD_PUBLIC_ARTICLE']; ?></strong></label></td>
                        </tr>
                    </table>

                    <div style="margin-top:7px">
                        <select name="category_id" size="10" id="category_id" style="width:99%;height:200px">
                            <option value="1" <?php if (@$mod['category_id']==1 || !isset($mod['category_id'])) { echo 'selected="selected"'; }?>><?php echo $_LANG['AD_ROOT_CATEGORY'] ; ?></option>
                            <?php
                                if (isset($mod['category_id'])){
                                    echo $inCore->getListItemsNS('cms_category', $mod['category_id']);
                                } else {
                                    echo $inCore->getListItemsNS('cms_category');
                                }
                            ?>
                        </select>
                    </div>

                    <div style="margin-bottom:10px">
                        <select name="showpath" id="showpath" style="width:99%">
                            <option value="0" <?php if (@!$mod['showpath']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_PATHWAY_NAME_ONLY']; ?></option>
                            <option value="1" <?php if (@$mod['showpath']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_PATHWAY_FULL']; ?></option>
                        </select>
                    </div>

                    <div style="margin-top:15px">
                        <strong><?php echo $_LANG['AD_ARTICLE_URL']; ?></strong><br/>
                        <div style="color:gray"><?php echo $_LANG['AD_IF_UNKNOWN']; ?></div>
                    </div>
                    <div>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td><input type="text" name="url" value="<?php echo $mod['url']; ?>" style="width:100%"/></td>
                                <td width="40" align="center">.html</td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-top:10px">
                        <strong><?php echo $_LANG['AD_ARTICLE_AUTHOR']; ?></strong>
                    </div>
                    <div>
                        <select name="user_id" id="user_id" style="width:99%">
                          <?php
                              if (isset($mod['user_id'])) {
                                    echo $inCore->getListItems('cms_users', $mod['user_id'], 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                              } else {
                                    echo $inCore->getListItems('cms_users', $inUser->id, 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                              }
                          ?>
                        </select>
                    </div>

                    <div style="margin-top:12px"><strong><?php echo $_LANG['AD_PHOTO']; ?></strong></div>
                    <div style="margin-bottom:10px">
                        <?php
                            if ($do=='edit'){
                                if (file_exists(PATH.'/images/photos/small/article'.$mod['id'].'.jpg')){
                        ?>
                        <div style="margin-top:3px;margin-bottom:3px;padding:10px;border:solid 1px gray;text-align:center">
                            <img src="/images/photos/small/article<?php echo $id; ?>.jpg" border="0" />
                        </div>
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="16"><input type="checkbox" id="delete_image" name="delete_image" value="1" /></td>
                                <td><label for="delete_image"><?php echo $_LANG['AD_PHOTO_REMOVE']; ?></label></td>
                            </tr>
                        </table>
                        <?php
                                }
                            }
                        ?>
                        <input type="file" name="picture" style="width:100%" />
                    </div>

                    <div style="margin-top:25px"><strong><?php echo $_LANG['AD_PUBLIC_PARAMETRS']; ?></strong></div>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20"><input type="checkbox" name="showlatest" id="showlatest" value="1" <?php if ($mod['showlatest'] || $do=='add') { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="showlatest"><?php echo $_LANG['AD_VIEW_NEW_ARTICLES']; ?></label></td>
                        </tr>
                        <tr>
                            <td width="20"><input type="checkbox" name="comments" id="comments" value="1" <?php if ($mod['comments'] || $do=='add') { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="comments"><?php echo $_LANG['AD_ENABLE_COMMENTS']; ?></label></td>
                        </tr>
                        <tr>
                            <td width="20"><input type="checkbox" name="canrate" id="canrate" value="1" <?php if ($mod['canrate']) { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="canrate"><?php echo $_LANG['AD_ENABLE_RATING']; ?></label></td>
                        </tr>
                    </table>

                    <?php if ($do=='add'){ ?>
                        <div style="margin-top:25px">
                            <strong><?php echo $_LANG['AD_CREATE_LINK']; ?></strong>
                        </div>
                        <div>
                            <select name="createmenu" id="createmenu" style="width:99%">
                                <option value="0" selected="selected"><?php echo $_LANG['AD_DONT_CREATE_LINK']; ?></option>
                            <?php foreach ($menu_list as $menu) { ?>
                                <option value="<?php echo $menu['id']; ?>">
                                    <?php echo $menu['title']; ?>
                                </option>
                            <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    {tab=<?php echo $_LANG['AD_DATE']; ?>}

                    <div style="margin-top:5px">
                        <strong><?php echo $_LANG['AD_ARTICLE_TIME']; ?></strong>
                    </div>
                    <div>
                        <select name="is_end" id="is_end" style="width:99%" onchange="if($(this).val() == 1){ $('#final_time').show(); }else {$('#final_time').hide();}">
                            <option value="0" <?php if (@!$mod['is_end']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_UNLIMITED']; ?></option>
                            <option value="1" <?php if (@$mod['is_end']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TO_FINAL_TIME']; ?></option>
                        </select>
                    </div>

                    <div id="final_time" <?php if (@!$mod['is_end']) { echo 'style="display: none"'; } ?>>
                    <div style="margin-top:20px">
                        <strong><?php echo $_LANG['AD_FINAL_TIME']; ?></strong><br/>
                        <span class="hinttext"><?php echo $_LANG['AD_CALENDAR_FORMAT']; ?></span>
                    </div>
                    <div><input name="enddate" type="text" style="width:80%" <?php if(@!$mod['is_end']) { echo 'value="'.date('d.m.Y').'"'; } else { echo 'value="'.$mod['enddate'].'"'; } ?>id="enddate" /></div></div>


                    {tab=SEO}

                    <div style="margin-top:5px">
                        <strong><?php echo $_LANG['AD_PAGE_TITLE']; ?></strong> <?php printLangPanel('content_content', @$mod['id'], 'pagetitle'); ?><br/>
                        <span class="hinttext"><?php echo $_LANG['AD_IF_UNKNOWN_PAGETITLE']; ?></span>
                    </div>
                    <div>
                        <input name="pagetitle" type="text" id="pagetitle" style="width:99%" value="<?php if (isset($mod['pagetitle'])) { echo htmlspecialchars($mod['pagetitle']); } ?>" />
                    </div>

                    <div style="margin-top:20px">
                        <strong><?php echo $_LANG['KEYWORDS']; ?></strong> <?php printLangPanel('content_content', @$mod['id'], 'meta_keys'); ?><br/>
                        <span class="hinttext"><?php echo $_LANG['AD_FROM_COMMA']; ?></span>
                    </div>
                    <div>
                         <textarea name="meta_keys" style="width:97%" rows="4" id="meta_keys"><?php echo htmlspecialchars($mod['meta_keys']);?></textarea>
                    </div>

                    <div style="margin-top:20px">
                        <strong><?php echo $_LANG['DESCRIPTION']; ?></strong> <?php printLangPanel('content_content', @$mod['id'], 'meta_desc'); ?><br/>
                        <span class="hinttext"><?php echo $_LANG['AD_LESS_THAN']; ?></span>
                    </div>
                    <div>
                         <textarea name="meta_desc" style="width:97%" rows="6" id="meta_desc"><?php echo htmlspecialchars($mod['meta_desc']);?></textarea>
                    </div>

                    {tab=<?php echo $_LANG['AD_TAB_ACCESS']; ?>}

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist" style="margin-top:5px">
                        <tr>
                            <td width="20">
                                <?php
                                    $sql    = "SELECT * FROM cms_user_groups";
                                    $result = $inDB->query($sql) ;

                                    $style  = 'disabled="disabled"';
                                    $public = 'checked="checked"';

                                    if ($do == 'edit'){

                                        $sql2 = "SELECT * FROM cms_content_access WHERE content_id = ".$mod['id']." AND content_type = 'material'";
                                        $result2 = $inDB->query($sql2);
                                        $ord = array();

                                        if ($inDB->num_rows($result2)){
                                            $public = '';
                                            $style = '';
                                            while ($r = $inDB->fetch_assoc($result2)){
                                                $ord[] = $r['group_id'];
                                            }
                                        }
                                    }
                                ?>
                                <input name="is_public" type="checkbox" id="is_public" onclick="checkGroupList()" value="1" <?php echo $public?> />
                            </td>
                            <td><label for="is_public"><strong><?php echo $_LANG['AD_SHARE']; ?></strong></label></td>
                        </tr>
                    </table>
                    <div style="padding:5px">
                        <span class="hinttext">
                            <?php echo $_LANG['AD_IF_NOTED']; ?>
                        </span>
                    </div>

                    <div style="margin-top:10px;padding:5px;padding-right:0px;" id="grp">
                        <div>
                            <strong><?php echo $_LANG['AD_GROUPS_VIEW']; ?></strong><br />
                            <span class="hinttext">
                                <?php echo $_LANG['AD_SELECT_MULTIPLE_CTRL']; ?>
                            </span>
                        </div>
                        <div>
                            <?php
                                echo '<select style="width: 99%" name="showfor[]" id="showin" size="6" multiple="multiple" '.$style.'>';

                                if ($inDB->num_rows($result)){
                                    while ($item = $inDB->fetch_assoc($result)){
                                        echo '<option value="'.$item['id'].'"';
                                        if ($do=='edit'){
                                            if (inArray($ord, $item['id'])){
                                                echo 'selected="selected"';
                                            }
                                        }

                                        echo '>';
                                        echo $item['title'].'</option>';
                                    }
                                }

                                echo '</select>';
                            ?>
                        </div>
                    </div>

                    {/tabs}

                    <?php echo jwTabs(ob_get_clean()); ?>

                </td>

            </tr>
        </table>

        <p>
            <input name="add_mod" type="submit" id="add_mod" <?php if ($do=='add') { echo 'value="'.$_LANG['AD_CREATE_CONTENT'].'"'; } else { echo 'value="'.$_LANG['AD_SAVE_CONTENT'].'"'; } ?> />
            <input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.history.back();"/>
            <input name="do" type="hidden" id="do" <?php if ($do=='add') { echo 'value="submit"'; } else { echo 'value="update"'; } ?> />
            <?php
                if ($do=='edit'){
                    echo '<input name="id" type="hidden" value="'.$mod['id'].'" />';
                }
            ?>
        </p>
    </form>
    <?php
    }

} ?>

<?php
if (!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }
/* * **************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.6                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/* * **************************************************************************/
function autoOrder($form_id) {

    $inDB = cmsDatabase::getInstance();

    $sql = "SELECT * FROM cms_form_fields WHERE form_id = '$form_id' ORDER BY ordering";
    $rs  = $inDB->query($sql);

    if ($inDB->num_rows($rs)) {
        $ord = 1;
        while ($item = $inDB->fetch_assoc($rs)) {
            $inDB->query("UPDATE cms_form_fields SET ordering = $ord WHERE id= '{$item['id']}'");
            $ord += 1;
        }
    }
    return true;
}
function moveField($id, $form_id, $dir) {

    $inDB = cmsDatabase::getInstance();

    $sign = $dir>0 ? '+' : '-';

    $current = $inDB->get_field('cms_form_fields', "id='{$id}'", 'ordering');
    if($current === false){ return false; }

    if ($dir>0){

        $sql = "UPDATE cms_form_fields
                SET ordering = ordering-1
                WHERE form_id='{$form_id}' AND ordering = ({$current}+1)
                LIMIT 1";
        $inDB->query($sql);
    }
    if ($dir<0){

        if($current == 1) { return false; }

        $sql = "UPDATE cms_form_fields
                SET ordering = ordering+1
                WHERE form_id='{$form_id}' AND ordering = ({$current}-1)
                LIMIT 1";
        $inDB->query($sql);
    }

    $sql    = "UPDATE cms_form_fields
               SET ordering = ordering {$sign} 1
               WHERE id='{$id}'";
    $inDB->query($sql);

    return true;

}

require('../includes/jwtabs.php');

$GLOBALS['cp_page_head'][] = '<script language="JavaScript" type="text/javascript" src="js/forms.js"></script>';
$GLOBALS['cp_page_head'][] = jwHeader();

$opt = cmsCore::request('opt', 'str', 'list');

$toolmenu[] = array('icon'=>'newform.gif','title'=>$_LANG['AD_NEW_FORM'],'link'=>'?view=components&do=config&id='.$id.'&opt=add');
$toolmenu[] = array('icon'=>'listforms.gif','title'=>$_LANG['AD_FORMS'],'link'=>'?view=components&do=config&id='.$id.'&opt=list');

cpToolMenu($toolmenu);

cmsCore::loadClass('form');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($opt == 'up_field') {

    moveField(cmsCore::request('item_id', 'int'), cmsCore::request('form_id', 'int'), -1);

    cmsCore::redirectBack();

}

if ($opt == 'down_field') {

    moveField(cmsCore::request('item_id', 'int'), cmsCore::request('form_id', 'int'), 1);

    cmsCore::redirectBack();

}

if ($opt == 'del_field') {

    $item_id = cmsCore::request('item_id', 'int');
    $form_id = cmsCore::request('form_id', 'int');

    $inDB->delete('cms_form_fields', "id = '{$item_id}'", 1);

    autoOrder($form_id);

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS']);

    cmsCore::redirectBack();

}

if (in_array($opt, array('add_field', 'update_field'))) {

    $item['kind']     = cmsCore::request('kind', 'str', '');
    $item['title']    = cmsCore::request('f_title', 'str', 'NO_TITLE');
    $item['description'] = cmsCore::request('f_description', 'str', '');
    $item['ordering'] = cmsCore::request('f_order', 'int');
    $item['form_id']  = cmsCore::request('form_id', 'int');
    $item['mustbe']   = cmsCore::request('mustbe', 'int');

    $item['config'] = array();

    $item['config']['text_is_link'] = cmsCore::request('text_is_link', 'int');
    $item['config']['text_link_prefix'] = cmsCore::request('text_link_prefix', 'str', '');
	$item['config']['max'] = cmsCore::request('text_max', 'int');

    switch ($item['kind']) {
        case 'text':

            $item['config']['size']    = cmsCore::request('f_text_size', 'int');
            break;

        case 'link':

            $item['config']['size']    = cmsCore::request('f_link_size', 'int');
            break;

        case 'textarea':

            $item['config']['size']    = cmsCore::request('f_ta_size', 'int');
            $item['config']['rows']    = cmsCore::request('f_ta_rows', 'int');
            $item['config']['default'] = cmsCore::request('f_ta_default', 'str', '');
            break;

        case 'checkbox':

            $item['config']['checked'] = cmsCore::request('f_checked', 'int');
            break;

        case 'radiogroup':

            $item['config']['items'] = cmsCore::request('f_rg_list', 'str', '');
            break;

        case 'list':

            $item['config']['items'] = cmsCore::request('f_list_list', 'str', '');
            $item['config']['size']  = cmsCore::request('f_list_size', 'int');
            break;

        case 'menu':

            $item['config']['items'] = cmsCore::request('f_menu_list', 'str', '');
            $item['config']['size']  = cmsCore::request('f_menu_size', 'int');
            break;

        case 'file':

            $exts = cmsCore::request('f_file_ext', 'str', '');

            while (mb_strpos($exts, 'htm') ||
                   mb_strpos($exts, 'php') ||
                   mb_strpos($exts, 'ht')) {
                $exts  = str_replace(array('htm','php','ht'), '', mb_strtolower($exts));
            }
            $item['config']['ext']   = str_replace(' ', '', $exts);
            $item['config']['size']  = cmsCore::request('f_file_size', 'int');
            break;
    }

    $item['config'] = $inDB->escape_string(cmsCore::arrayToYaml($item['config']));

    if($opt == 'add_field'){

        $inDB->insert('cms_form_fields', cmsCore::callEvent('ADD_FORM_FIELD', $item));

    } else {

        $inDB->update('cms_form_fields', cmsCore::callEvent('UPDATE_FORM_FIELD', $item), cmsCore::request('field_id', 'int'));

    }
    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS']);
    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=edit&item_id='.$item['form_id']);
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (in_array($opt, array('submit', 'update'))) {

    if (!cmsUser::checkCsrfToken()) { cmsCore::error404(); }

    $item['title']       = cmsCore::request('title', 'str', $_LANG['AD_FORM_UNTITLED']);
    $item['description'] = $inDB->escape_string(cmsCore::request('description', 'html', ''));

    $item['sendto']  = cmsCore::request('sendto', 'str', '');
    $item['email']   = cmsCore::request('email', 'str', '');
    $item['user_id'] = cmsCore::request('user_id', 'int', 0);
    $item['form_action'] = cmsCore::request('form_action', 'str', '/forms/process');
    $item['only_fields'] = cmsCore::request('only_fields', 'int', 0);
    $item['showtitle'] = cmsCore::request('showtitle', 'int', 0);
    $item['tpl']       = cmsCore::request('tpl', 'str', 'form');

    if($opt == 'submit'){

        $form_id = $inDB->insert('cms_forms', cmsCore::callEvent('ADD_FORM', $item));
        cmsCore::addSessionMessage($_LANG['AD_FORM_SUCCESFULL_CREATED']);

    } else {

        $form_id = cmsCore::request('item_id', 'int');

        $inDB->update('cms_forms', cmsCore::callEvent('UPDATE_FORM', $item), $form_id);
        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'].'.');

    }

    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=edit&item_id='.$form_id);

}

if ($opt == 'delete') {

    $item_id = cmsCore::request('item_id', 'int');
    $mod = $inDB->get_fields('cms_forms', "id = '{$item_id}'", '*');
    if(!$mod){ cmsCore::error404(); }

    cmsCore::callEvent('DELETE_FORM', $item_id);

    $inDB->delete('cms_forms', "id = '{$item_id}'", 1);

    $inDB->delete('cms_form_fields', "form_id = '{$item_id}'");

    files_remove_directory(PATH.'/upload/forms/'.$item_id);

    cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'].'.');

    cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=list');

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($opt == 'list') {

    $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
    $fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit&item_id=%id%');
    $fields[] = array('title'=>$_LANG['AD_E-MAIL'], 'field'=>'email', 'width'=>'150');

    $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit&item_id=%id%');
    $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_FORM_DELETE'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete&item_id=%id%');

    cpListTable('cms_forms', $fields, $actions);

}

if (in_array($opt, array('add', 'edit'))) {

    if ($opt == 'add') {

        cpAddPathway($_LANG['AD_NEW_FORM']);
        echo '<h3>'.$_LANG['AD_NEW_FORM'].'</h3>';

        $mod['showtitle']   = 1;
        $mod['form_action'] = '/forms/process';
        $mod['tpl']         = 'form';
        $mod['only_fields'] = 0;

    } else {

        $item_id  = cmsCore::request('item_id', 'int');
        $field_id = cmsCore::request('field_id', 'int');

        $mod = $inDB->get_fields('cms_forms', "id = '{$item_id}'", '*');

        $field = $inDB->get_fields('cms_form_fields', "id='{$field_id}'", '*');
        if($field){
            $field['config'] = cmsCore::yamlToArray($field['config']);
        }

        echo '<h3>'.$_LANG['AD_FORM']. ': ' . $mod['title'] . '</h3>';
        cpAddPathway($mod['title']);

        ob_start();

        echo '{tab='.$_LANG['AD_FORM_PROPERTIES'].'}';

    } ?>

    <form id="addform" name="addform" method="post" action="index.php?view=components&do=config&id=<?php echo $id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <table width="605" border="0" cellspacing="5" class="proptable">
            <tr>
                <td width="200"><strong><?php echo $_LANG['AD_FORM_NAME']; ?>: </strong></td>
                <td width=""><input name="title" type="text" id="title" size="30" value="<?php echo htmlspecialchars(@$mod['title']); ?>" style="width:220px;"/></td>
            </tr>
            <tr>
                <td><strong><?php echo $_LANG['AD_FORM_DESTINATION']; ?>: </strong></td>
                <td>
                    <select name="sendto" id="sendto" style="width:220px;" onChange="toggleSendTo()">
                        <option value="mail" <?php if (@$mod['sendto'] == 'mail' || !isset($mod['sendto'])) { echo 'selected'; } ?>><?php echo $_LANG['AD_EMAIL_ADDRESS']; ?></option>
                        <option value="user" <?php if (@$mod['sendto'] == 'user') { echo 'selected'; } ?>><?php echo $_LANG['AD_PERSON_MESS']; ?></option>
                    </select>
                </td>
            </tr>

            <tr>
                <td width="200"><strong><?php echo $_LANG['AD_VIEW_FORM_TITLE']; ?>: </strong></td>
                <td width="">
                    <label><input name="showtitle" type="radio" value="1" <?php if ($mod['showtitle']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="showtitle" type="radio" value="0" <?php if (!$mod['showtitle']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td width="200"><strong><?php echo $_LANG['AD_FORM_ACTION']; ?>: </strong></td>
                <td width="">
                    <input name="form_action" type="text" size="30" value="<?php echo htmlspecialchars(@$mod['form_action']); ?>" style="width:220px;"/>
                </td>
            </tr>
            <tr>
                <td width="200"><strong><?php echo $_LANG['AD_FILED_ONLY']; ?>: </strong></td>
                <td width="">
                    <label><input name="only_fields" type="radio" value="1" <?php if ($mod['only_fields']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                    <label><input name="only_fields" type="radio" value="0" <?php if (!$mod['only_fields']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                </td>
            </tr>
            <tr>
                <td width="200"><strong><?php echo $_LANG['AD_FORM_TPL']; ?>: </strong><br><span class="hinttext"><?php echo $_LANG['AD_FORM_TPL_HINT']; ?></span></td>
                <td width="">
                    <input name="tpl" type="text" size="30" value="<?php echo htmlspecialchars(@$mod['tpl']); ?>" style="width:220px;"/>
                </td>
            </tr>
        </table>
        <div id="sendto_mail" <?php if (@$mod['sendto'] == 'mail' || !isset($mod['sendto'])) {
                            echo 'style="display:block"';
                        } else {
                            echo 'style="display:none"';
                        } ?>>
            <table width="605" border="0" cellspacing="5" class="proptable">
                <tr>
                    <td width="16" valign="top"><img src="/admin/components/forms/email.gif" width="16" height="16"></td>
                    <td width="178">
                        <strong><?php echo $_LANG['AD_E-MAIL_ADDR']; ?>: </strong><br>
                        <span class="hinttext"><?php echo $_LANG['AD_E-MAIL_ADDR_HINT']; ?></span>
                    </td>
                    <td><input name="email" type="text" id="email" size="30" value="<?php echo @$mod['email']; ?>" style="width:220px;"/></td>
                </tr>
            </table>
        </div>
        <div id="sendto_user" <?php if (@$mod['sendto'] == 'user') {
                            echo 'style="display:block"';
                        } else {
                            echo 'style="display:none"';
                        } ?>>
            <table width="605" border="0" cellspacing="5" class="proptable">
                <tr>
                    <td width="16"><img src="/admin/components/forms/user.gif" width="16" height="16"></td>
                    <td width="178"><strong><?php echo $_LANG['AD_RECIPIENT']; ?>: </strong></td>
                    <td>
                        <select name="user_id" id="user_id" style="width:220px">
                            <?php
                            if (isset($mod['user_id'])) {
                                echo $inCore->getListItems('cms_users', $mod['user_id'], 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                            } else {
                                echo $inCore->getListItems('cms_users', 0, 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <table width="100%" border="0">
            <tr>
                <td width="52%" valign="top">
                    <p><strong><?php echo $_LANG['AD_FORM_EXPLANT']; ?>:</strong></p>
                    <?php $inCore->insertEditor('description', $mod['description'], '280', '100%'); ?>
                </td>
           </tr>
        </table>
        <?php if ($opt == 'add') {
            echo '<p><b>'.$_LANG['AD_NOTE'].': </b>'.$_LANG['AD_AFTER_CREATE'].'. </p>';
        } else {
            echo '<p><b>'.$_LANG['AD_NOTE'].': </b>' .$_LANG['AD_TO_INSERT'];
        }
        ?>
        <p>
            <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
            <input name="opt" type="hidden" id="do" <?php if ($opt == 'add') { echo 'value="submit"'; } else { echo 'value="update"'; } ?> />
    <?php
    if ($opt == 'edit') {
        echo '<input name="item_id" type="hidden" value="' . $mod['id'] . '" />';
    } ?>

        </p>
    </form>
    <?php if ($opt == 'edit') {
        $last_order = 1 + $inDB->get_field('cms_form_fields', "form_id='{$mod['id']}' ORDER BY ordering DESC", 'ordering'); ?>

        {tab=<?php echo $_LANG['AD_FIELDS']; ?>}
        <table width="761" cellpadding="8" cellspacing="5">
            <tr>
                <td width="300" valign="top" class="proptable">
                    <h4 style="border-bottom:solid 1px black; font-size: 14px; margin-bottom: 10px"><b><?php if(!@$field){ ?><?php echo $_LANG['AD_FIELD_ADD']; ?><?php } else { ?><?php echo $_LANG['AD_FIELD_EDIT']; ?><?php } ?></b></h4>
                    <form id="fieldform" name="fieldform" method="post" action="index.php?view=components&do=config&id=<?php echo $id; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
                        <input type="hidden" name="opt" value="<?php if(!@$field){ ?>add_field<?php } else { ?>update_field<?php } ?>"/>
                        <input name="form_id" type="hidden" id="form_id" value="<?php echo @$mod['id'] ?>"/>
                        <input name="field_id" type="hidden" value="<?php echo @$field['id'] ?>"/>
                        <table width="100%" border="0" cellspacing="2" cellpadding="2">
                            <tr>
                                <td width="100"><?php echo $_LANG['AD_FIELD_TYPE']; ?>:</td>
                                <td>
                                    <select name="kind" id="kind" onchange="show()">
                                        <option value="text" <?php if (@$field['kind'] == 'text' || !@$field['kind']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TYPE_TEXT']; ?></option>
                                        <option value="link" <?php if (@$field['kind'] == 'link') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TYPE_URL']; ?></option>
                                        <option value="textarea" <?php if (@$field['kind'] == 'textarea') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TYPE_MILTILINE']; ?></option>
                                        <option value="checkbox" <?php if (@$field['kind'] == 'checkbox') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TYPE_YES_NO']; ?></option>
                                        <option value="radiogroup" <?php if (@$field['kind'] == 'radiogroup') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TYPE_GROUP_OPTIONS'] ; ?></option>
                                        <option value="list" <?php if (@$field['kind'] == 'list') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TYPE_DROP_DOWN']; ?></option>
                                        <option value="menu" <?php if (@$field['kind'] == 'menu') { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_TYPE_VISIBLE']; ?></option>
                                        <option value="file" <?php if (@$field['kind'] == 'file') { echo 'selected="selected"'; } ?>><?php echo $_LANG['FILE']; ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $_LANG['AD_TITLE']; ?>:</td>
                                <td><input name="f_title" type="text" id="f_title" size="25" value="<?php echo htmlspecialchars(@$field['title']) ?>" /></td>
                            </tr>
                            <tr>
                                <td><?php echo $_LANG['DESCRIPTION']; ?>:</td>
                                <td><input name="f_description" type="text" id="f_description" size="25" value="<?php echo htmlspecialchars(@$field['description']) ?>" /></td>
                            </tr>
                            <tr>
                                <td><?php echo $_LANG['AD_FIELD_ORDER']; ?>:</td>
                                <td><input class="uispin" name="f_order" type="text" id="f_order" value="<?php if(!@$field) { echo $last_order; } else { echo @$field['ordering']; } ?>" size="6" /></td>
                            </tr>
                            <tr>
                                <td><?php echo $_LANG['AD_FIELD_FILLING']; ?>:</td>
                                <td><select name="mustbe" id="mustbe">
                                        <option value="1" <?php if (@$field['mustbe']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_NECESSARILY']; ?></option>
                                        <option value="0" <?php if (!@$field['mustbe']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_NOT_NECESSARILY']; ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="text_field">
                                <td><?php echo $_LANG['AD_VALUE_LINK']; ?>?</td>
                                <td>
                                    <label><input name="text_is_link"
                                                  onclick="$('#text_link_prefix').show();"
                                                  type="radio" value="1" <?php if (@$field['config']['text_is_link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['YES']; ?></label>
                                    <label><input name="text_is_link"
                                                  onclick="$('#text_link_prefix').hide();"
                                                  type="radio" value="0" <?php if (!@$field['config']['text_is_link']) { echo 'checked="checked"'; } ?>/> <?php echo $_LANG['NO']; ?></label>
                                </td>
                            </tr>
                            <tr id="text_link_prefix" <?php if(!@$field['config']['text_is_link']) { echo 'style="display:none"'; } ?>>
                                <td><?php echo $_LANG['AD_LINK_PREFIX']; ?>:</td>
                                <td><input name="text_link_prefix" type="text" size="25" value="<?php echo (@$field['config']['text_link_prefix'] ? $field['config']['text_link_prefix'] : '/users/hobby/'); ?>" /></td>
                            </tr>
                            <tr class="text_field">
                                <td><?php echo $_LANG['AD_MAXIMUM_LENGTH']; ?>:</td>
                                <td><input class="uispin" name="text_max" type="text" size="6" value="<?php echo (isset($field['config']['max']) ? $field['config']['max'] : 300) ?>" /> <?php echo $_LANG['AD_CHARACTERS']; ?> </td>
                            </tr>
                        </table>

                        <div id="kind_text">
                            <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                <tr>
                                    <td width="100"><?php echo $_LANG['AD_WIDTH']; ?>:</td>
                                    <td><input class="uispin" name="f_text_size" type="text" id="f_text_size" value="<?php echo (@$field['config']['size'] ? $field['config']['size'] : 160) ?>" size="6" />  <?php echo $_LANG['AD_PX'] ; ?> </td>
                                </tr>
                            </table>
                        </div>
                        <div id="kind_link" style="display:none">
                            <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                <tr>
                                    <td width="100"><?php echo $_LANG['AD_WIDTH']; ?>:</td>
                                    <td><input class="uispin" name="f_link_size" type="text" id="f_text_size" value="<?php echo (@$field['config']['size'] ? $field['config']['size'] : 160) ?>" size="6" />  <?php echo $_LANG['AD_PX'] ; ?> </td>
                                </tr>
                            </table>
                        </div>
                        <div id="kind_textarea" style="display:none">
                            <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                <tr>
                                    <td width="100"><?php echo $_LANG['AD_WIDTH']; ?>:</td>
                                    <td><input class="uispin" name="f_ta_size" type="text" id="f_ta_size" value="<?php echo (@$field['config']['size'] ? $field['config']['size'] : 160) ?>" size="6" /> <?php echo $_LANG['AD_PX'] ; ?> </td>
                                </tr>
                                <tr>
                                    <td><?php echo $_LANG['AD_STRINGS']; ?>:</td>
                                    <td><input class="uispin" name="f_ta_rows" type="text" id="f_ta_rows" value="<?php echo (@$field['config']['rows'] ? $field['config']['rows'] : 5) ?>" size="6" /></td>
                                </tr>
                            </table>
                        </div>
                        <div id="kind_checkbox" style="display:none">
                            <div id="div" >
                                <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                    <tr>
                                        <td width="100"><?php echo $_LANG['AD_MARK']; ?>:</td>
                                        <td><select name="f_checked" id="f_checked">
                                                <option value="1" <?php if (@$field['config']['checked']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_MARKED']; ?></option>
                                                <option value="0" <?php if (!@$field['config']['checked']) { echo 'selected="selected"'; } ?>><?php echo $_LANG['AD_NOT_MARKED']; ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div id="kind_radiogroup" style="display:none">
                            <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                <tr>
                                    <td width="100"><?php echo $_LANG['AD_ELEMENTS']; ?>:<br />
                                        <small><?php echo $_LANG['AD_THROUTH']; ?> "<b>/</b>"</small> </td>
                                    <td><textarea name="f_rg_list" cols="20" rows="5" id="f_rg_list"><?php echo htmlspecialchars(@$field['config']['items']) ?></textarea></td>
                                </tr>
                            </table>
                        </div>
                        <div id="kind_list" style="display:none">
                            <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                <tr>
                                    <td width="100"><?php echo $_LANG['AD_ELEMENTS']; ?>:<br />
                                        <small><?php echo $_LANG['AD_THROUTH']; ?> "<b>/</b>"</small> </td>
                                    <td><textarea name="f_list_list" cols="20" rows="5" id="f_list_list"><?php echo htmlspecialchars(@$field['config']['items']) ?></textarea></td>
                                </tr>
                                <tr>
                                    <td><?php echo $_LANG['AD_WIDTH']; ?>:</td>
                                    <td><input class="uispin" name="f_list_size" type="text" id="f_ta_size" value="<?php echo (@$field['config']['size'] ? $field['config']['size'] : 160) ?>" size="6" /> <?php echo $_LANG['AD_PX']; ?> </td>
                                </tr>
                            </table>
                        </div>
                        <div id="kind_menu" style="display:none">
                            <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                <tr>
                                    <td width="100"><?php echo $_LANG['AD_ELEMENTS']; ?>:<br />
                                        <small><?php echo $_LANG['AD_THROUTH']; ?> "<b>/</b>"</small> </td>
                                    <td><textarea name="f_menu_list" cols="20" rows="5" id="f_menu_list"><?php echo htmlspecialchars(@$field['config']['items']) ?></textarea></td>
                                </tr>
                                <tr>
                                    <td><?php echo $_LANG['AD_WIDTH']; ?>:</td>
                                    <td><input class="uispin" name="f_menu_size" type="text" id="f_ta_size" value="<?php echo (@$field['config']['size'] ? $field['config']['size'] : 160) ?>" size="6" /> <?php echo $_LANG['AD_PX']; ?> </td>
                                </tr>
                            </table>
                        </div>
                        <div id="kind_file" style="display:none">
                            <table width="100%" border="0" cellspacing="2" cellpadding="2">
                                <tr>
                                    <td width="100"><?php echo $_LANG['AD_EXT']; ?>:<br />
                                        <small><?php echo $_LANG['AD_EXT_HINT']; ?></small></td>
                                    <td><input name="f_file_ext" type="text" value="<?php echo @$field['config']['ext']; ?>" size="25" /></td>
                                </tr>
                                <tr>
                                    <td><?php echo $_LANG['AD_WIDTH']; ?>:</td>
                                    <td><input class="uispin" name="f_file_size" type="text" id="f_file_size" value="<?php echo (@$field['config']['size'] ? $field['config']['size'] : 160) ?>" size="6" /> <?php echo $_LANG['AD_PX']; ?> </td>
                                </tr>
                            </table>
                        </div>

                        <p>
                            <input type="submit" name="Submit" value="<?php if(!@$field){  echo $_LANG['AD_FIELD_ADD']; } else { echo $_LANG['AD_FIELD_SAVE']; } ?>" />
                        </p>
                    </form>

                </td>
                <td width="440" valign="top" class="proptable"><h4 style="border-bottom:solid 1px black;font-size: 14px; margin-bottom: 5px"><b><?php echo $_LANG['AD_PREVIEV']; ?> </b></h4>
                    <?php echo cmsForm::displayForm($item_id, array(), true); ?>
                </td>
            </tr>
        </table>
        <script type="text/javascript">
            $(document).ready(function(){
                show();
            });
        </script>

        {/tabs}
        <?php
        echo jwTabs(ob_get_clean());
        ?>
        <?php
    }
}
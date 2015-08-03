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

    $opt = cmsCore::request('opt', 'str', 'list');

    cmsCore::loadModel('polls');
    $model = new cms_model_polls();

    if ($opt == 'list'){
        $toolmenu[] = array('icon'=>'new.gif', 'title'=>$_LANG['AD_ADD_POLL'], 'link'=>'?view=components&do=config&id='.$id.'&opt=add');
    } else {
        $toolmenu[] = array('icon'=>'save.gif', 'title'=>$_LANG['SAVE'], 'link'=>'javascript:document.addform.submit();');
        $toolmenu[] = array('icon'=>'cancel.gif', 'title'=>$_LANG['CANCEL'], 'link'=>'?view=components&do=config&id='.$id);
    }

    cpToolMenu($toolmenu);

//=================================================================================================//
//=================================================================================================//

	if ($opt == 'list'){

		$fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
		$fields[] = array('title'=>$_LANG['TITLE'], 'field'=>'title', 'width'=>'', 'filter'=>'15', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit&poll_id=%id%');
		$fields[] = array('title'=>$_LANG['DATE'], 'field'=>'pubdate', 'width'=>'110', 'prc'=>array('cmsCore', 'dateFormat'));

        $actions[] = array('title'=>$_LANG['EDIT'], 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$id.'&opt=edit&poll_id=%id%');
        $actions[] = array('title'=>$_LANG['DELETE'], 'icon'=>'delete.gif', 'confirm'=>$_LANG['AD_DELETE_POLL'], 'link'=>'?view=components&do=config&id='.$id.'&opt=delete&poll_id=%id%');

		cpListTable('cms_polls', $fields, $actions);

	}

	if ($opt == 'submit'){

        function setupAnswers($answers_title) {
            $answers = array();
            foreach($answers_title as $answer){
                if ($answer) { $answers[$answer] = 0; }
            }
            return cmsCore::arrayToYaml($answers);
        }

        $types = array('title'=>array('title', 'str', ''),
                       'answers'=>array('answers', 'array_str', array(), 'setupAnswers'));

        $items = cmsCore::getArrayFromRequest($types);

		$inDB->insert('cms_polls', $items);

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');

        cmsCore::redirect('?view=components&do=config&id='.$id);

	}

	if($opt == 'delete'){

        $model->deletePoll(cmsCore::request('poll_id', 'int'));

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');

		cmsCore::redirect('?view=components&do=config&id='.$id);

	}

	if ($opt == 'update'){

        $poll_id       = cmsCore::request('poll_id', 'int');
		$item['title'] = cmsCore::request('title', 'str', '');
		$answers_title = cmsCore::request('answers', 'array_str');
        $nums          = cmsCore::request('num', 'array_int');

        $is_clear      = cmsCore::request('is_clear', 'int');

        if($is_clear){
            $inDB->delete('cms_polls_log', "poll_id = '$poll_id'");
        }

		$answers = array();

		foreach($answers_title as $key=>$answer){
			if ($answer) {
                if (isset($nums[$key]) && !$is_clear) {
                    $answers[$answer] = $nums[$key];
                }
                else {
                    $answers[$answer] = 0;
                }
            }
		}

		$item['answers'] = cmsCore::arrayToYaml($answers);

        $inDB->update('cms_polls', $item, $poll_id);

        cmsCore::addSessionMessage($_LANG['AD_DO_SUCCESS'], 'success');

		cmsCore::redirect('?view=components&do=config&id='.$id);

	}

	if($opt=='add' || $opt=='edit'){

		if ($opt=='add'){

            cpAddPathway($_LANG['AD_ADD_POLL']);

		} else {

			$mod = $model->getPoll(cmsCore::request('poll_id', 'int'));
			cpAddPathway($_LANG['AD_EDIT_POLL']);

            $answers_title = array();
            $answers_num   = array();
            $item = 1;
            foreach ($mod['answers'] as $answer=>$num){

                $answers_title[$item] = htmlspecialchars($answer);
                $answers_num[$item]   = $num;
                $item++;

            }

		}

?>
    <form id="addform" name="addform" method="post" action="index.php?view=components&do=config&id=<?php echo $id; ?>">
      <table width="600" border="0" cellspacing="5" class="proptable">
        <tr>
          <td width="200"><?php echo $_LANG['AD_QUESTION']; ?>: </td>
          <td width="213"><input name="title" type="text" id="title" size="30" value="<?php echo htmlspecialchars(@$mod['title']); ?>" /></td>
          <td width="173">&nbsp;</td>
        </tr>
        <?php for ($v=1; $v<=12; $v++) { ?>

        <tr>
          <td><?php echo $_LANG['AD_ANSWER']; ?> №<?php echo $v ?>:</td>
          <td><input name="answers[<?php echo $v ?>]" type="text" size="30" value="<?php echo @$answers_title[$v]; ?>" /></td>
          <td><?php if (isset($answers_num[$v])) { echo $_LANG['AD_VOTES'].': '.$answers_num[$v]; echo '<input type="hidden" name="num['.$v.']" value="'.$answers_num[$v].'" />';  } else { echo '&nbsp;'; } ?></td>
        </tr>

        <?php } ?>
      </table>

      <input name="add_mod" type="submit" id="add_mod" value="<?php echo $_LANG['SAVE']; ?>" />
      <input name="opt" type="hidden" id="opt" <?php if ($opt=='add') { echo 'value="submit"'; } else { echo 'value="update"'; } ?> />
      <?php
        if ($opt=='edit'){
            echo '<input name="poll_id" type="hidden" value="'.$mod['id'].'" /> ';
            echo ' <label><input name="is_clear" type="checkbox" value="1" /> '.$_LANG['AD_CLEAN_LOG'].'</label>';
        }
        ?>
    </form>

<?php } ?>
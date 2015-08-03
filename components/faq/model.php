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

class cms_model_faq{

	public function __construct(){
        $this->inDB = cmsDatabase::getInstance();
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'faq': $item = $this->inDB->get_fields('cms_faq_quests', "id={$target_id}", 'quest');
                        if (!$item) { return false; }
                        $result['link']     = '/faq/quest'.$target_id.'.html';
                        $result['title']    = (mb_strlen($item['quest'])<100 ? $item['quest'] : mb_substr($item['quest'], 0, 100).'...');
                        break;

        }

        return ($result ? $result : false);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function deleteQuest($id){

		$inCore = cmsCore::getInstance();

        $this->inDB->query("DELETE FROM cms_faq_quests WHERE id={$id}");

		$inCore->deleteComments('faq', $id);

        cmsActions::removeObjectLog('add_quest', $id);

        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteQuests($id_list){
        foreach($id_list as $key=>$id){
            $this->deleteQuest($id);
        }
        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

}
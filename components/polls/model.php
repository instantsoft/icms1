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

class cms_model_polls{

	public function __construct(){
		cmsCore::loadLanguage('components/polls');
        $this->inDB = cmsDatabase::getInstance();
    }

    public function getPoll($poll_id, $order = 'id ASC'){

        $where = $poll_id ? "id = '$poll_id'" : '1=1';

        $poll = $this->inDB->get_fields('cms_polls', $where, '*', $order);
        if(!$poll){ return false; }

        $poll['answers'] = cmsCore::yamlToArray($poll['answers']);
        $poll['total_answers'] = $this->getVoteCount($poll['answers']);

        return cmsCore::callEvent('GET_POLL', $poll);

    }

    public function deletePoll($poll_id){

        cmsCore::callEvent('DELETE_POLL', $poll_id);

        $sql = "DELETE FROM cms_polls WHERE id = '$poll_id' LIMIT 1";
        $this->inDB->query($sql);
        $sql = "DELETE FROM cms_polls_log WHERE poll_id = '$poll_id'";
        $this->inDB->query($sql);

        return true;

    }

    private function getVoteCount($poll_answers = array()){

        $count = 0;

        foreach($poll_answers as $num){
            $count += (int)$num;
        }

        return $count;

    }

    public function votePoll($poll, $answer){

        if(!$poll['answers']){ return false; }

        $inUser = cmsUser::getInstance();

        //Прибавляем голос к переданному нам варианту ответа
        foreach($poll['answers'] as $key=>$value){
            if ($key == stripslashes($answer)){
                $poll['answers'][$key] += 1;
            }
        }

        $answers = $this->inDB->escape_string(cmsCore::arrayToYaml($poll['answers']));

        //Сохраняем результаты опроса
        $sql = "UPDATE cms_polls SET answers = '{$answers}' WHERE id = '{$poll['id']}'";
        $this->inDB->query($sql);

        // помечаем кто за что проголосовал
        $sql = "INSERT cms_polls_log (poll_id, answer, user_id, ip)
                VALUES ('{$poll['id']}', '$answer', '{$inUser->id}', '{$inUser->ip}')";
        $this->inDB->query($sql);

        return true;

    }

    public function isUserVoted($poll_id){

        $inUser = cmsUser::getInstance();

        $sql = "SELECT 1
                FROM cms_polls_log
                WHERE ((ip = '{$inUser->ip}' AND user_id = '0') OR (user_id > 0 AND user_id='{$inUser->id}')) AND poll_id = '$poll_id'";

        $result = $this->inDB->query($sql);

        return $this->inDB->num_rows($result);

    }

}
?>

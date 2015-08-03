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

class p_morecontent extends cmsPlugin {

    public function __construct(){

		// Информация о плагине
        $this->info['plugin']      = 'p_morecontent';
        $this->info['title']       = 'Похожие статьи';
        $this->info['description'] = 'Добавляет в конец каждой статьи список похожих статей.';
        $this->info['author']      = 'Maximov & InstantCMS Team';
        $this->info['version']     = '1.10.3';

        // События, которые будут отлавливаться плагином
        $this->events[] = 'GET_ARTICLE';

        // Настройки по-умолчанию
        $this->config['P_LIMIT']  = 5;
        $this->config['P_UNSORT'] = 1;

        parent::__construct();

    }

// ==================================================================== //
    /**
     * Обработка событий
     * @param string $event
     * @param mixed $item
     * @return html
     */

    public function execute($event='', $item=array()){
        switch ($event){
            case 'GET_ARTICLE': $item = $this->eventGetArticle($item); break;
        }
        return $item;
    }

// ==================================================================== //

    private function eventGetArticle($item) {

        $inDB = cmsDatabase::getInstance();
        global $_LANG;

        $item_id = $item['id'];
  		$tag_arr = explode(', ', cmsTagLine('content', $item_id, false));
        $id_target = array();

		// Получаем id назначения таких же тегов, не более пяти на каждый
  		foreach ($tag_arr as $tag) {

    		$sql = "SELECT item_id FROM cms_tags WHERE tag = '$tag' AND item_id<>'$item_id' AND target='content' LIMIT 5";
			$rs = $inDB->query($sql) ;
			if ($inDB->num_rows($rs)) {
				while ($tagitem = $inDB->fetch_assoc($rs)){
					$id_target[]= $tagitem['item_id'];
				}
			}

        }

        if (count($id_target)){

	        $id_target	= array_unique($id_target);
	        $id_target 	= array_slice($id_target, 0, $this->config['P_LIMIT']);
	        if ($this->config['P_UNSORT']) shuffle($id_target);

			$morecontent = '';
	        foreach ($id_target as $n) {
				$con = cmsTagItemLink('content', $n);
				if($con){
	        		$morecontent .= '<p>'.$con."</p>";
				}
	        }
			if($morecontent) { $item['content'] .= '<h4>'.$_LANG['P_SIMILAR_ARTICLES'].':</h4>'.$morecontent; }
        }
        return $item;
	}
}
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

class p_demo extends cmsPlugin {

    public function __construct(){

        // Информация о плагине
        $this->info['plugin']      = 'p_demo';
        $this->info['title']       = 'Demo Plugin';
        $this->info['description'] = 'Пример плагина - Добавляет текст в конец каждой статьи на сайте';
        $this->info['author']      = 'InstantCMS Team';
        $this->info['version']     = '1.0';

        // Настройки по-умолчанию
        $this->config['text']    = 'Added By Plugin From Parameter';
        $this->config['color']   = 'blue';
        $this->config['counter'] = 1;

        // События, которые будут отлавливаться плагином
        $this->events[] = 'GET_ARTICLE';

        parent::__construct();

    }

// ==================================================================== //
    /**
     * Обработка событий
     * @param string $event
     * @param mixed $item
     * @return mixed
     */
    public function execute($event='', $item=array()){

        switch ($event){
            case 'GET_ARTICLE': $item = $this->eventGetArticle($item); break;
        }

        return $item;

    }

// ==================================================================== //

    private function eventGetArticle($item) {

        $item['content'] .= '<p style="color:'.$this->config['color'].'"><strong>'.$this->config['text'].' - '.$this->config['counter'].'</strong></p>';

        $this->config['counter'] += 1;

        $this->saveConfig();

        return $item;

    }

// ==================================================================== //

}

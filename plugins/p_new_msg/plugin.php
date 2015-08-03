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

class p_new_msg extends cmsPlugin {

    public function __construct(){

        $this->info['plugin']      = 'p_new_msg';
        $this->info['title']       = 'Анимация при новом сообщении';
        $this->info['description'] = 'Анимация при новом сообщении';
        $this->info['author']      = 'InstantCMS Team';
        $this->info['version']     = '1.0';

        $this->events[] = 'PRINT_PAGE_HEAD';

        parent::__construct();

    }

// ==================================================================== //

    public function execute($event='', $data=array()){

        switch ($event){
            case 'PRINT_PAGE_HEAD': return $this->animateNewMsg($data);
        }

        return $data;

    }

// ==================================================================== //

    private function animateNewMsg($page_head) {

        $inUser = cmsUser::getInstance();

        if(!$inUser->id || !$inUser->new_msg_count){ return $page_head; }

        ob_start(); ?>
        <script type="text/javascript">
            $(function(){
                function an (){
                    $('.my_messages a').fadeOut().addClass('has_new').fadeIn();
                    setTimeout(an, 3000);
                }
                an();
            });
        </script>
        <?php

        $page_head[] = ob_get_clean();

        return $page_head;

    }

}

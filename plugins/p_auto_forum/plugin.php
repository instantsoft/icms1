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

class p_auto_forum extends cmsPlugin {

    public function __construct(){

        // Информация о плагине
        $this->info['plugin']      = 'p_auto_forum';
        $this->info['title']       = 'Автофорум';
        $this->info['description'] = 'Создает тему на форуме для обсуждения статьи';
        $this->info['author']      = 'InstantCMS Team';
        $this->info['version']     = '1.10';

        // Настройки по-умолчанию
        // Ключи массива должны быть латиницей в верхнем регистре
        // если вы хотите, чтобы название опций было на нужном вам языке
        // создайте соответствующий lang файл, например /languages/ru/plugins/p_auto_forum.php
        // ключи языковых переменных должны совпадать с ключами массива конфигурации
        $this->config['AF_DELETE_THREAD']    = 1;
        $this->config['AF_LINK_TREAD']       = 1;
        $this->config['AF_ADDTREADFORUM_ID'] = 0;
		$this->config['AF_NOCREATETREAD']    = array();

        // События, которые будут отлавливаться плагином
        $this->events[] = 'DELETE_ARTICLE';
        $this->events[] = 'GET_ARTICLE';
        $this->events[] = 'ADD_ARTICLE_DONE';
        $this->events[] = 'UPDATE_ARTICLE';

        parent::__construct();

    }

// ========================================================================== //
    /**
     * Обработка событий
     * @param string $event
     * @param array $article
     * @return html
     */
    public function execute($event='', $article=array()){

        switch ($event){
            case 'DELETE_ARTICLE':   $this->deleteForum($article); break;
            case 'GET_ARTICLE':      $article = $this->getForumLink($article); break;
            case 'ADD_ARTICLE_DONE': $this->createForum($article); break;
            case 'UPDATE_ARTICLE':   $this->updateLastForumPost($article); break;
        }

        return $article;

    }

// ========================================================================== //

    private function updateLastForumPost($article){

		cmsCore::loadModel('forum');
		$model_forum = new cms_model_forum();

        // получаем полную статью
        $article = $this->inDB->get_fields('cms_content', "id = '{$article['id']}'", '*');

        $post = $this->inDB->get_fields('cms_forum_threads t, cms_forum_posts p',
                                                       "t.id = p.thread_id AND t.rel_to='content' AND t.rel_id= '{$article['id']}'", 'p.id', 'p.pubdate ASC');

        if ($post){
            $model_forum->updatePost(array('content'=>$this->getBbtexPost($article),
                                           'content_html'=>$this->getHtmlPost($article)), $post['id']);
        }

        return true;

    }

// ========================================================================== //

    private function deleteForum($article_id){

		if(!$this->config['AF_DELETE_THREAD']) { return; }

		cmsCore::loadModel('forum');
		$model_forum = new cms_model_forum();

        $thread = $this->inDB->get_fields('cms_forum_threads t
                                           INNER JOIN cms_forums f ON f.id = t.forum_id',
                                           "rel_to='content' AND rel_id= '{$article_id}'",
                                           't.*, f.NSLeft, f.NSRight');

        if ($thread){
            $model_forum->deleteThread($thread['id']);

            $model_forum->updateForumCache($thread['NSLeft'], $thread['NSRight'], true);
        }

        return true;

    }

// ========================================================================== //

    private function getForumLink($article){

        global $_LANG;

		if(!$this->config['AF_LINK_TREAD']) { return $article; }

		$forum_thread_id = $this->inDB->get_field('cms_forum_threads', "rel_to='content' AND rel_id='{$article['id']}'", 'id');

		if($forum_thread_id){
			$article['content'] .= '<div class="con_forum_link"><a href="/forum/thread'.$forum_thread_id.'.html">'.$_LANG['DISCUSS_ON_FORUM'].'</a></div>';
		}

        return $article;

    }

// ========================================================================== //

    private function createForum($article){

		$forum_id = (int)$this->config['AF_ADDTREADFORUM_ID'];

		if(!$forum_id) { return false; }

		if(!$this->checkCatForAdd($article['category_id'])) { return false; }

        // если для статьи есть уже тема, выходим
        $forum_thread_id = $this->inDB->get_field('cms_forum_threads', "rel_to='content' AND rel_id='{$article['id']}'", 'id');
        if($forum_thread_id){ return false; }

		cmsCore::loadModel('forum');
		$model_forum = new cms_model_forum();

		$post_html = $this->getHtmlPost($article);
		$post      = $this->getBbtexPost($article);

        // проверяем есть ли такой форум
        if(!$model_forum->getForum($forum_id)){
            return false;
        }

		$threadlastid = $model_forum->addThread(array(
				'forum_id' => $forum_id,
				'user_id' => $article['user_id'],
				'title' => $this->inDB->escape_string($article['title']),
				'description' => '',
				'is_hidden' => '0',
				'rel_to' => 'content',
				'hits' => 0,
				'pubdate' => date("Y-m-d H:i:s"),
				'rel_id' => $article['id']
		));

		$model_forum->addPost(array(
						'thread_id' => $threadlastid,
						'user_id' => $article['user_id'],
                        'content' => $post,
                        'content_html' => $post_html,
                        'pubdate' => date("Y-m-d H:i:s"),
                        'editdate' => date("Y-m-d H:i:s")
		));

		$forum = $this->inDB->get_fields('cms_forums', "id='{$forum_id}'", '*');

        $model_forum->updateThreadPostCount($threadlastid);

        cmsUser::checkAwards($article['user_id']);

        $model_forum->updateForumCache($forum['NSLeft'], $forum['NSRight'], true);

		cmsActions::log('add_thread', array(
					'object' => $article['title'],
					'user_id' => $article['user_id'],
					'object_url' => '/forum/thread'.$threadlastid.'.html',
					'object_id' => $threadlastid,
					'target' => $this->inDB->escape_string($forum['title']),
					'target_url' => '/forum/'.$forum_id,
					'target_id' => $forum_id,
					'description' => strip_tags($post_html)
		));

        return true;

    }

// ========================================================================== //

    private function checkCatForAdd($cat_id){

		if(!$cat_id) { return false; }

		if(!$this->config['AF_NOCREATETREAD']) { return true; }

		return !(in_array($cat_id, (array)$this->config['AF_NOCREATETREAD']));

    }

// ========================================================================== //

    private function getHtmlPost($article) {
        global $_LANG;
        return $this->inDB->escape_string(sprintf($_LANG['AF_LANG_TEXT_HTML'], '<a href="'.HOST.'/'.$article['seolink'].'.html">'.$article['title'].'</a>'));
    }
    private function getBbtexPost($article) {
        global $_LANG;
        return $this->inDB->escape_string(sprintf($_LANG['AF_LANG_TEXT_HTML'], '[url='.HOST.'/'.$article['seolink'].'.html]'.$article['title'].'[/url]'));
    }

// ========================================================================== //

}

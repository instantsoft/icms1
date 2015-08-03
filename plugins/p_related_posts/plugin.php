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

class p_related_posts extends cmsPlugin {

	public function __construct(){

		$this->info['plugin']      = 'p_related_posts';
        $this->info['title']       = 'Похожие записи в блогах';
        $this->info['description'] = 'Добавляет в конец каждого поста список похожих записей';
        $this->info['author']      = 'Pasha && InstantCMS Team';
        $this->info['version']     = '1.0';
        $this->info['published']   = '1';

		$this->events[] = 'GET_POST';

		$this->config['search_mode'] = 2;              // Режим поиска по титлам и контенту
		$this->config['tags_mode']   = 1;              // Режим поиска по тегам
		$this->config['add_mode']    = 1;              // Дополнение предыдущими-следующими
		$this->config['limit']       = 4;              // Количество похожих блогов
		$this->config['truncate']    = 200;            // Кол-во символов после которого текст будет обрезан
		$this->config['blank_photo'] = 'no_image.png'; // Путь к изображению-заглушке
		$this->config['cash_time']   = 24;             // Время жизни кеша в часах

        parent::__construct();

	}

    public function execute($event='', $item=array()){

        switch ($event){
            case 'GET_POST': return $this->getRelatedPosts($item);
        }

        return $item;

    }

    private function getRelatedPosts($item) {

        if(!($this->inCore->do == 'post' && $this->inCore->component == 'blogs')){ return $item; }

        $current_post_id = $item['id'];
        $current_blog_id = $item['blog_id'];
        $current_title   = $item['title'];
        $search_mode     = (int)$this->config['search_mode'];
        $tags_mode       = (int)$this->config['tags_mode'];
        $add_mode        = (int)$this->config['add_mode'];
        $need_found      = (int)$this->config['limit'];
        $truncate        = (int)$this->config['truncate'];
        $blank_photo     = strip_tags($this->config['blank_photo']);
        $cash_time       = (int)$this->config['cash_time'];
        $found_posts     = array();
        $exception       = '';

        // защита от дурака
        if($need_found < 1 || $truncate < 20) { return $item; }

        // Опционально может быть использовано кеширование
        if(cmsCore::isCached('rel_posts', $current_post_id, $cash_time, 'HOUR')){

            $cache = cmsCore::getCache('rel_posts', $current_post_id);

            if(isset($item['plugins_output_after'])){
                $item['plugins_output_after'] .= $cache;
            } else {
                $item['plugins_output_after'] = $cache;
            }

            return $item;

        }

        // Исключаем текущий, предыдущий и следующий пост
        $sql = "(
                SELECT id
                FROM cms_blog_posts
                WHERE id < $current_post_id
                AND blog_id = $current_blog_id
                ORDER BY id DESC LIMIT 1
                ) UNION (
                SELECT id
                FROM cms_blog_posts
                WHERE id > $current_post_id
                AND blog_id = $current_blog_id
                ORDER BY id ASC LIMIT 1
                )";

        if($res = $this->inDB->query($sql)){
            while($r = $this->inDB->fetch_row($res)){
                $exception .= $r[0].", ";
            }
        }

        // Первый набор исключений готов
        $exception .= $current_post_id;

        if(in_array($search_mode, array(1,2))){// Если разрешен поиск по титлу или контенту
            $target = $search_mode == 1 ? 'p.title' : 'p.content_html';

            // Сразу по обоим полям с существующими на сегодняшний день индексами сделать нельзя
            $sql = "SELECT p.id,
                           p.title,
                           p.seolink AS post_link,
                           p.content_html AS content,
                           b.seolink AS blog_link,
                           img.fileurl,
                           MATCH($target) AGAINST('$current_title') AS rel
                    FROM cms_blog_posts p
                    JOIN cms_blogs b ON b.id = p.blog_id
                    LEFT JOIN cms_upload_images img ON img.target_id = p.id AND img.target = 'blog_post' AND img.component = 'blogs'
                    WHERE MATCH($target) AGAINST('$current_title' IN BOOLEAN MODE)
                    AND p.allow_who = 'all'
                    AND b.allow_who = 'all'
                    AND b.owner = 'user'
                    AND p.id NOT IN($exception)
                    GROUP BY p.id
                    ORDER BY rel DESC
                    LIMIT $need_found";

            if($res = $this->inDB->query($sql)){
                while($r = $this->inDB->fetch_assoc($res)){
                    $found_posts[] = $r;
                }
            }
        }

        $found = count($found_posts);

        if($found < $need_found && $tags_mode){// Если недобор и разрешен поиск по тегам

            $left_find = $need_found - $found;

            $except = '';
            foreach($found_posts as $post){// Обновляю список найденных постов
                $except .= $post['id'].", ";
            }

            // Второй набор исключений готов
            $exception2 = $except . $exception;

            // Вытягиваю идентификаторы постов имеющих такие же теги как и у текущего поста.
            // Остаток оставляю в чистом виде в надежде что в последующей выборке все посты с этими идентификаторами попадут в выборку.
            // В ином случае недобор покроется за счет соседей
            $sql = "SELECT item_id,
                           COUNT(item_id) AS match_common_tags
                    FROM cms_tags
                    WHERE item_id NOT IN($exception2)
                    AND target = 'blogpost'
                    AND tag IN(SELECT tag
                               FROM cms_tags
                               WHERE target = 'blogpost'
                               AND item_id = $current_post_id)
                    GROUP BY item_id
                    ORDER BY match_common_tags DESC
                    LIMIT $left_find";

            $res = $this->inDB->query($sql);

            // Определять факт успеха операции в этом месте недостаточно, тут нужно гарантировать возврат числа
            if($this->inDB->num_rows($res)){

                $target_id = '';
                while($r = $this->inDB->fetch_row($res)){
                    $target_id .= ($r[0]).", ";// Заполняю найдеными идентификаторами переменную
                }
                $target_id = rtrim($target_id, ', ');

                // Вытягиваю посты
                $sql = "SELECT p.id,
                               p.title,
                               p.seolink AS post_link,
                               p.content_html AS content,
                               b.seolink AS blog_link,
                               img.fileurl
                        FROM cms_blog_posts p
                        JOIN cms_blogs b ON b.id = p.blog_id
                        LEFT JOIN cms_upload_images img ON img.target_id = p.id AND img.target = 'blog_post' AND img.component = 'blogs'
                        WHERE p.allow_who = 'all'
                        AND b.allow_who = 'all'
                        AND b.owner = 'user'
                        AND p.id IN($target_id)
                        GROUP BY p.id
                        LIMIT $left_find";

                if($res = $this->inDB->query($sql)){
                    while($r = $this->inDB->fetch_assoc($res)){
                        $found_posts[] = $r;
                    }
                }

            }
        }

        $found = count($found_posts);

        if($found < $need_found && $add_mode == 1){ // Если опять недобор и разрешено дополнять сеседями

            $left_find = $need_found - $found;

            $except = '';
            foreach($found_posts as $post){ // Обновляю список найденных постов
                $except .= $post['id'].", ";
            }

            // Третий набор исключений готов
            $exception3 = $except . $exception;

            // Смотрю как там оно распределено и сколько вообще осталось контента удовлетворяющего моим требованиям
            $sql = "(
                     SELECT COUNT(p.id)
                     FROM cms_blog_posts p
                     JOIN cms_blogs b ON b.id = p.blog_id
                     WHERE p.allow_who = 'all'
                     AND b.allow_who = 'all'
                     AND b.owner = 'user'
                     AND p.id < $current_post_id
                     AND p.id NOT IN($exception3)
                     ) UNION (
                     SELECT COUNT(p.id)
                     FROM cms_blog_posts p
                     JOIN cms_blogs b ON b.id = p.blog_id
                     WHERE p.allow_who = 'all'
                     AND b.allow_who = 'all'
                     AND b.owner = 'user'
                     AND p.id > $current_post_id
                     AND p.id NOT IN($exception3)
                    )";

            if($res = $this->inDB->query($sql)){
                $have_left  = $this->inDB->fetch_row($res);
                $have_left  = $have_left[0];
                $have_right = $this->inDB->fetch_row($res);
                $have_right = $have_right[0];
            } else {
                $have_left  = 0;
                $have_right = 0;
            }

            // Все дальнейшие действия актуальны только если в базе остался подходящий под все условия контент
            if($have_left + $have_right > 0){
                // Разделяю на левую и правую половину
                $need_from_both  = $left_find / 2;
                $need_from_left  = floor($need_from_both);
                $need_from_right = ceil($need_from_both);

                // Коррекция если текущий оказался крайним
                if($have_left < $need_from_left){
                    $need_from_right = $need_from_right + $need_from_left - $have_left;
                }
                if($have_right < $need_from_right){
                    $need_from_left = $need_from_left + $need_from_right - $have_right;
                }

                if($need_from_left == 0){// Если слева оказался ноль

                    $sql = "SELECT p.id,
                                   p.title,
                                   p.seolink AS post_link,
                                   p.content_html AS content,
                                   b.seolink AS blog_link, img.fileurl
                            FROM cms_blog_posts p
                            JOIN cms_blogs b ON b.id = p.blog_id
                            LEFT JOIN cms_upload_images img ON img.target_id = p.id AND img.target = 'blog_post' AND img.component = 'blogs'
                            WHERE p.allow_who = 'all'
                            AND b.allow_who = 'all'
                            AND b.owner = 'user'
                            AND p.id NOT IN($exception3)
                            AND p.id > $current_post_id
                            GROUP BY p.id
                            ORDER BY p.id ASC
                            LIMIT $left_find";
                } else {

                    $sql = "(
                             SELECT p.id,
                                    p.title,
                                    p.seolink AS post_link,
                                    p.content_html AS content,
                                    b.seolink AS blog_link, img.fileurl
                             FROM cms_blog_posts p
                             JOIN cms_blogs b ON b.id = p.blog_id
                             LEFT JOIN cms_upload_images img ON img.target_id = p.id AND img.target = 'blog_post' AND img.component = 'blogs'
                             WHERE p.allow_who = 'all'
                             AND b.allow_who = 'all'
                             AND b.owner = 'user'
                             AND p.id NOT IN($exception3)
                             AND p.id < $current_post_id
                             GROUP BY p.id
                             ORDER BY p.id DESC
                             LIMIT $need_from_left
                             ) UNION (
                             SELECT p.id,
                                    p.title,
                                    p.seolink AS post_link,
                                    p.content_html AS content,
                                    b.seolink AS blog_link, img.fileurl
                             FROM cms_blog_posts p
                             JOIN cms_blogs b ON b.id = p.blog_id
                             LEFT JOIN cms_upload_images img ON img.target_id = p.id AND img.target = 'blog_post' AND img.component = 'blogs'
                             WHERE p.allow_who = 'all'
                             AND b.allow_who = 'all'
                             AND b.owner = 'user'
                             AND p.id NOT IN($exception3)
                             AND p.id > $current_post_id
                             GROUP BY p.id
                             ORDER BY p.id ASC
                             LIMIT $need_from_right
                            )

                            LIMIT $left_find";
                }

                if($res = $this->inDB->query($sql)){
                    while($post = $this->inDB->fetch_assoc($res)){
                        $found_posts[] = $post;
                    }
                }
            }
        }

        // Если ничео нет, то возврат
        if(count($found_posts) == 0) { return $item; }

		cmsCore::loadModel('blogs');
		$model = new cms_model_blogs();

        foreach($found_posts as $key=>$post){
            if(!$found_posts[$key]['fileurl']){
                $found_posts[$key]['fileurl'] = '/images/photos/small/'.$blank_photo;
            }
            $found_posts[$key]['url']      = $model->getPostURL($post['blog_link'], $post['post_link']);
            $found_posts[$key]['blog_url'] = $model->getBlogURL($post['bloglink']);
            $found_posts[$key]['content']  = mb_strimwidth(preg_replace('/\[cut=.*\]/ui', '', strip_tags($post['content'])), 0, $truncate, '...');
        }

        ob_start();

        cmsPage::initTemplate('plugins', 'p_related_posts.tpl')->
        assign('posts', $found_posts)->
        display('p_related_posts.tpl');

        $html = ob_get_clean();

        // Добавляем в спец ячейку вывод
        if(isset($item['plugins_output_after'])){
            $item['plugins_output_after'] .= $html;
        } else {
            $item['plugins_output_after'] = $html;
        }

        // кешируем если нужно
        if($cash_time > 0) cmsCore::saveCache('rel_posts', $current_post_id, $html);

        return $item;

    }

}
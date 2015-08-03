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

class cms_model_blogs{

	public function __construct(){
        $this->inDB = cmsDatabase::getInstance();
		$this->config = cmsCore::getInstance()->loadComponentConfig('blogs');
    }

/* ========================================================================== */

    public static function getDefaultConfig() {

        return array (
			  'perpage' => 10,
			  'perpage_blog' => 15,
			  'update_date' => 0,
			  'update_seo_link' => 0,
			  'min_karma_private' => 0,
			  'min_karma_public' => 5,
			  'min_karma' => 1,
			  'list_min_rating' => 0,
			  'watermark' => 1,
			  'img_on' => 1,
              'meta_keys' => '',
              'meta_desc' => '',
              'seo_user_access' => 0,
			  'update_seo_link_blog' => 0
			);

    }

/* ========================================================================== */
   //
   // этот метод вызывается компонентом comments при создании нового комментария
   // метод обновляет количество комментариев для поста и для блога в целом
   //
   public function updateCommentsCount($target, $target_id) {

        if ($target != 'blog') { return false; }

		cmsCore::loadClass('blog');

        return cmsBlogs::updateCommentsCount($target, $target_id);

    }

/* ========================================================================== */
   //
   // этот метод вызывается компонентом comments при создании нового комментария
   // метод должен вернуть массив содержащий ссылку и заголовок поста, к которому
   // добавляется комментарий
   //
   public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'blog': $sql = "SELECT p.title as title,
                                        p.seolink as seolink,
                                        b.seolink as bloglink
                                 FROM cms_blog_posts p
								 LEFT JOIN cms_blogs b ON b.id = p.blog_id
                                 WHERE p.id = '{$target_id}' LIMIT 1";

                         $res = $this->inDB->query($sql);
                         if (!$this->inDB->num_rows($res)){ return false; }
                         $post = $this->inDB->fetch_assoc($res);
                         $result['link']  = $this->getPostURL($post['bloglink'], $post['seolink']);
                         $result['title'] = $post['title'];
                         break;

        }

        return ($result ? $result : false);

    }

/* ========================================================================== */
   //
   // этот метод вызывается компонентом comments при создании нового комментария
   // метод должен вернуть 0 или 1
   //
   public function getVisibility($target, $target_id) {

        $is_hidden = 0;

        switch($target){

            case 'blog':
						// получаем массив поста
						$post = $this->inDB->get_fields('cms_blog_posts', "id='$target_id'", 'blog_id, allow_who, published');
						if($post['allow_who'] != 'all' || !$post['published']) { $is_hidden = 1; }
						// получаем массив блога
						$blog = $this->inDB->get_fields('cms_blogs', "id='{$post['blog_id']}'", 'allow_who');
						if($blog['allow_who'] != 'all') { $is_hidden = 1; }
                        break;

        }

        return $is_hidden;

    }

/* ========================================================================== */
    //
    // этот метод является хуком и вызывается при изменении рейтинга объекта blogpost
    // см. таблицу cms_rating_targets
    //
    public function updateRatingHook($target, $item_id, $points) {

        if ($target != 'blogpost' || !$item_id || abs($points)!=1) { return false; }

        $sql = "UPDATE cms_blogs b, cms_blog_posts p
                SET b.rating = b.rating + ({$points}), p.rating = p.rating + ({$points})
                WHERE p.blog_id = b.id AND p.id = {$item_id}";

        $this->inDB->query($sql);

        return true;

    }

/* ========================================================================== */

    public static function getPostURL($bloglink, $seolink){

        return '/blogs/'.$bloglink.'/'.$seolink.'.html';

    }

/* ========================================================================== */

    public static function getBlogURL($bloglink, $page=1, $cat_id=0){

        $cat_section  = ($cat_id >0 ? '/cat-'.$cat_id   : '');
        $page_section = ($page   >1 ? '/page-'.$page    : '');

        return '/blogs/'.$bloglink.$cat_section.$page_section;

    }

/* ========================================================================== */

}
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

class cms_model_catalog{

	public function __construct(){
        $this->inDB = cmsDatabase::getInstance();
        cmsCore::loadLib('tags');
        $this->config = cmsCore::getInstance()->loadComponentConfig('catalog');
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    public static function getDefaultConfig() {

        return array(
                'email' => '',
                'delivery' => '',
                'meta_keys' => '',
                'meta_desc' => '',
                'notice' => 1,
                'premod' => 1,
                'premod_msg' => 1,
                'is_comments' => 1,
                'is_rss' => 1,
                'small_size' => 100,
                'medium_size' => 250,
                'watermark' => 1);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'catalog': $item = $this->inDB->get_fields('cms_uc_items', "id={$target_id}", 'title');
                            if (!$item) { return false; }
                            $result['link']  = '/catalog/item'.$target_id.'.html';
                            $result['title'] = $item['title'];
                            break;

        }

        return ($result ? $result : false);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function deleteItem($id){

        $imageurl = $this->getItemImageUrl($id);

        @chmod(PATH."/images/catalog/$imageurl", 0777);
        @chmod(PATH."/images/catalog/small/$imageurl", 0777);
        @chmod(PATH."/images/catalog/medium/$imageurl", 0777);

        @unlink(PATH.'/images/catalog/'.$imageurl);
        @unlink(PATH.'/images/catalog/small/'.$imageurl);
        @unlink(PATH.'/images/catalog/medium/'.$imageurl);

        $this->inDB->query("DELETE FROM cms_uc_items WHERE id= '{$id}'");
        $this->inDB->query("DELETE FROM cms_tags WHERE target='catalog' AND item_id = '{$id}'");
        $this->inDB->query("DELETE FROM cms_uc_ratings WHERE item_id = '{$id}'");

		cmsActions::removeObjectLog('add_catalog', $id);

        cmsCore::deleteComments('catalog', $id);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function updateItem($id, $item){

        $item = cmsCore::callEvent('UPDATE_CATALOG_ITEM', $item);

        $this->inDB->update('cms_uc_items', $item, $id);

        cmsInsertTags($item['tags'], 'catalog', $id);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function renewItem($id){
        cmsCore::callEvent('RENEW_CATALOG_ITEM', $id);
        $sql = "UPDATE cms_uc_items SET pubdate = NOW() WHERE id = $id";
		$this->inDB->query($sql);
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function getItemImageUrl($id){
        $imageurl = $this->inDB->get_field('cms_uc_items', "id={$id}", 'imageurl');
        $imageurl = cmsCore::callEvent('GET_CATALOG_ITEM_IMAGE', $imageurl);
        return $imageurl;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function addItem($item){

        $item = cmsCore::callEvent('ADD_CATALOG_ITEM', $item);

        $item_id = $this->inDB->insert('cms_uc_items', $item);

		cmsInsertTags($item['tags'], 'catalog', $item_id);

        return $item_id;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function copyItem($id, $copies){

        cmsCore::callEvent('COPY_CATALOG_ITEM', $id);

		$item = $this->inDB->get_fields('cms_uc_items', "id = '$id'", 'category_id, title, pubdate, published, imageurl, fieldsdata, is_comments, tags, rating, meta_desc, meta_keys, price, canmany, user_id, on_moderate');
		if(!$item) { return false; }

		for($c=1; $c<=$copies; $c++){

			$set = '';
			foreach($item as $field=>$value){
				$set .= "{$field} = '{$this->inDB->escape_string($value)}',";
			}
			$set = rtrim($set, ',');

			$this->inDB->query("INSERT INTO cms_uc_items SET {$set}");

			$id = $this->inDB->get_last_id('cms_uc_items');

			cmsInsertTags($item['tags'], 'catalog', $id);

		}

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function deleteDiscount($id){
        cmsCore::callEvent('DELETE_CATALOG_DISCOUNT', $id);
        $sql = "DELETE FROM cms_uc_discount WHERE id = $id LIMIT 1";
        $this->inDB->query($sql) ;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function updateDiscount($id, $item){
        $item = cmsCore::callEvent('UPDATE_CATALOG_DISCOUNT', $item);
        return $this->inDB->update('cms_uc_discount', $item, $id);
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function addDiscount($item){
        $item = cmsCore::callEvent('ADD_CATALOG_DISCOUNT', $item);
		return $this->inDB->insert('cms_uc_discount', $item);
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function deleteCategory($id){
        cmsCore::callEvent('DELETE_CATALOG_CAT', $id);
        $sql = "SELECT id FROM cms_uc_items WHERE category_id = '$id'";
        $result = $this->inDB->query($sql) ;
        if ($this->inDB->num_rows($result)){
            while($item = $this->inDB->fetch_assoc($result)){
                $this->deleteItem($item['id']);
            }
        }
        $this->inDB->deleteNS('cms_uc_cats', $id);
        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

	public function updateCategory($id, $cat){

		$old = $this->inDB->get_fields('cms_uc_cats', "id='$id'", 'parent_id, NSLevel');
		if(!$old) { return false; }

        $cat = cmsCore::callEvent('UPDATE_CATALOG_CAT', $cat);

		if($cat['parent_id'] != $old['parent_id'] && $cat['parent_id'] != $id){
        	cmsCore::nestedSetsInit('cms_uc_cats')->MoveNode($id, $cat['parent_id']);
		}
        if($cat['parent_id'] == $id){
            $cat['parent_id'] = $old['parent_id'];
        }

        return $this->inDB->update('cms_uc_cats', $cat, $id);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function copyCategory($id, $copies){

		$inCore = cmsCore::getInstance();
		$ns = $inCore->nestedSetsInit('cms_uc_cats');

		// Данные категории для копирования
		$item = $this->inDB->get_fields('cms_uc_cats', "id = '$id'", 'parent_id, title, description, published, fieldsstruct, view_type, fields_show, showmore, perpage, showtags, showsort,	is_ratings,	orderby, orderto, showabc, shownew, newint,	filters, is_shop, is_public, can_edit, cost');
		if(!$item) { return false; }

		// Получаем родительскую категорию
		$rootid = $this->inDB->get_field('cms_uc_cats', "id = '{$item['parent_id']}'", 'id');
		if(!$rootid) { return false; }

        cmsCore::callEvent('COPY_CATALOG_CAT', $id);

		for($c=1; $c<=$copies; $c++){

			$cat_id = $ns->AddNode($rootid);
			$set = '';
			foreach($item as $field=>$value){
				$set .= "{$field} = '{$this->inDB->escape_string($value)}',";
			}
			$set = rtrim($set, ',');
			$this->inDB->query("UPDATE cms_uc_cats SET {$set} WHERE id = '{$cat_id}' LIMIT 1");

        }
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCategoryPath($left_key, $right_key) {

        $path = array();

        $sql = "SELECT id, title, NSLevel
                FROM cms_uc_cats
                WHERE NSLeft <= $left_key AND NSRight >= $right_key AND parent_id > 0
                ORDER BY NSLeft";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) { return false; }

        while($cat = $this->inDB->fetch_assoc($result)){
            $path[] = $cat;
        }

        return $path;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getSubCats($parent_id, $left_key, $right_key) {

        $subcats=array();

        $sql = "SELECT cat.*
                FROM cms_uc_cats cat
                WHERE cat.parent_id = '$parent_id' AND cat.published = 1
                ORDER BY cat.title";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) { return false; }

        while($subcat = $this->inDB->fetch_assoc($result)){

            $count_sql = "SELECT con.id
                          FROM cms_uc_items con, cms_uc_cats cat
                          WHERE con.category_id = cat.id AND (cat.NSLeft >= {$subcat['NSLeft']} AND cat.NSRight <= {$subcat['NSRight']}) AND con.published = 1";

            $count_result = $this->inDB->query($count_sql);

            $subcat['content_count'] = $this->inDB->num_rows($count_result);

            $subcats[] = $subcat;

        }

        $subcats = cmsCore::callEvent('GET_CATALOG_SUBCATS', $subcats);

        return $subcats;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function setCategoryAccess($id, $showfor_list){

        $this->clearCategoryAccess($id);

        if (!sizeof($showfor_list)){ return true; }

        foreach ($showfor_list as $key=>$value){
            $sql = "INSERT INTO cms_uc_cats_access (cat_id, group_id)
                    VALUES ($id, $value)";
            $this->inDB->query($sql);
        }

        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function clearCategoryAccess($id){

        $sql = "DELETE FROM cms_uc_cats_access WHERE cat_id = $id";

        $this->inDB->query($sql);

        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function checkCategoryAccess($cat_id, $cat_public, $group_id) {
        return ($cat_public && $this->inDB->rows_count('cms_uc_cats_access', "cat_id={$cat_id} AND group_id={$group_id}", 1));
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public static function getUCSearchLink($cat_id, $text){

        $text = strip_tags(html_entity_decode(trim($text)));
        $text = preg_replace('/\s+/u', ' ', $text);

        $words = explode(',', $text);
        $html  = '';

        foreach($words as $key=>$value){

            $value = trim($value);

            $html .= '<a href="/catalog/'.$cat_id.'/find/'.urlencode($value).'">'.$value.'</a>, ';

        }

        return rtrim($html, ', ');

    }

    public static function buildRating($rating){
        global $_LANG;
        $rating = round($rating, 2);
        $html   = '<span title="'.$_LANG['RATING'].': '.$rating.'">';
        for($r = 0; $r < 5; $r++){
            if (round($rating) > $r){
                $html .= '<img src="/images/ratings/starfull.gif" border="0" />';
            } else {
                $html .= '<img src="/images/ratings/starhalf.gif" border="0" />';
            }
        }
        $html .= '</span>';
        return $html;
    }

	public function uploadPhoto($old_file = '') {

		cmsCore::loadClass('upload_photo');
		$inUploadPhoto = cmsUploadPhoto::getInstance();
		$inUploadPhoto->upload_dir    = PATH.'/images/catalog/';
		$inUploadPhoto->small_size_w  = $this->config['small_size'];
		$inUploadPhoto->medium_size_w = $this->config['medium_size'];
		$inUploadPhoto->is_watermark  = $this->config['watermark'];
		$inUploadPhoto->is_saveorig   = 1;
		$inUploadPhoto->input_name    = 'imgfile';

		return $inUploadPhoto->uploadPhoto($old_file);

	}

}
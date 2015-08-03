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

class cms_model_arhive{

	public function __construct(){
		$this->config = cmsCore::getInstance()->loadComponentConfig('arhive');
        $this->inDB   = cmsDatabase::getInstance();
        cmsCore::loadLanguage('components/arhive');
        $this->year  = cmsCore::request('y', 'int', 'all');
        $this->month = sprintf("%02d", cmsCore::request('m', 'int', 'all'));
        $this->day   = sprintf("%02d", cmsCore::request('d', 'int', 'all'));
        $this->setSqlParams();
    }

/* ==================================================================================================== */
    /**
     * Настройки по умолчанию для компонента
     * @return array
     */
    public static function getDefaultConfig() {

        $cfg = array (
				  'source' => 'arhive'
				);

        return $cfg;

    }
/* ==================================================================================================== */
    private function setSqlParams() {

        if ($this->config['source'] != 'both'){
            if ($this->config['source']=='arhive'){
                $this->inDB->where("con.is_arhive = 1");
            } else {
                $this->inDB->where("con.is_arhive = 0");
            }
        }

        $this->inDB->where("con.published = 1 AND con.pubdate <= '".date("Y-m-d H:i:s")."'");
        $this->inDB->groupBy("DATE_FORMAT(con.pubdate, '%M, %Y')");
        $this->inDB->orderBy('con.pubdate', 'DESC');
        $this->inDB->select = "DATE_FORMAT(con.pubdate, '%Y') as year, DATE_FORMAT(con.pubdate, '%m') as month, COUNT( con.id ) as num";

    }
/* ==================================================================================================== */
    public function whereYearIs() {
        if(is_numeric($this->year)){
            $this->inDB->where("DATE_FORMAT(con.pubdate, '%Y') LIKE '{$this->year}'");
        }
    }
    public function whereMonthIs() {
        if(is_numeric($this->year) && is_numeric($this->month)){
            $date_str = $this->year.'-'.$this->month;
            $this->inDB->where("DATE_FORMAT(con.pubdate, '%Y-%m') LIKE '{$date_str}'");
        }
    }
    public function whereDayIs() {
        if(is_numeric($this->day) && is_numeric($this->year) && is_numeric($this->month)){
            $date_str = $this->year.'-'.$this->month.'-'.$this->day;
            $this->inDB->where("DATE_FORMAT(con.pubdate, '%Y-%m-%d') LIKE '{$date_str}'");
        }
    }
    public function whereThisAndNestedCats($cat_id) {
        if(!@$cat_id){ return false; }
        $rootcat = $this->inDB->get_fields('cms_category', "id='{$cat_id}'", 'NSLeft, NSRight');
        if(!$rootcat) { return false; }
        $this->inDB->where("cat.NSLeft >= '{$rootcat['NSLeft']}' AND cat.NSRight <= '{$rootcat['NSRight']}' AND cat.parent_id > 0");
        $this->inDB->addJoin('INNER JOIN cms_category cat ON cat.id = con.category_id');
    }
    public function setArtticleSql() {

        $this->inDB->select   = "con.*, DATE_FORMAT(con.pubdate, '%Y') as year, DATE_FORMAT(con.pubdate, '%m') as month, DATE_FORMAT(con.pubdate, '%d') as day, cat.title cat_title, cat.showdesc, cat.seolink as cat_seolink";
        $this->inDB->group_by = '';
        $this->inDB->addJoin('INNER JOIN cms_category cat ON cat.id = con.category_id');

    }
/* ==================================================================================================== */

	public function getArhiveContent(){

        $sql = "SELECT {$this->inDB->select}
                FROM cms_content con
				{$this->inDB->join}
                WHERE 1=1 {$this->inDB->where}
                {$this->inDB->group_by}
                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

        $result = $this->inDB->query($sql);

        $this->inDB->resetConditions();

        if (!$this->inDB->num_rows($result)) { return array(); }

        cmsCore::loadModel('content');
        $content_model = new cms_model_content();

        while ($item = $this->inDB->fetch_assoc($result)){

            if(!isset($item['seolink'])){
                $item['fmonth'] = cmsCore::intMonthToStr($item['month']);
            } else {
                $item['url']          = $content_model->getArticleURL(0, $item['seolink']);
                $item['category_url'] = $content_model->getCategoryURL(0, $item['cat_seolink']);
				$item['fpubdate']     = cmsCore::dateFormat($item['pubdate']);
            }
            $item['image'] = (file_exists(PATH.'/images/photos/small/article'.$item['id'].'.jpg') ?
                                'article'.$item['id'].'.jpg' : '');

            $content[] = $item;

        }

        return cmsCore::callEvent('GET_ARHIVE', translations::process(cmsConfig::getConfig('lang'), 'content_content', $content));

    }

/* ==================================================================================================== */

}
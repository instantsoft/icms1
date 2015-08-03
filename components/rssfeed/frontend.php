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

function rssfeed($component=null, $item_id=null){

    $inCore = cmsCore::getInstance();
    $inConf = cmsConfig::getInstance();

    $model = new cms_model_rssfeed();

    global $_LANG;

    $component = isset($component) ? $component : cmsCore::request('target', 'str', 'rss');
    $item_id   = isset($item_id) ? $item_id : cmsCore::request('item_id', 'str', 'all');

    if(!$inCore->isComponentInstalled($component)) { cmsCore::error404(); }

	if (!preg_match('/^([a-z0-9_\-]+)$/ui', $item_id)) { $item_id = 0; }

	if ($item_id == 'all') { $item_id = 0; }

////////////////////////////////////////////////////////////////////////////////
if ($inCore->do == 'view'){

	if (!file_exists(PATH.'/components/'.$component.'/prss.php')){

        header('HTTP/1.0 404 Not Found');
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');

        cmsCore::halt($_LANG['NOT_RSS_GENERATOR']);

    }

	cmsCore::loadLanguage('components/'.$component);
	cmsCore::includeFile('components/'.$component.'/prss.php');

	$rssdata = call_user_func_array('rss_'.$component, array($item_id, $model->config));
	if(!$rssdata['channel']){
        header('HTTP/1.1 203 Non-Authoritative Information');
        cmsCore::halt($_LANG['NOT_POST_IN_RSS']);
    }

	$channel = $rssdata['channel'];
	$items   = $rssdata['items'];

	if ($model->config['addsite']) { $channel['title'] .= ' :: ' . $inConf->sitename; }
	$channel['title'] = trim(htmlspecialchars(strip_tags($channel['title'])));

	header('Content-Type: application/rss+xml; charset=utf-8');

	$rss  = '<?xml version="1.0" encoding="utf-8" ?>' ."\n";
	$rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' ."\n";
		$rss .= '<channel>' ."\n";
			// Канал
			$rss .= '<title>'.$channel['title'].'</title>' ."\n";
			$rss .= '<link>'.$channel['link'].'</link>' ."\n";
			$rss .= '<description><![CDATA['.trim(htmlspecialchars(strip_tags($channel['description']))).']]></description>' ."\n";

			if ($model->config['icon_on']){
				$rss .= '<image>'."\n";
					$rss .= '<title>'.$channel['title'].'</title>'."\n";
					$rss .= '<url>'.$model->config['icon_url'].'</url>'."\n";
					$rss .= '<link>'.$channel['link'].'</link>'."\n";
				$rss .= '</image>'."\n";
			}

			// Содержимое канала
			if (is_array($items) && $items){
				foreach ($items as $item){
					$rss .= '<item>' ."\n";
						$rss .= '<title>'.trim(htmlspecialchars(strip_tags($item['title']))).'</title>' ."\n";
						$rss .= '<pubDate>'.date('r', strtotime($item['pubdate'])+($inConf->timediff*3600)).'</pubDate>' ."\n";
						$rss .= '<guid>'.$item['link'].'</guid>' ."\n";
						$rss .= '<link>'.$item['link'].'</link>' ."\n";
						if (!empty($item['description'])){
							$rss .= '<description><![CDATA['.$item['description'].']]></description>' ."\n";
						}
						$rss .= '<category>'.$item['category'].'</category>' ."\n";
						$rss .= '<comments>'.$item['comments'].'</comments>' ."\n";
						if (!empty($item['image'])){
							  $rss .= '<enclosure url="'.$item['image'].'" length="'.$item['size'].'" type="image/jpeg" />' ."\n";
						}
                        if(!empty($item['custom_enclosure'])){
                            $rss .= '<enclosure url="'.$item['custom_enclosure']['url'].'" length="'.$item['custom_enclosure']['length'].'" type="'.$item['custom_enclosure']['type'].'" />' ."\n";
                        }
					$rss .= '</item>' ."\n";
				}
			}
		$rss .= '</channel>' ."\n";
	$rss .= '</rss>';

	cmsCore::halt($rss);

}
////////////////////////////////////////////////////////////////////////////////

}
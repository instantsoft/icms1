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
    function smarty_function_city_input($params, &$smarty) {

        global $_LANG;

        if (!cmsCore::getInstance()->isComponentInstalled('forms')) {
            return '<input type="text" value="'.htmlspecialchars($params['value']).'" name="'.htmlspecialchars($params['name']).'" class="text-input" style="width:300px"/>';
        }

        array_walk($params, create_function('&$value', 'return htmlspecialchars($value);'));

        if (!isset($params['placeholder'])) {
            $params['placeholder'] = $_LANG['SELECT_CITY'];
        }
        if (!isset($params['width'])) {
            $params['width'] = '100%';
        }
        if (!isset($params['input_width'])) {
            $params['input_width'] = '150px';
        }
        if (!isset($params['city_id'])) {
            $params['city_id'] = 0;
        }
        if (!isset($params['region_id'])) {
            $params['region_id'] = 0;
        }
        if (!isset($params['country_id'])) {
            $params['country_id'] = 0;
        }
        if (!isset($params['value'])) {
            $params['value'] = '';
        }

        cmsPage::getInstance()->addHeadJS('components/geo/js/geo.js');

        $id = uniqid();

        $display = $params['value'] ? '' : 'style="display:none"';

        return '<div class="text-input city_block" id="'.$id.'" style="width:'.$params['width'].'">
                <input type="hidden" value="'.htmlspecialchars($params['value']).'" name="'.$params['name'].'" class="city_name" />
                <input type="hidden" value="'.$params['city_id'].'" name="city_id" class="city_id" />
                <input type="hidden" value="'.$params['region_id'].'" name="region_id" class="region_id" />
                <input type="hidden" value="'.$params['country_id'].'" name="country_id" class="country_id" />
                <input readonly="readonly" placeholder="'.$params['placeholder'].'" type="text" value="'.$params['value'].'" class="city_view" onclick="geo.viewForm(\''.$id.'\');return false;" style="width:'.$params['input_width'].'" />
                <a class="city_link city_clear_link" href="#" onclick="geo.clear(\'' . $id . '\');return false;" '.$display.'>'.$_LANG['DELETE'].'</a>
                <a class="city_link" href="#" onclick="geo.viewForm(\''.$id.'\');return false;">'.$_LANG['SELECT'].'</a>
            </div>' . "\n";

    }
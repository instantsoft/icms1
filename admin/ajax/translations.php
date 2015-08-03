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

define('PATH', $_SERVER['DOCUMENT_ROOT']);
define("VALID_CMS_ADMIN", 1);
include(PATH.'/core/ajax/ajax_core.php');

cmsCore::loadLanguage('admin/lang');

if (!$inUser->is_admin) { cmsCore::halt($_LANG['ACCESS_DENIED']); }

$lang      = cmsCore::request('lang', 'str', '');
$target_id = cmsCore::request('target_id', 'int', 0);
$target    = preg_replace('/[^a-z0-9_\-]/i', '', cmsCore::request('target', 'str', ''));
$field     = preg_replace('/[^a-z0-9_\-]/i', '', cmsCore::request('field', 'str', ''));

$langs = cmsCore::getDirsList('/languages');

if(!in_array($lang, $langs) || !$target_id || !$target || !$field){
    cmsCore::halt(1);
}

$fields = translations::getFields($target);

if(!isset($fields[$field])){
    cmsCore::halt(2);
}

$type = $fields[$field];

// получаем все данные по $target_id и $target
$translation = translations::getTranslation($lang, $target, $target_id);

// получаем текущее значение поля
if($translation && isset($translation['data'][$field])){
    $value = $translation['data'][$field];
} else {
    $value = '';
}

if(cmsCore::inRequest('save')){

    if(!cmsUser::checkCsrfToken()) { cmsCore::halt(); }

    $field_data = cmsCore::request('field_data', $type, '');

    // если есть запись, обновляем
    if($translation){

        if($field_data){
            $translation['data'][$field] = $field_data;
        }
        if(!$field_data && isset($translation['data'][$field])){
            unset($translation['data'][$field]);
        }

        $inDB->update('cms_translations', array(
            'data'=>$inDB->escape_string(cmsCore::arrayToYaml($translation['data']))
        ), $translation['id']);

    }
    // нет - добавляем
    else {

        $inDB->insert('cms_translations', array(
            'data'=>$inDB->escape_string(cmsCore::arrayToYaml(array(
                $field=>$field_data
            ))),
            'lang'=>$lang,
            'fieldsset_id'=>translations::getFieldsetId($target),
            'target_id'=>$target_id,
        ));

    }

    cmsCore::halt();

} else {

    cmsPage::includeTemplateFile('admin/translations.php', array(
        'type'=>$type,
        'value'=>$value,
        'action'=>$_SERVER['REQUEST_URI']
    ));

}


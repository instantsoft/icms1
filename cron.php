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

// некоторые задачи требуют безлимитного времени выполнения, в cli это по умолчанию
// задача для CRON выглядит примерно так: php -f /path_to_site/cron.php site.ru
// где site.ru - имя вашего домена
// Если планируете запускать задачи CRON через curl или иные http запросы, закомментируйте строку ниже
if(PHP_SAPI != 'cli') die('Access denied');

Error_Reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

define('PATH', dirname(__FILE__));
define("VALID_CMS", 1);

include(PATH.'/core/cms.php');

cmsCore::getInstance();
cmsCore::loadClass('cron');
cmsCore::loadClass('actions');

$jobs = cmsCron::getJobs();

// если есть задачи
if(is_array($jobs)){

    // выполняем их
    foreach($jobs as $job){

        // проверяем интервал запуска
        if (!$job['job_interval'] || ($job['hours_ago'] > $job['job_interval']) || $job['is_new']) {
            // запускаем задачу
            cmsCron::executeJob($job);
        }

    }

}

cmsCore::halt();
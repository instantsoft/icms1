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

class cmsCron {

    private static $instance;

// ============================================================================ //
// ============================================================================ //

    private function __construct() {}

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Регистрирует новую задачу СRON
     * @param str $job_name
     * @param array $job (interval, component, model_method, custom_file, comment)
     * @return bool
     */
    public static function registerJob($job_name, $job){

        $inDB = cmsDatabase::getInstance();

        if (!isset($job['enabled'])) { $job['enabled'] = 1; }
        if (!isset($job['class_name'])) { $job['class_name'] = ''; }
        if (!isset($job['class_method'])) { $job['class_method'] = ''; }

        $sql = "INSERT INTO cms_cron_jobs (job_name, job_interval, job_run_date,
                                           component, model_method, custom_file,
                                           is_enabled, is_new, comment,
                                           class_name, class_method)
                VALUES ('{$job_name}', '{$job['interval']}', CURRENT_TIMESTAMP,
                        '{$job['component']}', '{$job['model_method']}', '{$job['custom_file']}',
                        '{$job['enabled']}', '1', '{$job['comment']}',
                        '{$job['class_name']}', '{$job['class_method']}')";

        $inDB->query($sql);

        return true;

    }

    /**
     * Обновляет описание задачи СRON
     * @param int $job_id
     * @param array $job (interval, component, model_method, custom_file, comment, enabled)
     * @return bool
     */
    public static function updateJob($job_id, $job){

        return cmsDatabase::getInstance()->update('cms_cron_jobs', $job, $job_id);

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Находит описание задачи CRON по названию
     * @param str $job_name
     * @param bool $only_enabled
     * @return array | false
     */
    public static function getJob($job_name, $only_enabled=true){

        $enabled = $only_enabled ? 'AND is_enabled=1' : '';

        return cmsDatabase::getInstance()->get_fields('cms_cron_jobs', "job_name='{$job_name}' {$enabled}", '*');

    }

    /**
     * Находит описание задачи CRON по id
     * @param int $job_id
     * @return array | false
     */
    public static function getJobById($job_id){

        return cmsDatabase::getInstance()->get_fields('cms_cron_jobs', "id='{$job_id}'", '*');

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Возвращает список задач CRON
     * @param bool $only_enabled Только активные
     * @param bool $only_custom Только задачи выполнения скрипта
     * @return array
     */
    public static function getJobs($only_enabled=true, $only_custom=false){

        $inDB = cmsDatabase::getInstance();

        $enabled = $only_enabled ? 'AND is_enabled=1' : '';

        $custom = $only_custom ? "AND component='' AND model_method='' AND class_name='' AND class_method=''" : '';

        $sql = "SELECT id,
                       job_name as name,
                       job_interval,
                       job_run_date as run_date,
                       component,
                       model_method,
                       custom_file,
                       is_enabled,
                       is_new,
                       comment,
                       class_name,
                       class_method

                FROM cms_cron_jobs

                WHERE 1=1 {$enabled} {$custom}

                ORDER BY job_run_date ASC

                ";

        $result = $inDB->query($sql);

        if (!$inDB->num_rows($result)){ return false; }

        $jobs = array();

        while($job = $inDB->fetch_assoc($result)){

            $job['hours_ago'] = round((time() - strtotime($job['run_date']))/3600, 2);

            $jobs[] = $job;

        }

        return $jobs;

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Удаляет задачу CRON
     * @param string $job_name
     * @return bool
     */
    public static function removeJob($job_name){

        return cmsDatabase::getInstance()->delete('cms_cron_jobs', "job_name = '{$job_name}'", 1);

    }

    /**
     * Удаляет задачу CRON по id
     * @param int $job_id
     * @return bool
     */
    public static function removeJobById($job_id){

        return cmsDatabase::getInstance()->delete('cms_cron_jobs', "id = '{$job_id}'", 1);

    }

    /**
     * Изменяет активность задачи
     * @param int $job_id ID задачи
     * @param bool $is_enabled Активность
     * @return bool
     */
    public static function jobEnabled($job_id, $is_enabled){

        $is_enabled = (int)$is_enabled;

        $inDB = cmsDatabase::getInstance();

        $sql = "UPDATE cms_cron_jobs SET is_enabled = '{$is_enabled}' WHERE id = '{$job_id}'";

        $inDB->query($sql);

        return true;

    }


// ============================================================================ //
// ============================================================================ //

    /**
     * Отмечает задачу как успешно выполненную
     * @param int $job_id ID задачи
     * @return bool
     */
    public static function jobSuccess($job_id){

        $inDB = cmsDatabase::getInstance();

        $sql = "UPDATE cms_cron_jobs SET job_run_date = CURRENT_TIMESTAMP, is_new = 0 WHERE id = '{$job_id}'";

        $inDB->query($sql);

        return true;

    }

// ============================================================================ //
// ============================================================================ //

    /**
     * Выполняет задачу с указанным именем
     * @param str $job_name
     * @return bool
     */
    public static function executeJobByName($job_name){

        $job = self::getJob($job_name);
        return self::executeJob($job);

    }

    /**
     * Выполняет задачу с указанным ID
     * @param int $job_id
     * @return bool
     */
    public static function executeJobById($job_id){

        $job = self::getJobById($job_id);
        return self::executeJob($job);

    }

    /**
     * Выполняет переданную задачу
     * @param array $job
     * @return bool
     */
    public static function executeJob($job){

        $job_result = true;

        /* ================================================ */
        /* ==============  внешний php-файл  ============== */
        /* ================================================ */
        if ($job['custom_file']){

            cmsCore::includeFile(ltrim($job['custom_file'], '/'));

        }

        /* ================================================ */
        /* ================  метод модели ================= */
        /* ================================================ */
        if ($job['component'] && $job['model_method']){

            cmsCore::loadModel($job['component']);

            $classname  = "cms_model_{$job['component']}";

            if (class_exists($classname)) {

                $model = new $classname();

                if (method_exists($model, $job['model_method'])){

                    $job_result = call_user_func(array($model, $job['model_method']));

                }

            }

        }

        /* ================================================ */
        /* =================  метод класса ================ */
        /* ================================================ */
        if ($job['class_name'] && $job['class_method']){

            $classfile = '';

            if (!mb_strstr($job['class_name'], '|')){
                $classname = $job['class_name'];
            } else {
                $job['class_name'] = explode('|', $job['class_name']);
                $classfile = $job['class_name'][0];
                $classname = $job['class_name'][1];
            }

            if ($classfile){ cmsCore::loadClass($classfile); }

            if (class_exists($classname)) {

                if (method_exists($classname, $job['class_method'])){

                    $job_result = $job_result && call_user_func(array($classname, $job['class_method']));

                }

            }

        }

        if ($job_result){ self::jobSuccess($job['id']); }

		return $job_result;

    }


// ============================================================================ //
// ============================================================================ //

}
?>

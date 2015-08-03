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

class cmsPlugin {

    protected $inDB;
    protected $inCore;
    protected $inPage;

    public $info;
    public $events;
    public $config;

// ================================================================== //

    public function __construct(){

        cmsCore::loadClass('page');
        $this->inCore = cmsCore::getInstance();
        $this->inDB   = cmsDatabase::getInstance();
        $this->inPage = cmsPage::getInstance();
        $this->config = array_merge($this->config, $this->inCore->loadPluginConfig(get_called_class()));

    }

    public function __clone() {}

// ================================================================== //

    public function install() {

        return $this->inCore->installPlugin($this->info, $this->events, $this->config);

    }

// ================================================================== //

    public function upgrade() {

        return $this->inCore->upgradePlugin($this->info, $this->events, $this->config);

    }

// ================================================================== //

    public function execute($event='', $item=array()) {}

// ================================================================== //

    public function saveConfig() {

        $this->inCore->savePluginConfig( $this->info['plugin'], $this->config );

    }

// ================================================================== //

}
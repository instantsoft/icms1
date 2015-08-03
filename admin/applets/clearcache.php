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

if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

function applet_clearcache() {

  global $adminAccess;
  global $_LANG;

  if (!cmsUser::isAdminCan('admin/config', $adminAccess)) { cpAccessDenied(); }

  cmsCore::clearCache();

  cmsCore::addSessionMessage($_LANG['AD_CLEAR_CACHE_SUCCESS'], 'success');

  cmsCore::redirectBack();

}
?>
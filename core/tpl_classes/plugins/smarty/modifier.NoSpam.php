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
function smarty_modifier_nospam($email, $filterLevel = 'normal'){
    $email = strrev($email);
    $email = preg_replace('[\.]', '/', $email, 1);
    $email = preg_replace('[@]', '/', $email, 1);

    if($filterLevel == 'low') {
        $email = strrev($email);
    }

    return $email;
}
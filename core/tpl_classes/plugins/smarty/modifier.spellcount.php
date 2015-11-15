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
function smarty_modifier_spellcount($num, $one, $two, $many) {
    if ($num%10==1 && $num%100!=11){
        echo $num.' '.$one;
    }
    elseif($num%10>=2 && $num%10<=4 && ($num%100<10 || $num%100>=20)){
        echo $num.' '.$two;
    }
    else{
        echo $num.' '.$many;
    }
}
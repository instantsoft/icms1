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

function smarty_function_dateform($params, &$smarty){

    if (empty($params['element'])) {
        $params['element'] = 'birthdate';
    }
    if (empty($params['seldate'])) {
        $params['seldate'] = false;
    }

    $year_from = 1950;
    $year_to   = intval(date('Y'));
    $day_default=1; $month_default=1; $year_default=1980;
    global $_LANG;

    $html = '';

    if($params['seldate']){
        $parts = explode('-', $params['seldate']);
        if ($parts[2]){
            $day_default = (int)$parts[2];
        }
        if ($parts[1]){
            $month_default = (string)$parts[1];
        }
        if ($parts[0]){
            $year_default = (int)$parts[0];
        }
    }

    // Дни
    $html .= '<select name="'.$params['element'].'[day]">' . "\n";
    for($day=1; $day<=31;$day++){
        if ($day<10){ $day = '0'.$day; }
        if ($day == $day_default){
            $html .= '<option value="'.$day.'" selected="selected">'.$day.'</option>'. "\n";
        } else {
            $html .= '<option value="'.$day.'">'.$day.'</option>'. "\n";
        }
    }
    $html .= '</select>'. "\n";

    // Месяцы
    $html .= '<select name="'.$params['element'].'[month]">' . "\n";
    for($month=1; $month<13; $month++){
        if ($month<10){ $month = '0'.$month; }
        if (($month) == $month_default){
            $html .= '<option value="'.($month).'" selected="selected">'.$_LANG['MONTH_'.$month.'_ONE'].'</option>'. "\n";
        } else {
            $html .= '<option value="'.($month).'">'.$_LANG['MONTH_'.$month.'_ONE'].'</option>'. "\n";
        }
    }
    $html .= '</select>'. "\n";

    // Года
    $html .= '<select name="'.$params['element'].'[year]">'. "\n";
    for($year=$year_from; $year<=$year_to;$year++){
        if ($year == $year_default){
            $html .= '<option value="'.$year.'" selected="selected">'.$year.'</option>'. "\n";
        } else {
            $html .= '<option value="'.$year.'">'.$year.'</option>'. "\n";
        }
    }
    $html .= '</select>'. "\n";

    return $html;

}
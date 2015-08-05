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
/**
 * Накладывает ватермарк на изображение
 * @param string $src Путь к изображению
 * @return boolean
 */
function img_add_watermark($src) {

    $size = getimagesize($src);
    if ($size === false) {
        return false;
    }

    $format = mb_strtolower(mb_substr($size['mime'], mb_strpos($size['mime'], '/') + 1));
    $icfunc = 'imagecreatefrom'.$format;
    $igfunc = 'image'.$format;

    if (!function_exists($icfunc)) {
        return false;
    }

    if (!function_exists($igfunc)) {
        return false;
    }

    $isrc = $icfunc($src);

    if ($format == 'png' || $format == 'gif') {
        imagealphablending($isrc, true);
        imagesavealpha($isrc, true);
    }

    img_watermark($isrc, $size[0], $size[1]);

    // вывод картинки и очистка памяти
    $igfunc($isrc, $src);
    imagedestroy($isrc);

}

function img_watermark(&$img, $w, $h) {

    $inConf = cmsConfig::getInstance();

    if (!$inConf->wmark) {
        return;
    }

    $wm_file = PATH . '/images/' . $inConf->wmark;

    if (!file_exists($wm_file)) {
        return;
    }

    $size = getimagesize($wm_file);

    $wm = imagecreatefrompng($wm_file);

    $wm_w = $size[0];
    $wm_h = $size[1];

    $wm_x = $w - $wm_w;
    $wm_y = $h - $wm_h;

    imagealphablending($img, true);
    imagesavealpha($img, true);
    imagecopyresampled($img, $wm, $wm_x, $wm_y, 0, 0, $wm_w, $wm_h, $wm_w, $wm_h);

}

/**
 * Изменяет изображение согласно переданных параметров
 * @param string $src Исходный файл
 * @param string $dest Результирующий файл
 * @param int $maxwidth Ширина, в которую ресайзить исходное изображение
 * @param int $maxheight Высота
 * @param bool $is_square Делать квадратное изображение?
 * @param bool $watermark Накладывать ватермарк?
 * @param string $rgb Цвет подложки
 * @param int $quality Качество изображения
 * @return boolean
 */
function img_resize($src, $dest, $maxwidth, $maxheight = 160, $is_square = false, $watermark = false, $rgb = 0xFFFFFF, $quality = 95) {

    if (!file_exists($src)) {
        return false;
    }

    $upload_dir = dirname($dest);
    if (!is_writable($upload_dir)) {
        @chmod($upload_dir, 0777);
    }

    $size = getimagesize($src);
    if ($size === false) {
        return false;
    }

    $new_width  = $size[0];
    $new_height = $size[1];

    // Определяем исходный формат по MIME-информации, предоставленной
    // функцией getimagesize, и выбираем соответствующую формату
    // imagecreatefrom-функцию.
    $format = mb_strtolower(mb_substr($size['mime'], mb_strpos($size['mime'], '/') + 1));
    $icfunc = 'imagecreatefrom'.$format;
    $igfunc = 'image'.$format;

    if (!function_exists($icfunc)) {
        return false;
    }

    if (!function_exists($igfunc)) {
        return false;
    }

    if ($format == 'png') {
        $quality = ( 10 - ceil($quality / 10) );
    }

    if ($format == 'gif') {
        $quality = NULL;
    }

    $isrc = $icfunc($src);

    if (($new_height <= $maxheight) && ($new_width <= $maxwidth)) {

        if ($watermark) {

            if ($format == 'png') {
                imagealphablending($isrc, true);
                imagesavealpha($isrc, true);
            }

            img_watermark($isrc, $new_width, $new_height);
            $igfunc($isrc, $dest, $quality);

        } else {
            @copy($src, $dest);
        }

        return true;

    }

    if ($is_square) {

        $idest = imagecreatetruecolor($maxwidth, $maxwidth);

        if ($format == 'jpeg') {
            imagefill($idest, 0, 0, $rgb);
        } else if ($format == 'png' || $format == 'gif') {
            $trans = imagecolorallocatealpha($idest, 255, 255, 255, 127);
            imagefill($idest, 0, 0, $trans);
            imagealphablending($idest, true);
            imagesavealpha($idest, true);
        }

        // вырезаем квадратную серединку по x, если фото горизонтальное
        if ($new_width > $new_height) {

            imagecopyresampled(
                    $idest, $isrc, 0, 0, round(( max($new_width, $new_height) - min($new_width, $new_height) ) / 2), 0, $maxwidth, $maxwidth, min($new_width, $new_height), min($new_width, $new_height)
            );
        }

        // вырезаем квадратную верхушку по y,
        if ($new_width < $new_height) {
            imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $maxwidth, $maxwidth, min($new_width, $new_height), min($new_width, $new_height));
        }

        // квадратная картинка масштабируется без вырезок
        if ($new_width == $new_height) {
            imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $maxwidth, $maxwidth, $new_width, $new_width);
        }

    } else {

        if ($new_width > $maxwidth) {

            $wscale = $maxwidth / $new_width;
            $new_width *= $wscale;
            $new_height *= $wscale;
        }

        if ($new_height > $maxheight) {

            $hscale = $maxheight / $new_height;
            $new_width *= $hscale;
            $new_height *= $hscale;
        }

        $idest = imagecreatetruecolor($new_width, $new_height);

        if ($format == 'jpeg') {
            imagefill($idest, 0, 0, $rgb);
        } else if ($format == 'png' || $format == 'gif') {
            $trans = imagecolorallocatealpha($idest, 255, 255, 255, 127);
            imagefill($idest, 0, 0, $trans);
            imagealphablending($idest, true);
            imagesavealpha($idest, true);
        }

        imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $new_width, $new_height, $size[0], $size[1]);

    }

    if ($watermark) {
        img_watermark($idest, $new_width, $new_height);
    }

    if ($format == 'jpeg') {
        imageinterlace($idest, 1);
    }

    // вывод картинки и очистка памяти
    $igfunc($idest, $dest, $quality);
    imagedestroy($isrc);
    imagedestroy($idest);

    return true;

}

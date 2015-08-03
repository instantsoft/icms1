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

class p_ckeditor extends cmsPlugin {

    public $config = array(
        'iswatermark'       => 0,
        'photo_width'       => 600,
        'photo_height'      => 600,
        'is_compatible'     => 1,
        'entermode'         => 'CKEDITOR.ENTER_P',
        'skin'              => 'moono',
        'upload_for_groups' => array(2)
    );

    public function __construct() {

        global $_LANG;

        $this->info = array(
            'plugin'      => 'p_ckeditor',
            'title'       => 'CKEditor',
            'description' => $_LANG['CK_DESCRIPTION'],
            'author'      => 'InstantCMS Team',
            'version'     => '4.4.5',
            'published'   => 1,
            'plugin_type' => 'wysiwyg'
        );

        $this->events = array(
            'INSERT_WYSIWYG'
        );

        parent::__construct();

    }

    public function execute($event = '', $item = array()) {

        $access = (cmsUser::getInstance()->is_admin) ? 'admin' : 'user';
        $width  = (is_numeric($item['width']) ? $item['width'].'px' : $item['width']);
        $height = (is_numeric($item['height']) ? $item['height'].'px' : $item['height']);

        ob_start(); ?>

        <textarea class="ckeditor" id="<?php echo $item['name']; ?>" name="<?php echo $item['name']; ?>" style="width: <?php echo $width; ?>; height: <?php echo $height; ?>;"><?php echo htmlspecialchars($item['text']); ?></textarea>
        <script type="text/javascript">
                $(function (){
                    if(typeof CKEDITOR == 'undefined') {
                        script = document.createElement('script');
                        script.type = 'text/javascript';
                        script.src  = '/plugins/p_ckeditor/editor/ckeditor.js';
                        $('head').append(script);
                    }

                    <?php echo ($this->config['is_compatible'] ? 'CKEDITOR.env.isCompatible = true;' : ''); ?>
                    CKEDITOR.replace("<?php echo $item['name']; ?>",{
                        customConfig : "/plugins/p_ckeditor/editor/config/<?php echo $access; ?>_<?php echo $item['toolbar']; ?>.js",
                        skin: "<?php echo $this->config['skin']; ?>",
                        width: "<?php echo $width; ?>",
                        height: "<?php echo $height; ?>",
                        forcePasteAsPlainText: true,
                        extraPlugins: "colorbutton,panelbutton",
                        <?php echo ($this->canUpload() ? 'filebrowserUploadUrl: "/plugins/p_ckeditor/upload.php?component='.$this->inCore->component.'",' : ''); ?>
                        locationMapPath: "<?php echo HOST; ?>/plugins/p_ckeditor/editor/plugins/locationmap/",
                        enterMode: <?php echo $this->config['entermode']; ?>,
                        language: "<?php echo cmsConfig::getConfig('lang'); ?>"
                    });
                });
          </script>

        <?php return ob_get_clean();

    }

    public function canUpload(){
        return ($this->config['upload_for_groups'] ? in_array(cmsUser::getInstance()->group_id, $this->config['upload_for_groups']) : false);
    }

}
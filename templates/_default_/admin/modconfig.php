<?php global $mod, $cfg_form, $mode, $inCore, $_LANG; ?>
<form action="/admin/index.php?view=modules&do=save_auto_config&id=<?php echo $mod['id']; ?>&ajax=1<?php if($mode!='xml'){?>&title_only=1<?php } ?>" method="post" name="optform" target="_self" id="optform">
    <div id="mc_module_title">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td width="145"><div class="title"><?php echo $_LANG['AD_MODULE_CONFIG']; ?></div></td>
                <td>
                    <div class="value">
                        <input type="text" name="title" value="<?php echo $mod['title']; ?>" />
                    </div>
                </td>
            </tr>
            <tr>
                <td width="145">&nbsp;</td>
                <td style="padding-top:4px">
                    <label>
                        <input type="checkbox" name="published" value="1" <?php if ($mod['published']){ ?>checked="checked"<?php } ?> /> <?php echo $_LANG['AD_ENABLE']; ?></label>
                </td>
            </tr>
        </table>
    </div>
    <div id="mc_module_cfg">
        <?php if ($mode == 'xml'){ ?>
            <?php echo $cfg_form; ?>
        <?php } elseif($mode == 'php') { ?>
            <div class="params-form">
                <?php echo sprintf($_LANG['AD_MODULE_CONFIGURED_IN_CP'], $mod['id']); ?>.
            </div>
            <div class="params-buttons">
                <input type="submit" name="save" value="<?php echo $_LANG['SAVE']; ?>" />
            </div>
        <?php } elseif($mode == 'none') { ?>
            <div class="params-form">
                <?php echo $_LANG['AD_MODULE_NO_CONFIGURED']; ?>.
            </div>
            <div class="params-buttons">
                <input type="submit" name="save" value="<?php echo $_LANG['SAVE']; ?>" />
            </div>
        <?php } else { ?>
            <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
            <?php $inCore->insertEditor('content', $mod['content'], '500', '99%'); ?>
            <div class="params-buttons">
               <input type="submit" name="save" value="<?php echo $_LANG['SAVE']; ?>" />
            </div>
        <?php } ?>
    </div>
</form>
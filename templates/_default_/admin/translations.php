<form action="<?php echo $action; ?>" method="post" target="_self" id="lang_form">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
    <input type="hidden" name="save" value="1" />
    <?php if ($type == 'html'){ ?>
        <?php cmsCore::insertEditor('field_data', $value, '500', '99%'); ?>
    <?php } else { ?>
    <input type="text" style="width: 99%" placeholder="<?php echo $_LANG['AD_HINT_DEFAULT']; ?>" name="field_data" value="<?php echo htmlspecialchars($value); ?>" />
    <?php } ?>
    <div class="lang_submit">
        <input type="submit" name="save" class="button" value="<?php echo $_LANG['SAVE']; ?>" />
    </div>
</form>
<script type="text/javascript">
    $('#lang_form').on('submit', function (){
        if(typeof CKEDITOR != 'undefined') {
            for ( instance in CKEDITOR.instances ) {
                CKEDITOR.instances[instance].updateElement();
            }
        }
        $(this).ajaxSubmit({
            success: function(result, statusText, xhr, $form){
                $.colorbox.close();
            }
        });
        return false;
    });
</script>
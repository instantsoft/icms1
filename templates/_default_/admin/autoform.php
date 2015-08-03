<?php

    global $tpl_data, $_LANG;
    extract($tpl_data);

?>

<input type="hidden" name="do" value="save_auto_config" />
<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />

<div class="params-form">
    <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <?php foreach($fields as $fid=>$field){ ?>
        <tr id="f<?php echo $fid; ?>">
            <td class="param-name">
                <div class="label"><strong><?php echo $field['title']; ?></strong></div>
                <?php if ($field['hint']) { ?>
                    <div class="hinttext"><?php echo $field['hint']; ?></div>
                <?php } ?>

                <?php if ($field['type']=='list_db' && $field['multiple']) { ?>
                    <div class="param-links">
                        <a href="javascript:void(0);" onclick="$('tr#f<?php echo $fid; ?> td input:checkbox').prop('checked', true)"><?php echo $_LANG['SELECT_ALL']; ?></a> |
                        <a href="javascript:void(0);" onclick="$('tr#f<?php echo $fid; ?> td input:checkbox').prop('checked', false)"><?php echo $_LANG['REMOVE_ALL']; ?></a>
                    </div>
                <?php } ?>
            </td>
            <td class="param-value">
                <?php echo $field['html']; ?>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<div class="params-buttons">
    <input type="submit" name="save" class="button" value="<?php echo $_LANG['SAVE']; ?>" />
</div>

<script type="text/javascript">
    function submitModuleConfig(){
        $('#optform').submit();
    }
</script>


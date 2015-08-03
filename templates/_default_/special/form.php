<?php if($formObj->form['showtitle']) { ?>
	<h3 class="userform_title"><?php echo $formObj->form['title'] ?></h3>
<?php } ?>

<?php if($formObj->form['description']) { ?>
	<p><?php echo $formObj->form['description'] ?></p>
<?php } ?>

<?php if(!$formObj->form['only_fields']) { ?>
    <form name="userform" enctype="multipart/form-data" action="<?php echo $formObj->form['form_action'] ?>" method="POST">
    <input type="hidden" name="form_id" value="<?php echo $formObj->form['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
<?php } ?>

    <table class="userform_table" cellpadding="3">
    <?php foreach ($formObj->form_fields as $form_field) { ?>
        <tr>
        	<td class="userform_fieldtitle">
        	<?php if($formObj->is_admin) { ?>
            	[<font color="gray"><?php echo $form_field['ordering'] ?></font>]
        	<?php } ?>
			<?php echo $form_field['title'] ?>
			<?php if($form_field['mustbe']) { ?>
            	<span class="mustbe">*</span>
            <?php } ?>

           <?php if($formObj->is_admin) { ?>
                <span class="edit_links">
                <a href="?view=components&do=config&id=<?php echo (int)$_REQUEST['id'] ?>&opt=del_field&form_id=<?php echo $formObj->form['id'] ?>&item_id=<?php echo $form_field['id'] ?>" title="<?php echo $_LANG['DELETE']; ?>"><img src="/admin/images/actions/delete.gif" border="0" /></a>
                <a href="?view=components&do=config&id=<?php echo (int)$_REQUEST['id'] ?>&opt=edit&item_id=<?php echo $formObj->form['id'] ?>&field_id=<?php echo $form_field['id'] ?>" title="<?php echo $_LANG['EDIT_FIELD']; ?>"><img src="/admin/images/actions/edit.gif" border="0" /></a>
                <a href="?view=components&do=config&id=<?php echo (int)$_REQUEST['id'] ?>&opt=up_field&form_id=<?php echo $formObj->form['id'] ?>&item_id=<?php echo $form_field['id'] ?>" title="<?php echo $_LANG['FIELD_MOVE_UP']; ?>"><img src="/admin/images/actions/top.gif" border="0" /></a>
                <a href="?view=components&do=config&id=<?php echo (int)$_REQUEST['id'] ?>&opt=down_field&form_id=<?php echo $formObj->form['id'] ?>&item_id=<?php echo $form_field['id'] ?>" title="<?php echo $_LANG['FIELD_MOVE_DOWN']; ?>"><img src="/admin/images/actions/down.gif" border="0" /></a>
                </span>
            <?php } ?>
        	</td>
         </tr>
         <tr><td><?php echo $form_field['field'] ?></td></tr>
    <?php } ?>
    <?php if(!$formObj->is_admin && !$formObj->form['only_fields']) { ?>
         <tr><td>
            <?php echo cmsPage::getCaptcha(); ?>
         </td></tr>
    <?php } ?>
    <?php if(!$formObj->form['only_fields']) { ?>
         <tr><td><div style="margin-top:10px">
             <input type="submit" value="<?php echo $_LANG['SEND']; ?>" />
         </div></td></tr>
    <?php } ?>
    </table>
<?php if(!$formObj->form['only_fields']) { ?>
	</form>
<?php } ?>
<?php if($formObj->is_admin) { ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('td.userform_fieldtitle').hover(
            function() {
                $(this).find('span.edit_links').fadeIn();
            },
            function() {
                $(this).find('span.edit_links').hide();
            }
        );
    });
</script>
<?php } ?>

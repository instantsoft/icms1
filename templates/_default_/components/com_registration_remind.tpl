<h1 class="con_heading">{$LANG.RECOVER_PASS}</h1>

{add_js file='components/registration/js/check.js'}
<form action="" method="post">
    <input type="hidden" name="csrf_token" value="{csrf_token}" />
    <table cellpadding="5" cellspacing="2" border="0" style="margin-bottom: 15px">
        <tr>
            <td width="130"><strong>{$LANG.LOGIN}:</strong></td>
            <td width="" height="24"><input type="text" name="pass" value="{$user.login}" disabled="disabled" style="width:200px" class="text-input"/></td>
        </tr>
        <tr>
            <td><strong>{$LANG.PASS}:</strong></td>
            <td><input type="password" name="pass" id="pass1input" value="" style="width:200px" class="text-input" onchange="$('#passcheck').html('');" /></td>
        </tr>
        <tr>
            <td><strong>{$LANG.REPEAT_PASS}:</strong></td>
            <td><input type="password" name="pass2" id="pass2input" value="" style="width:200px" class="text-input" onchange="checkPasswords()" /><div id="passcheck"></div></td>
        </tr>
    </table>

    <input type="submit" id="submit" name="submit" value="{$LANG.CHANGE_PASS}" />

</form>
<script type="text/javascript">
    $(function(){
        $('input[name=pass]').focus();
    });
</script>
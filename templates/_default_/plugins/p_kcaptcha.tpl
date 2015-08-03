<table align="left" cellpadding="2" cellspacing="0">
    <tr>
        <td valign="middle" width="130" style="padding-left:0">
            <img id="kcaptcha{$input_id}" class="captcha" src="/plugins/p_kcaptcha/codegen/cms_codegen.php?id={$input_id}" />
        </td>
        <td valign="middle">
            <div>{$LANG.P_CAPTCHA_CODE}:</div>
            <div><input name="captcha_code" type="text" style="width:120px" class="text-input" /></div>
            <div><a href="#" onclick="$('#kcaptcha{$input_id}').attr('src', '/plugins/p_kcaptcha/codegen/cms_codegen.php?'+Math.random()+'&id={$input_id}');return false;"><small>{$LANG.P_CAPTCHA_RELOAD}</small></a></div>
            <input name="captcha_id" type="hidden" value="{$input_id}"/>
        </td>
    </tr>
</table>
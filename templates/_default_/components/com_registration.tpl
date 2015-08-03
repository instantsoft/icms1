<div class="con_heading">{$pagetitle}</div>

{if $cfg.is_on}

    {if $cfg.reg_type == 'invite' && !$correct_invite}

        <p style="margin-bottom:15px; font-size: 14px">{$LANG.INVITES_ONLY}</p>

        <form id="regform" name="regform" method="post" action="/registration">
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td><strong>{$LANG.INVITE_CODE}:</strong></td>
                <td style="padding-left:15px">
                    <input type="text" name="invite_code" class="text-input" value="" style="width:300px"/>
                </td>
                <td style="padding-left:5px">
                    <input type="submit" name="show_invite" value="{$LANG.SHOW_INVITE}" />
                </td>
            </tr>
        </table>
        </form>

    {else}

        {add_js file='components/registration/js/check.js'}

        <form id="regform" name="regform" method="post" action="/registration/add">
            <input type="hidden" name="csrf_token" value="{csrf_token}" />
            <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
                <tr>
                    <td width="269" valign="top" class="">
                        <div><strong>{$LANG.LOGIN}:</strong></div>
                        <div><small>{$LANG.USED_FOR_AUTH}<br/>{$LANG.ONLY_LAT_SYMBOLS}</small></div>
                    </td>
                    <td valign="top" class="">
                        <input name="login" id="logininput" class="text-input" type="text" style="width:300px" value="{$item.login|escape:'html'}" onchange="checkLogin()" autocomplete="off"/>
                        <span class="regstar">*</span>
                        <div id="logincheck"></div>
                    </td>
                </tr>
                {if $cfg.name_mode == 'nickname'}
                    <tr>
                        <td valign="top" class="" width="269">
                            <div><strong>{$LANG.NICKNAME}:</strong></div>
                            <small>{$LANG.NICKNAME_TEXT}</small>
                        </td>
                        <td valign="top" class="">
                            <input name="nickname" id="nickinput" class="text-input" type="text" style="width:300px" value="{$item.nickname|escape:'html'}" />
                            <span class="regstar">*</span>
                        </td>
                    </tr>
                {else}
                    <tr>
                        <td valign="top" class="">
                            <div><strong>{$LANG.NAME}:</strong></div>
                        </td>
                        <td valign="top" class="">
                            <input name="realname1" id="realname1" class="text-input" type="text" style="width:300px" value="{$item.realname1|escape:'html'}" />
                            <span class="regstar">*</span>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="">
                            <div><strong>{$LANG.SURNAME}:</strong></div>
                        </td>
                        <td valign="top" class="">
                            <input name="realname2" id="realname2" class="text-input" type="text" style="width:300px" value="{$item.realname2|escape:'html'}" />
                            <span class="regstar">*</span>
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td valign="top" class=""><strong>{$LANG.PASS}:</strong></td>
                    <td valign="top" class="">
                        <input name="pass" id="pass1input" class="text-input" type="password" style="width:300px" onchange="$('#passcheck').html('');"/>
                        <span class="regstar">*</span>
                    </td>
                </tr>
                <tr>
                    <td valign="top" class=""><strong>{$LANG.REPEAT_PASS}: </strong></td>
                    <td valign="top" class="">
                        <input name="pass2" id="pass2input" class="text-input" type="password" style="width:300px" onchange="checkPasswords()" />
                        <span class="regstar">*</span>
                        <div id="passcheck"></div>
                    </td>
                </tr>
                <tr>
                    <td valign="top" class="">
                        <div><strong>{$LANG.EMAIL}:</strong></div>
                        <div><small>{$LANG.NOPUBLISH_TEXT}</small></div>
                    </td>
                    <td valign="top" class="">
                        <input name="email" type="text" class="text-input" style="width:300px" value="{$item.email}"/>
                        <span class="regstar">*</span>
                    </td>
                </tr>
                {if $private_forms}
                    {foreach key=tid item=field from=$private_forms}
                    <tr>
                        <td valign="top">
                            <strong>{$field.title}:</strong>
                            {if $field.description}
                                <div><small>{$field.description}</small></div>
                            {/if}
                        </td>
                        <td valign="top">
                            {$field.field} <span class="regstar">*</span>
                        </td>
                    </tr>
                    {/foreach}
                {/if}
                {if $cfg.ask_city}
                    <tr>
                        <td valign="top" class=""><strong>{$LANG.CITY}:</strong></td>
                        <td valign="top" class="">
                            {city_input value=$item.city name="city" width="300px"}
                        </td>
                    </tr>
                {/if}
                {if $cfg.ask_icq}
                    <tr>
                        <td valign="top" class=""><strong>ICQ:</strong></td>
                        <td valign="top" class="">
                            <input name="icq" type="text" class="text-input" id="icq" value="{$item.icq|escape:'html'}" style="width:300px"/>
                        </td>
                    </tr>
                {/if}
                {if $cfg.ask_birthdate}
                    <tr>
                        <td valign="top" class="">
                            <div><strong>{$LANG.BIRTH}:</strong></div>
                            <div><small>{$LANG.NOPUBLISH_TEXT}</small></div>
                        </td>
                        <td valign="top" class="">{dateform seldate=$item.birthdate}</td>
                    </tr>
                {/if}
                <tr>
                    <td valign="top" class="">
                        <div><strong>{$LANG.SECUR_SPAM}: </strong></div>
                        <div><small>{$LANG.SECUR_SPAM_TEXT}</small></div>
                    </td>
                    <td valign="top" class="">{captcha}</td>
                </tr>
                <tr>
                    <td valign="top" class="">&nbsp;</td>
                    <td valign="top" class="">
                        <input name="do" type="hidden" value="register" />
                        <input name="save" type="submit" id="save" value="{$LANG.REGISTRATION}" />
                    </td>
                </tr>
            </table>
        </form>

    {/if}

{else}

    <div style="margin-top:10px">{$cfg.offmsg}</div>

{/if}


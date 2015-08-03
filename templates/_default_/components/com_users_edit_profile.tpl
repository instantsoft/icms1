{add_js file='includes/jquery/tabs/jquery.ui.min.js'}
{add_css file='includes/jquery/tabs/tabs.css'}

<script type="text/javascript">
    $(function(){ $(".uitabs").tabs(); });
</script>

<div class="con_heading">{$LANG.CONFIG_PROFILE}</div>

<div id="profiletabs" class="uitabs">
    <ul id="tabs">
        <li><a href="#about"><span>{$LANG.ABOUT_ME}</span></a></li>
        <li><a href="#contacts"><span>{$LANG.CONTACTS}</span></a></li>
        <li><a href="#notices"><span>{$LANG.NOTIFIC}</span></a></li>
        <li><a href="#policy"><span>{$LANG.PRIVACY}</span></a></li>
        <li rel="hid"><a href="#change_password"><span>{$LANG.CHANGING_PASS}</span></a></li>
    </ul>

    <form id="editform" name="editform" enctype="multipart/form-data" method="post" action="">
        <input type="hidden" name="opt" value="save" />
        <input type="hidden" name="csrf_token" value="{csrf_token}" />
        <div id="about">
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td width="300" valign="top">
                        <strong>{$LANG.YOUR_NAME}: </strong><br />
                        <span class="usr_edithint">{$LANG.YOUR_NAME_TEXT}</span>
                    </td>
                    <td valign="top"><input name="nickname" type="text" class="text-input" id="nickname" style="width:300px" value="{$usr.nickname|escape:'html'}"/></td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.SEX}:</strong></td>
                    <td valign="top">
                        <select name="gender" id="gender" style="width:307px">
                            <option value="0" {if $usr.gender==0} selected {/if}>{$LANG.NOT_SPECIFIED}</option>
                            <option value="m" {if $usr.gender=='m'} selected {/if}>{$LANG.MALES}</option>
                            <option value="f" {if $usr.gender=='f'} selected {/if}>{$LANG.FEMALES}</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <strong>{$LANG.CITY}:</strong><br />
                        <span class="usr_edithint">{$LANG.CITY_TEXT}</span>
                    </td>
                    <td valign="top">
                        {city_input value=$usr.city name="city" width="300px"}
                    </td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.BIRTH}:</strong> </td>
                    <td valign="top">
                        {dateform seldate=$usr.birthdate}
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <strong>{$LANG.HOBBY} ({$LANG.TAGSS}): </strong><br/>
                        <span class="usr_edithint">{$LANG.YOUR_KEYWORDS}</span><br />
                        <span class="usr_edithint">{$LANG.TAGSS_TEXT}</span>
                    </td>
                    <td valign="top">
                        <textarea name="description" class="text-input" style="width:300px" rows="2" id="description">{$usr.description}</textarea>
                    </td>
                </tr>
                {if $cfg_forum.component_enabled}
                <tr>
                    <td valign="top">
                        <strong>{$LANG.SIGNED_FORUM}:</strong><br />
                        <span class="usr_edithint">{$LANG.CAN_USE_BBCODE} </span>
                    </td>
                    <td valign="top">
                        <textarea name="signature" class="text-input" style="width:300px" rows="2" id="signature">{$usr.signature|escape:'html'}</textarea>
                    </td>
                </tr>
                {/if}
                {if $private_forms}
                    {foreach key=tid item=field from=$private_forms}
                    <tr>
                        <td valign="top">
                            <strong>{$field.title}:</strong>
                            {if $field.description}
                                <br /><span class="usr_edithint">{$field.description}</span>
                            {/if}
                        </td>
                        <td valign="top">
                            {$field.field}
                        </td>
                    </tr>
                    {/foreach}
                {/if}
            </table>
        </div>

        <div id="contacts">
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td width="300" valign="top">
                        <strong>E-mail:</strong><br />
                        <span class="usr_edithint">{$LANG.REALY_ADRESS_EMAIL}</span>
                    </td>
                    <td valign="top">
                        <input name="email" type="text" class="text-input" id="email" style="width:300px" value="{$usr.email}"/>
                    </td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.NUMBER_ICQ} :</strong></td>
                    <td valign="top"><input name="icq" class="text-input" type="text" id="icq" style="width:300px" value="{$usr.icq|escape:'html'}"/></td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.PHONE} :</strong><br /><span class="usr_edithint">{$LANG.PHONE_HINT}</span></td>
                    <td valign="top"><input name="phone" class="text-input" type="text" id="phone" style="width:300px" value="{$usr.phone}"/></td>
                </tr>
            </table>
        </div>

        <div id="notices">
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td width="300" valign="top">
                        <strong>
                            {$LANG.NOTIFY_NEW_MESS}:
                        </strong><br/>
                        <span class="usr_edithint">
                            {$LANG.NOTIFY_NEW_MESS_TEXT}
                        </span>
                    </td>
                    <td valign="top">
                        <label><input name="email_newmsg" type="radio" value="1" {if $usr.email_newmsg}checked{/if}/> {$LANG.YES} </label>
                        <label><input name="email_newmsg" type="radio" value="0" {if !$usr.email_newmsg}checked{/if}/> {$LANG.NO}</label>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <strong>{$LANG.HOW_NOTIFY_NEW_MESS} </strong><br />
                        <span class="usr_edithint">{$LANG.WHERE_TO_SEND}</span>
                    </td>
                    <td valign="top">
                        <select name="cm_subscribe" id="cm_subscribe" style="width:307px">
                            <option value="mail" {if $usr.cm_subscribe=='mail'}selected{/if}>{$LANG.TO_EMAIL}</option>
                            <option value="priv" {if $usr.cm_subscribe=='priv'}selected{/if}>{$LANG.TO_PRIVATE_MESS}</option>
                            <option value="both" {if $usr.cm_subscribe=='both'}selected{/if}>{$LANG.TO_EMAIL_PRIVATE_MESS}</option>
                            <option value="none" {if $usr.cm_subscribe=='none'}selected{/if}>{$LANG.NOT_SEND}</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div id="policy">
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td width="300" valign="top">
                        <strong>{$LANG.SHOW_EMAIL}:</strong><br/>
                        <span class="usr_edithint">{$LANG.SHOW_EMAIL_TEXT}</span>
                    </td>
                    <td valign="top">
                        <label><input name="showmail" type="radio" value="1" {if $usr.showmail}checked{/if}/> {$LANG.YES} </label>
                        <label><input name="showmail" type="radio" value="0" {if !$usr.showmail}checked{/if}/> {$LANG.NO} </label>
                    </td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.SHOW_ICQ}:</strong></td>
                    <td valign="top">
                        <label><input name="showicq" type="radio" value="1" {if $usr.showicq}checked{/if}/> {$LANG.YES} </label>
                        <label><input name="showicq" type="radio" value="0" {if !$usr.showicq}checked{/if}/> {$LANG.NO} </label>
                    </td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.SHOW_PHONE}:</strong></td>
                    <td valign="top">
                        <label><input name="showphone" type="radio" value="1" {if $usr.showphone}checked{/if}/> {$LANG.YES} </label>
                        <label><input name="showphone" type="radio" value="0" {if !$usr.showphone}checked{/if}/> {$LANG.NO} </label>
                    </td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.SHOW_BIRTH}:</strong> </td>
                    <td valign="top">
                        <label><input name="showbirth" type="radio" value="1" {if $usr.showbirth}checked{/if}/> {$LANG.YES} </label>
                        <label><input name="showbirth" type="radio" value="0" {if !$usr.showbirth}checked{/if}/> {$LANG.NO} </label>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <strong>{$LANG.SHOW_PROFILE}:</strong><br/>
                        <span class="usr_edithint">{$LANG.WHOM_SHOW_PROFILE} </span>
                    </td>
                    <td valign="top">
                        <select name="allow_who" id="allow_who" style="width:307px">
                            <option value="all" {if $usr.allow_who=='all'}selected{/if}>{$LANG.EVERYBODY}</option>
                            <option value="registered" {if $usr.allow_who=='registered'}selected{/if}>{$LANG.REGISTERED}</option>
                            <option value="friends" {if $usr.allow_who=='friends'}selected{/if}>{$LANG.MY_FRIENDS}</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div style="margin-top: 12px;" id="submitform">
            <input style="font-size:16px" name="save" type="submit" id="save" value="{$LANG.SAVE}" />
            <input style="font-size:16px" name="delbtn2" type="button" id="delbtn2" value="{$LANG.DEL_PROFILE}" onclick="location.href='/users/{$usr.id}/delprofile.html';" />
        </div>
    </form>
    <div id="change_password">
        <form id="editform" name="editform" method="post" action="">
            <input type="hidden" name="opt" value="changepass" />
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td width="300" valign="top">
                        <strong>{$LANG.OLD_PASS}</strong>
                    </td>
                    <td valign="top">
                        <input name="oldpass" type="password" id="oldpass" class="text-input" size="30" />
                    </td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.NEW_PASS}</strong></td>
                    <td valign="top"><input name="newpass" type="password" id="newpass" class="text-input" size="30" /></td>
                </tr>
                <tr>
                    <td valign="top"><strong>{$LANG.NEW_PASS_REPEAT}</strong></td>
                    <td valign="top"><input name="newpass2" type="password" class="text-input" id="newpass2" size="30" /></td>
                </tr>
            </table>
            <div style="margin-top: 12px;">
                <input style="font-size:16px" name="save2" type="submit" id="save2" value="{$LANG.CHANGE_PASSWORD}" />
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $( '#tabs li' ).click( function(){
            rel = $( this ).attr( "rel" );
            if(!rel){
                $('#submitform').show();
            } else {
                $('#submitform').hide();
            }
        });
    });
</script>
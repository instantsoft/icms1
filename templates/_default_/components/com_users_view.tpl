{if $cfg.sw_search}
<div id="users_search_link" class="float_bar"><a href="javascript:void(0)" onclick="$('#users_sbar').slideToggle('fast');"> <span>{$LANG.USERS_SEARCH}</span> </a> </div>
{/if}
<h1 class="con_heading">{$LANG.USERS}</h1>
{if $cfg.sw_search}
<div id="users_sbar" {if !$stext}style="display:none;"{/if}>
  <form name="usr_search_form" method="post" action="/users">
    <table cellpadding="2" width="100%">
      <tr>
        <td width="80">{$LANG.FIND}: </td>
        <td width="170"><select name="gender" id="gender" class="field" style="width:150px">
            <option value="f" {if $gender == 'f'}selected="selected"{/if}>{$LANG.FIND_FEMALE}</option>
            <option value="m" {if $gender == 'm'}selected="selected"{/if}>{$LANG.FIND_MALE}</option>
            <option value="all" {if !$gender}selected="selected"{/if}>{$LANG.FIND_ALL}</option>
          </select></td>
        <td width="80">{$LANG.AGE_FROM}</td>
        <td><input style="width:60px" name="agefrom" type="text" id="agefrom" value="{if $age_fr}{$age_fr}{/if}"/>
          {$LANG.TO}
          <input style="width:60px" name="ageto" type="text" id="ageto" value="{if $age_to}{$age_to}{/if}"/></td>
      </tr>
      <tr>
        <td> {$LANG.NAME} </td>
        <td colspan="3"><input id="name" name="name" class="longfield" type="text" value="{$name|escape:'html'}"/></td>
      </tr>
      <tr>
        <td>{$LANG.CITY}</td>
        <td colspan="3">{city_input value=$city name="city" width="408px"}</td>
      </tr>
      <tr>
        <td>{$LANG.HOBBY}</td>
        <td colspan="3"><input style="" id="hobby" class="longfield" name="hobby" type="text" value="{$hobby|escape:'html'}"/></td>
      </tr>
    </table>
	<p><label for="online" style="display:inherit;"><input id="online" name="online" type="checkbox" value="1" {if $only_online} checked="checked"{/if}> {$LANG.SHOW_ONLY_ONLINE}</label></p>
    <p>
      <input name="gosearch" type="submit" id="gosearch" value="{if $stext}{$LANG.SEARCH_IN_RESULTS}{else}{$LANG.SEARCH}{/if}" />
      {if $stext}
      	<input type="button" value="{$LANG.CANCEL_SEARCH_SHOWALL}" onclick="centerLink('/users/all.html')" />
      {/if}
      <input name="hide" type="button" id="hide" value="{$LANG.HIDE}" onclick="$('#users_sbar').slideToggle();"/>
    </p>
  </form>
</div>
{/if}

{if $stext && !$cfg.sw_search}
<div class="users_search_results"> <a href="javascript:void(0)" rel="nofollow" onclick="centerLink('/users/all.html')" style="float: right; margin:4px 0 0 0">{$LANG.CANCEL_SEARCH_SHOWALL}</a>
  <h3>{$LANG.SEARCH_RESULT}</h3>
  <ul>
    {foreach key=id item=text from=$stext}
    <li>{$text}</li>
    {/foreach}
  </ul>
</div>
{/if}
  <div class="users_list_buttons">
    <div class="button {if $link.selected=='latest'}selected{/if}"><a rel=”nofollow” href="{$link.latest}">{$LANG.LATEST}</a></div>
    <div class="button {if $link.selected=='positive'}selected{/if}"><a rel=”nofollow” href="{$link.positive}">{$LANG.POSITIVE}</a></div>
    <div class="button {if $link.selected=='rating'}selected{/if}"><a rel=”nofollow” href="{$link.rating}">{$LANG.RATING}</a></div>
    {if $link.selected=='group'}
    <div class="button selected"><a rel=”nofollow” href="{$link.group}">{$LANG.GROUP_SEARCH_NAME}</a></div>
    {/if}
  </div>
  <div class="users_list">
    <table width="100%" cellspacing="0" cellpadding="0" class="users_list">
      {if $total}
      {foreach key=tid item=usr from=$users}
      <tr>
        <td width="80" valign="top"><div class="avatar"><a href="{profile_url login=$usr.login}"><img alt="{$usr.nickname|escape:'html'}" class="usr_img_small" src="{$usr.avatar}" /></a></div></td>
        <td valign="top">
        	{if $link.selected=='rating'}
          		<div class="rating" title="{$LANG.RATING}">{$usr.rating|rating}</div>
          	{/if}
          	{if $link.selected=='positive'}
          		<div title="{$LANG.KARMA}" class="karma{if $usr.karma > 0} pos{/if}{if $usr.karma < 0} neg{/if}">{if $usr.karma > 0}+{/if}{$usr.karma}</div>
          	{/if}
          <div class="status">
          	{if $usr.is_online}
            	<span class="online">{$LANG.ONLINE}</span>
            {else}
            	<span class="offline">{$usr.flogdate}</span>
            {/if}
          </div>
          <div class="nickname">{$usr.user_link}</div>
          {if $usr.microstatus}
          <div class="microstatus">{$usr.microstatus}</div>
          {/if} </td>
      </tr>
      {/foreach}
      {else}
      <tr>
        <td><p>{$LANG.USERS_NOT_FOUND}.</p></td>
      </tr>
      {/if}
    </table>
  </div>
  {$pagebar}
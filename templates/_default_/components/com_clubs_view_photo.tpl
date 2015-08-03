<div class="float_bar">
<strong>{$LANG.RATING}: </strong><span id="karmapoints">{$photo.rating|rating}</span> | <strong>{$LANG.HITS}: </strong> {$photo.hits} | {if !$photo.published}<span id="pub_photo_wait" style="color:#F00;">{$LANG.WAIT_MODERING}</span><span id="pub_photo_date" style="display:none;">{$photo.pubdate}</span>{else}{$photo.pubdate}{/if} | <a href="{profile_url login=$photo.login}">{$photo.nickname}</a> {if $is_author || $is_admin || $is_moder}| <a class="ajaxlink" href="javascript:void(0)" onclick="clubs.editPhoto({$photo.id});return false;">{$LANG.EDIT}</a> {if $is_admin || $is_moder}{if !$photo.published}<span id="pub_photo_link">  | <a class="ajaxlink" href="javascript:void(0)" onclick="clubs.publishPhoto({$photo.id});return false;">{$LANG.PUBLISH}</a></span>{/if} | <a class="ajaxlink" href="javascript:void(0)" onclick="clubs.deletePhoto({$photo.id}, '{csrf_token}');return false;">{$LANG.DELETE}</a>{/if}{/if}
</div>

<h1 class="con_heading">{$photo.title}</h1>
{if $photo.description}
    <p class="photo_desc"> {$photo.description|nl2br} </p>
{/if}
<table width="100%" cellspacing="0" cellpadding="3" border="0">
  <tbody>
    <tr>
      <td width="150px" valign="middle" align="center">
      {if $photo.previd}
        <cite>{$LANG.PREVIOUS}</cite><br>
        <a href="/clubs/photo{$photo.previd.id}.html#main"><img alt="{$photo.previd.title|escape:'html'}" src="/images/photos/small/{$photo.previd.file}"></a>
      {/if}
      </td>
      <td align="center" valign="top">
          {if $is_exists_original}
              <a href="/images/photos/{$photo.file}" class="photobox">
                  <img src="/images/photos/medium/{$photo.file}" alt="{$photo.title|escape:'html'}" id="view_photo" />
              </a>
          {else}
              <img src="/images/photos/medium/{$photo.file}" alt="{$photo.title|escape:'html'}" id="view_photo" />
          {/if}

      </td>
      <td width="150px" valign="middle" align="center">
      {if $photo.nextid}
      	<cite>{$LANG.NEXT}</cite><br>
        <a href="/clubs/photo{$photo.nextid.id}.html#main"><img alt="{$photo.nextid.title|escape:'html'}" src="/images/photos/small/{$photo.nextid.file}"></a>
      {/if}
      </td>
    </tr>
  </tbody>
</table>
{if $photo.karma_buttons}
	<div class="club_photo">{$photo.karma_buttons}</div>
{/if}
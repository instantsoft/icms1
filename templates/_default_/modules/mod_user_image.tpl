{foreach key=tid item=usr from=$users}
    <div align="center">
    <a href="/users/{$usr.uid}/photo{$usr.id}.html">
        <img src="/images/users/photos/small/{$usr.imageurl}" border="0"/>
    </a>
    </div>
    {if $cfg.showtitle}
        <div style="margin-top:5px" align="center"><strong>{$usr.title}</strong></div>
        <div align="center">{$usr.genderlink}</a></div>
    {/if}

{/foreach}
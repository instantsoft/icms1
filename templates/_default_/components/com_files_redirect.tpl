<!--noindex-->
{if !$is_domain_banned}
    <h1 class="con_heading"><i class="fa fa-external-link"></i>{$LANG.FILE_EXTERNAL_LINK}</h1>

    <p class="redirect">{$LANG.FILE_EXTERNAL_LINK_1|sprintf:$smarty.const.HOST:$sitename:$url:$url}</p>
    <p class="redirect">{$LANG.FILE_EXTERNAL_LINK_2|sprintf:$url}</p>
    <p class="redirect">
        {$LANG.FILE_EXTERNAL_LINK_3} <b><span id="timer"></span> {$LANG.DEBUG_SEC}</b>
    </p>
    <script type="text/javascript">
        $(function () {
            var timer    = $('#timer');
            var delay    = +{$time};
            var location = "{$url}";
            $(timer).html(delay);
            var interval = setInterval(function () {
                if(delay) { delay--; }
                $(timer).html(delay);
                if(delay <= 0){
                    clearInterval(interval);
                    window.location.href=location;
                }
            }, 1000);
        });
    </script>
{else}
    <h1 class="con_heading"><i class="fa fa-external-link"></i>{$LANG.FILE_SUSPICIOUS_LINK}</h1>
    <p class="redirect">{$LANG.FILE_SUSPICIOUS_LINK_1}</p>
{/if}
<p class="redirect color_asbestos">{$LANG.FILE_YOUR_SAFETY|sprintf:$sitename}</p>
<!--/noindex-->
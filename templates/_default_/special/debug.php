<?php $time = $inCore->getGenTime(); ?>
<div class="debug_info">
    <div class="debug_time">
        <?php echo $_LANG['DEBUG_TIME_GEN_PAGE'].' '.number_format($time, 4).' '.$_LANG['DEBUG_SEC']; ?>
    </div>
    <div class="debug_memory">
        <?php echo $_LANG['DEBUG_MEMORY'].' '.round(@memory_get_usage()/1024/1024, 2).' '.$_LANG['SIZE_MB']; ?>
    </div>
    <div class="debug_query_count">
        <a href="#debug_query_show" class="ajaxlink debug_query_dump"><?php echo $_LANG['DEBUG_QUERY_DB'].' '.$inDB->q_count; ?></a>
    </div>
    <div id="debug_query_dump">
        <div id="debug_query_show">
        <?php foreach($inDB->q_dump as $sql) { ?>
            <div class="query">
                <div class="src"><?php echo $sql['src']; ?></div>
                <?php echo nl2br($sql['sql']); ?>
                <div class="query_time"><?php echo $_LANG['DEBUG_QUERY_TIME']; ?> <span class="<?php echo (($sql['time']>=0.1) ? 'red_query' : 'green_query'); ?>"><?php echo number_format($sql['time'], 5).'</span> '.$_LANG['DEBUG_SEC'] ?></div>
            </div>
        <?php } ?>
        <div>
    </div>
</div>
<script>
    $(function(){
        $('.debug_query_dump').colorbox({inline:true, width:"70%", maxHeight: "100%", transition:"none"});
    });
</script>
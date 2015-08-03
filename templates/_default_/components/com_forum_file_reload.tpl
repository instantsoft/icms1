<form enctype="multipart/form-data" action="/forum/reloadfile{$file.id}.html" method="POST" id="reload_file">
    <input name="goreload" type="hidden" value="1" />
    <div style="margin: 10px 5px;">
        <div class="forum_fa_desc">
            <div><strong>{$LANG.MAX_SIZE_FILE}:</strong> {$cfg.fa_size} {$LANG.KBITE}.</div>
            <div><strong>{$LANG.MUST_FILE_TYPE}:</strong> {$cfg.fa_ext}</div>
        </div>
        <input type="file" name="fa[]" size="30" />
    </div>
</form>
<div class="sess_messages" style="display:none">
  <div class="message_info" id="error_mess"></div>
</div>
<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>

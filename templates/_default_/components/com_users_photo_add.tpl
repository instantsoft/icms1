<h1 class="con_heading">{$LANG.ADD_PHOTOS}</h1>
{if $total_no_pub}
<p class="usr_photos_add_limit">{$LANG.NO_PUBLISHED_PHOTO}: <a href="/users/{$user_login}/photos/submit">{$total_no_pub|spellcount:$LANG.PHOTO:$LANG.PHOTO2:$LANG.PHOTO10}</a></p>
{/if}
{if !$stop_photo}
	{if $uload_type == 'multi'}
{add_js file='includes/swfupload/swfupload.js'}
{add_js file='includes/swfupload/swfupload.queue.js'}
{add_js file='includes/swfupload/fileprogress.js'}
{add_js file='includes/swfupload/handlers.js'}
{add_css file='includes/swfupload/swfupload.css'}

<script type="text/javascript">
    var swfu;
    var uploadedCount = 0;

    window.onload = function() {
        var settings = {
            flash_url : "/includes/swfupload/swfupload.swf",
            upload_url: "/components/users/ajax/upload_photo.php",
            post_params: { "sess_id" : "{$sess_id}" },
            file_size_limit : "20 MB",
            file_types : "*.jpg;*.png;*.gif;*.jpeg;*.JPG;*.PNG;*.GIF;*.JPEG",
            file_types_description : "{$LANG.ALL_PHOTOS}",
            file_upload_limit : {if $max_limit}{$max_files}{else}100{/if},
            file_queue_limit : 0,
            custom_settings : {
                progressTarget : "fsUploadProgress",
                cancelButtonId : "btnCancel"
            },
            debug: false,
            // Button settings
            button_image_url: "/includes/swfupload/uploadbtn199x36.png",
            button_width: "199",
            button_height: "36",
            button_placeholder_id: "spanButtonPlaceHolder",
            // The event handler functions are defined in handlers.js
            file_queued_handler : fileQueued,
            file_queue_error_handler : fileQueueError,
            file_dialog_complete_handler : fileDialogComplete,
            upload_start_handler : uploadStart,
            upload_progress_handler : uploadProgress,
            upload_error_handler : uploadError,
            upload_success_handler : uploadSuccess,
            upload_complete_handler : uploadComplete,
            queue_complete_handler : queueComplete	// Queue plugin event
        };

        swfu = new SWFUpload(settings);
    };

    function queueComplete(numFilesUploaded) {
        if (numFilesUploaded>0){
            uploadedCount += numFilesUploaded;
            $('#divStatus').show();
            $('#continue').show();
            $("#files_count").html(uploadedCount);
        }
    }

</script>

<form id="usr_photos_upload_form" action="" method="post" enctype="multipart/form-data">

    {if $max_limit}
    <p class="usr_photos_add_limit">{$LANG.YOU_CAN_UPLOAD} <strong>{$max_files}</strong> {$LANG.PHOTO_SHORT}</p>
    {/if}

        <div class="fieldset flash" id="fsUploadProgress" style="display:none">
            <span class="legend">{$LANG.UPLOAD_QUEUE}</span>
        </div>

        <div>
            <span id="spanButtonPlaceHolder"></span>
            <input id="btnCancel" type="button" value="{$LANG.CANCEL}" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 36px;" />
        </div>

        <div id="divStatus" style="display:none">
            {$LANG.UPLOADED} <span id="files_count"><strong>0</strong></span> {$LANG.PHOTO_SHORT}.
            <a href="/users/{$user_login}/photos/submit" id="continue">{$LANG.CONTINUE}</a>
        </div>

</form>
        <p class="usr_photos_add_st">{$LANG.TEXT_TO_NO_FLASH} <a href="/users/addphotosingle.html">{$LANG.PHOTO_ST_UPLOAD}.</a></p>
    {elseif $uload_type == 'single'}
        {if $max_limit}
         <p class="usr_photos_add_limit">{$LANG.YOU_CAN_UPLOAD} <strong>{$max_files}</strong> {$LANG.PHOTO_SHORT}</p>
        {/if}

        <form id="usr_photos_upload_form" enctype="multipart/form-data" action="/users/photos/upload" method="POST">
            <p>{$LANG.SELECT_UPLOAD_FILE}: </p>
            <input name="Filedata" type="file" id="picture" size="30" />
            <input name="upload" type="hidden" value="1"/>
            <div style="margin-top:5px">
                <strong>{$LANG.TYPE_FILE}:</strong> gif, jpg, jpeg, png
            </div>

            <p>
                <input type="submit" value="{$LANG.UPLOAD}">
                <input type="button" onclick="window.history.go(-1);" value="{$LANG.CANCEL}"/>
            </p>
        </form>
		<p class="usr_photos_add_st">{$LANG.TEXT_TO_TO_FLASH} <a href="/users/addphoto.html">{$LANG.PHOTO_FL_UPLOAD}.</a></p>
    {/if}
{else}
<p class="usr_photos_add_limit">{$LANG.FOR_ADD_PHOTO_TEXT}</p>
{/if}
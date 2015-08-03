<?php if ($actions) { ?>
    <div class="actions_list">
        <?php foreach($actions as $action) { ?>
            <div class="action_entry act_<?php echo $action['name']; ?>">
                <div class="action_date
                    <?php if ($action['is_new']){ ?> is_new<?php } ?>"><?php echo $action['pubdate']; ?> <?php echo $_LANG['BACK']; ?>
                    <a href="#" class="action_delete uittip" title="<?php echo $_LANG['DELETE']; ?>" onclick="jsmsg('<?php echo $_LANG['AD_DELETE_ACTION']; ?>','/actions/delete/<?php echo $action['id']; ?>');return false;"></a>
                </div>
                <div class="action_title">
                    <a href="<?php echo $action['user_url']; ?>" class="action_user"><?php echo $action['user_nickname']; ?></a>
                    <?php if ($action['message']) { ?>
                        <?php echo $action['message']; ?><?php if ($action['description']) { ?>:<?php } ?>
                    <?php } else { ?>
                        <?php if ($action['description']){ ?>
                            &rarr; <?php echo $action['description']; ?>
                        <?php } ?>
                    <?php } ?>
                </div>
                <?php if ($action['message']) { ?>
                    <?php if ($action['description']) { ?>
                        <div class="action_details"><?php echo $action['description']; ?></div>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php
	if($pagebar) { echo $pagebar; }

} else { echo $_LANG['OBJECTS_NOT_FOUND']; }

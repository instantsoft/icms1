<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $_LANG['BAN_TITLE']; ?></title>
        <meta http-equiv="refresh" content="60;URL=/">
        <style type="text/css">
            * { font-family: Arial; }
            html, body { height:100%; margin:0px; background: #2F4F7D; }
            h2 { color: red; margin:0px; }
            p { margin:0px; margin-top:10px; font-size:14px; color: #FFF; }
        </style>
    </head>
    <body>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
            <tr>
                <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0" >
                        <tr>
                            <td width="160">
                                <img src="/templates/<?php echo TEMPLATE; ?>/special/images/banned.png" />
                            </td>
                            <td>
                                <h2><?php echo $_LANG['BAN_TITLE']; ?></h2>
                                <div style="padding:15px 0;">
                                    <p><strong><?php echo $_LANG['BAN_LOCK_DATE']; ?>:</strong> <?php echo $ban['bandate'] ?></p>
                                    <?php if ($ban['int_num']<=0){ ?>
                                        <p><strong><?php echo $_LANG['BAN_PERIOD_LOCK']; ?>:</strong> <?php echo $_LANG['BAN_INFINITE']; ?></p>
                                    <?php } else { ?>
                                        <p><strong><?php echo $_LANG['BAN_PERIOD_LOCK']; ?>:</strong> <?php echo $ban['enddate'] ?></p>
                                    <?php } ?>
                                    <?php if ($ban['cause']){ ?>
                                        <p><strong><?php echo $_LANG['BAN_REASON_LOCK']; ?>:</strong></p><p><?php echo nl2br($ban['cause']); ?></p>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>

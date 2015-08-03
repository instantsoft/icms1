<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $_LANG['SITE_IS_OFF']; ?></title>
        <meta http-equiv="refresh" content="25;URL=/">
        <style type="text/css">
            * { font-family: Arial; }
            html, body { height:100%; margin:0px; background: #2F4F7D; }
            h2 { color: #F5C347; margin:0px; }
            p { margin:0px; margin-top:10px; font-size:16px; color: #FFF; }
        </style>
    </head>
    <body>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
            <tr>
                <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0" >
                        <tr>
                            <td width="150">
                                <img src="/templates/<?php echo TEMPLATE; ?>/special/images/siteoff.png" />
                            </td>
                            <td>
                                <h2><?php echo $_LANG['SITE_IS_OFF']; ?></h2>
                                <p><?php echo cmsConfig::getConfig('offtext'); ?></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>

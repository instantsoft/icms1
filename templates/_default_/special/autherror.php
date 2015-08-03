<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $_LANG['AUTHORIZATION_ERROR']; ?></title>
        <meta http-equiv="refresh" content="5;URL=/login">
        <style type="text/css">
            * { font-family: Arial; }
            html, body { height:100%; margin:0px; background: #2F4F7D; }
            h2 { color: red; margin:0px; }
            p { margin:0px; margin-top:10px; font-size:14px; color: #FFF; }
            a { color: #FFF; }
        </style>
    </head>
    <body>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
            <tr>
                <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0" >
                        <tr>
                            <td width="150">
                                <img src="/templates/<?php echo TEMPLATE; ?>/special/images/autherror.png" />
                            </td>
                            <td>
                                <h2><?php echo $_LANG['AUTHORIZATION_ERROR']; ?></h2>
                                <p><?php echo $_LANG['CHECK_LOGIN_PASS']; ?></p>
                                <p><?php echo $_LANG['YOU_WILL_BE_REDIRECTED']; ?> <a href="/login"><?php echo $_LANG['BACK']; ?></a></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>

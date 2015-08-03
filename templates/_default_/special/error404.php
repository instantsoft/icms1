<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $_LANG['404']; ?></title>
        <style type="text/css">
            * { font-family: Arial; }
            html, body { height:100%; margin:0px; }
            h2, p { margin:0px; }
            .ajaxlink{ text-decoration:none; border-bottom:dashed 1px #AAA; color:#AAA; }
            ul { list-style: none; margin: 10px; padding: 0; }
        </style>
    </head>
    <body>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
            <tr>
                <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0" >
                        <tr>
                            <td width="140">
                                <img src="/templates/<?php echo TEMPLATE; ?>/special/images/error404.png" />
                            </td>
                            <td>
                                <h2><?php echo $_LANG['404']; ?></h2>
                                <p><?php echo $_LANG['404_INFO']; ?>.</p>
                                <?php if(cmsConfig::getConfig('debug')){ ?>
                                    <p><a href="#trace_stack" class="ajaxlink trace_stack"><?php echo $_LANG['TRACE_STACK']; ?></a></p>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php if(cmsConfig::getConfig('debug')){ ?>
        <div style="display: none">
            <ul id="trace_stack">

                <?php $stack = debug_backtrace(); ?>

                <?php for($i=2; $i<=14; $i++){ ?>

                    <?php if (!isset($stack[$i])){ break; } ?>

                    <?php $row = $stack[$i]; ?>
                    <li>
                        <b><?php echo $row['function']; ?>()</b>
                        <?php if (isset($row['file'])) { ?>
                            <span>@ <?php echo str_replace(PATH, '', $row['file']); ?></span> => <span><?php echo $row['line']; ?></span>
                        <?php } ?>
                    </li>

                <?php } ?>

            </ul>
        </div>
        <script type="text/javascript" src="/includes/jquery/jquery.js"></script>
        <script type="text/javascript" src="/includes/jquery/colorbox/jquery.colorbox.js"></script>
        <link href="/includes/jquery/colorbox/colorbox.css" rel="stylesheet" type="text/css" />
        <script>
            $(function(){
                $('.trace_stack').colorbox({inline:true, width:"50%", maxHeight: "100%", transition:"none"});
            });
        </script>
        <?php } ?>
    </body>
</html>
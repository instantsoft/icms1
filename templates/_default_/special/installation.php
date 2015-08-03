<?php cmsCore::loadLanguage('install'); ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="10;URL=/">
    <title><?php echo $_LANG['INS_HEADER']; ?></title>
    <style>
    body{
        font-family:Arial, Helvetica, sans-serif;
        font-size:12px;
        background-color:#EBEBEB;
    }
    .center {
        background-color: #183152;
        border: 1px solid #183152;
        border-radius: 6px 6px 6px 6px;
        color: #FFFFFF;
        padding: 20px;
        text-align: center;
        text-shadow: 0 1px 1px #224674;
        box-shadow: 1px 1px 6px #183152;
        width: 50%;
    }
    a {
        color: #FFF;
    }
    </style>
</head>
<body>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" valign="middle">
        <div class="center">
			<h2 style="color:red"><?php echo $_LANG['INS_INCOMPLETE']; ?></h2>

			<div style="margin-top:21px;font-weight:bold"><?php echo $_LANG['INS_DELETE_INST_MIGRATE']; ?></div>

			<div style="margin-top:21px;"><a href="/"><?php echo $_LANG['INS_RELOAD_PAGE']; ?></a></div>
		</div>
    </td>
  </tr>
</table>
</body></html>
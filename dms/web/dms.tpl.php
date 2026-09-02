<?php declare(strict_types=1);
ob_start();
$COOKIE_NAME = 'ALVEST_STAGING';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title><?php echo 'TLD - '._('Document Management System'); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_CHARSET; ?>">
    <link href="dms.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/shared/javascript/overlib/overlib.js"></script>
    <script type="text/javascript" src="/shared/javascript/tld/tld.table.sorttable.js"></script>
    <script type="text/javascript" src="/shared/javascript/tld/qfamsHandler-min.js"></script>
    <script src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
  </head>
  <body>
    <br/>
    <?php if (isset($_COOKIE[$COOKIE_NAME])) { ?>
        <div class="staging-warning navbar navbar-default navbar-fixed-top">
            You are on a test environment: the data you see here might be outdated and your actions will not have any impact on the production data.<a href="<?php echo $php_self; ?>?m[0]=switchstaging" name="staging link">Click here to go back to the Prod environment</a>
        </div>
    <?php } ?>
    <table width="1024" border="0" cellpadding="0" cellspacing="0" padding="20" align="center">
<!-- HEADER BANNER -->
	  <tr>
		<td bgcolor="white">
		  <table cellpadding="10" width="100%">
		    <tr>
		      <td align="left" width="100"><img src="/shared/logos/alvest_logo.jpg" width="200" height="60" /></td>
		      <td align="center"><span style="font:38px bold;"><?php echo _('Document Management System'); ?></span></td>
		    </tr>
		  </table>
		</td>
	  </tr>
<!-- HEADER GREY BAR -->
	  <tr>
		<td bgcolor="#D0D0D0">
          <!-- LANGUAGE ITEM LINK -->
		  <div id="menu_lang">
		    <a href="<?php echo "$php_self?lang=en"; ?>" style="<?php if ('en' == $_LANG) {
		        echo 'text-decoration:underline;';
		    } ?>" title="Switch to English">
		      [English]
	        </a>
		    <a href="<?php echo "$php_self?lang=fr"; ?>" style="<?php if ('fr' == $_LANG) {
		        echo 'text-decoration:underline;';
		    } ?>" title="Site en Fran&ccedil;ais">
	          [Fran&ccedil;ais]
            </a>
		    <a href="<?php echo "$php_self?lang=zh"; ?>" style="<?php if ('zh' == $_LANG) {
		        echo 'text-decoration:underline;';
		    } ?>" title="&#20013;&#25991;">
		      <span style="font-family:SimSun" lang="ZH-CN">
              [&#20013;&#25991;]
              </span>
		    </a>
		  </div>
		</td>
	  </tr>
<!-- HEADER Login info -->
	  <tr>
	    <td bgcolor="white" align="right" style="font:12px;color:grey;">
          <?php if (is_a($user, 'tldUser') && !empty($user)) { ?>
          <span>
            <?php echo sprintf(_('Logged in as %s'), $user->getFullname()); ?> |

              <?php if (array_search('GG_STAGING', array_column($user->getUserGroups(), 'group_name'), true) && (!isset($_COOKIE[$COOKIE_NAME]))) { ?>
                  <a href="<?php echo "$php_self?m[0]=switchstaging"; ?>"><?php echo _('Go to test environment'); ?></a> |
              <?php } ?>
            <a href="<?php echo "$php_self?m[0]=logout&redirect=1"; ?>"><?php echo _('Go to'); ?> Intranet</a> |
            <a href="<?php echo "$php_self?m[0]=logout"; ?>"><?php echo _('Log out'); ?></a>
          </span>
          <?php } else { ?>
            <form method="post" action="<?php echo "$php_self?m[0]=login"; ?>">
              <?php echo _('Username'); ?>: <input type="text" name="login" width="6" />
              <?php echo _('Password'); ?>: <input type="password" name="pass" width="6" />
              <input type="submit" value="<?php echo _('Login'); ?>"/>
            </form>
          <?php } ?>
        </td>
	  </tr>
<!-- HEADER FLASH MESSAGES BAR -->
        <tr bgcolor="#FFFFFF">
            <?php
		                                        foreach ($app->session->getFlashBag()->all() as $type => $messages) {
		                                            foreach ($messages as $message) {
		                                                ?>
                    <td>
                        <div class="<?php echo $type; ?>">
                            <?php echo $message; ?>
                        </div>
                    </td>
                    <?php
		                                            }
		                                        }
?>
        </tr>
<!-- MAIN BODY APPLICATION -->
	  <tr>
		<td bgcolor="#FFFFFF">
		  <div id="index_body">
            <!-- TITLE -->
            <?php if (null != $user) { ?>
            <div id="index_title">
              <p><?php echo $_TITLE; ?></p>
            </div>
            <!-- MENU-->
            <div id="index_menu">
              <p><?php echo $_MENU; ?></p>
            </div>
            <?php } ?>
            <div id="body">
	          <!-- CONF & NOTICE & ERROR MESSAGE -->
	          <?php if (!empty($_CONF)) { ?><p class="conf"><?php echo _('SUCCESS').": $_CONF"; ?></p><?php } ?>
		      <?php if (!empty($_NOTE)) { ?><p class="note"><?php echo _('NOTE').": $_NOTE"; ?></p><?php } ?>
	          <?php if (!empty($_WARNING)) { ?><p class="warning"><?php echo _('WARNING').": $_WARNING"; ?></p><?php } ?>
	          <?php if (!empty($_ERROR)) { ?><p class="alert"><?php echo _('ERROR').": $_ERROR"; ?></p><?php } ?>
		      <br/>
		      <!-- BODY -->
		      <?php if (null != $user) { ?>
		      <?php echo $_BODY; ?>
		      <?php } ?>
		    </div>
		  </div>
		</td>
	  </tr>
<!-- FOOTER -->
	  <tr>
		<td>
		  <p align="center">Copyright 2021 TLD - All Rights Reserved | <a href="http://www.tld-gse.com">http://www.tld-gse.com</a></p>
		</td>
	  </tr>
	</table>
  </body>
</html>
<?php
$_BUFF = ob_get_contents();
ob_end_clean();

return $_BUFF;
?>

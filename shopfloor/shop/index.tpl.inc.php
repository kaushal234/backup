<?php
include_once("common.inc.php");
ob_start();
?>

<?php if (null !== $error = ($_SESSION['error'] ?? null)): ?>
    <p style="color: red"><strong><?= $error ?></strong></p>
    <?php $_SESSION['error'] = null; ?>
<?php endif ?>

<table width="100%" border="0">
    <tr>
        <td colspan="6"><h3><?= _("Reference") ?></h3></td>
    </tr>
    <tr>
        <td align="center">
            <a href="<?= $php_self ?>?m[0]=er">
                <img src="/shared/bluesphere/48x48/apps/tools.png"><br>
                <h3><?= _("ER") ?></h3>
            </a>
        </td>
        <td align="center">
            <a href="<?= $php_self ?>?m[0]=cbom">
                <img src="/shared/bluesphere/48x48/apps/tux_config.png"><br>
                <h3><?= _("Customized BOM") ?></h3>
            </a>
        </td>
        <td align="center">
            <a href="<?= $php_self ?>?m[0]=dwg">
                <img src="/shared/bluesphere/48x48/apps/snavigator.png"><br>
                <h3><?= _("View Drawings") ?></h3>
            </a>
        </td>
        <?php if (in_array($ERP, array(250, 300, 400, 410, 420, 430))): ?>

            <td align="center">
                <a href="https://chemmanagement.ehs.com/9/930a0026-4ddf-4a80-b4fd-c9dac5d768c0/ebinder" target="_blank">
                    <img src="/shared/thirdparty/MSDSonlineLogo.gif"><br>
                    <h3>MSDS <?= _("Online") ?></h3>
                </a>
            </td>
        <?php endif; ?>
    </tr>
    <tr>
        <td colspan="6">
            <hr>
            <h3><?= _("Forms") ?></h3></td>
    </tr>
    <tr>
        <td align="center">
            <a href="<?= $php_self ?>?m[0]=ncr&m[1]=forms&m[2]=newNCR">
                <img src="/shared/bluesphere/48x48/apps/klipper.png"><br>
                <h3><?= _("Submit") ?> NCR</h3>
            </a>
        </td>
        <td align="center">
            <a href="<?= $php_self ?>?m[0]=er">
                <img src="/shared/bluesphere/48x48/apps/klipper.png"><br>
                <h3><?= _("Submit") ?> CRAB</h3>
            </a>
        </td>
        <td align="center">

            <?php if (in_array($ERP, [220, 250, 400, 410, 420, 430, 500, 510, 520, 540, 570, 640, 660, 820, 900])): ?>
                <a href="/shop/autoselect.php?m[0]=pi">
                    <img src="//www.tld-gse.com/shared/icons/application/PI.PNG"><br>
                    <h3>Process & Inspection</h3></a>

            <?php endif; ?>

        </td>
        <td align="center">

        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="6">
            <hr>
            <h3><?= _("Ethics") ?></h3>
        </td>
    </tr>
    <tr>
        <td align="center">
            <a href="<?= $SHOPFLOOR_URL ?>/index.php?m[0]=dms&id=<?= in_array($ERP, array(420, 500, 520)) ? '238' : '236' ?>"
               target="_blank">
                <img src="/shared/icons/pdf_icon.png" width="48px"><br>
                <h3><?= _("Code of Ethics") ?></h3>
        </td>
        <td align="center">
            <a href="<?= $SHOPFLOOR_URL ?>/index.php?m[0]=dms&id=<?= in_array($ERP, array(420, 500, 520)) ? '3066' : '3054' ?>"
               target="_blank">
                <img src="/shared/icons/pdf_icon.png" width="48px"><br>
                <h3><?= _("Speak Up Policy") ?></h3>
            </a>
        </td>
        <td align="center">
            <?php
            $dmsId = null;
            switch ($ERP) {
                case 250:
                    $dmsId = 3079;
                    break;
                case 400:
                case 410:
                    $dmsId = 3083;
                    break;
                case 420:
                    $dmsId = 3080;
                    break;
                case 500:
                case 510:
                case 520:
                case 530:
                case 540:
                    $dmsId = 3082;
                    break;
                case 640:
                case 660:
                    $dmsId = null;
                    break;
            }
            ?>
            <?php if ($dmsId !== null): ?>
                <a href="<?= $SHOPFLOOR_URL ?>/index.php?m[0]=dms&id=<?= $dmsId ?>" target="_blank">
                    <img src="/shared/icons/pdf_icon.png" width="48px"><br>
                    <h3><?= _("Speak Up Poster") ?></h3>
                </a>
            <?php endif; ?>
        </td>
        <td colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="6">
            <hr>
            <h3><?= _("Others") ?></h3></td>
    </tr>
    <tr>
        <td align="center">
            <a href="<?= $php_self ?>?m[0]=dms">
                <img src="/shared/bluesphere/48x48/filesystems/folder.png"><br>
                <h3><?= $translator->trans('home.title.dms_shopfloor') ?></h3>
            </a>
        </td>
        <td align="center">
            <a href="https://outlook.office365.com/mail" target="_blank">
                <img src="/shared/bluesphere/48x48/apps/korn.png"><br>
                <h3><?= $translator->trans('home.title.webmail') ?></h3>
            </a>
        </td>
        <td align="center">
            <a href="https://www.tld-gse.com/en/private" target="_blank">
                <img src="/shared/bluesphere/64x64/apps/ALVEST_logo.png"><br>
                <h3><?= _("Intranet") ?></h3>
            </a>
        </td>
        <td align="center">
            <a href="https://alvest.learn.link/" target="_blank">
                <img src="/shared/images/logo-agile-transparent.png" height="48px"><br>
                <h3><?= _("Agile") ?></h3>
            </a>
        </td>
        <?php if (in_array($ERP, [400, 410])): ?>
            <td align="center">
                <a href="https://www.theapplicantmanager.com/careers?co=td" target="_blank">
                    <img src="/shared/bluesphere/64x64/apps/careers.png"><br>
                    <h3><?= _("Career Page") ?></h3>
                </a>
            </td>
        <?php endif; ?>
        <?php if (in_array($ERP, array(400))): ?>
            <td align="center">
                <form method="post">
                    <input type="hidden" name="changeLocation" value="52">
                    <button type="submit" name="submit_param"
                            style="background:none;border:none;padding:0;cursor:pointer;outline:none;"
                            onmouseover='this.style.textDecoration="underline"'
                            onmouseout='this.style.textDecoration="none"'>
                        <img src="/shared/bluesphere/48x48/actions/desktop.png"><br>
                        <h3><?= _("TLD WIM") ?></h3>
                    </button>
                </form>
            </td>
        <?php endif; ?>
        <?php if (in_array($ERP, array(410))): ?>
            <td align="center">
                <form method="post">
                    <input type="hidden" name="changeLocation" value="17">
                    <button type="submit" name="submit_param"
                            style="background:none;border:none;padding:0;cursor:pointer;outline:none;"
                            onmouseover='this.style.textDecoration="underline"'
                            onmouseout='this.style.textDecoration="none"'>
                        <img src="/shared/bluesphere/48x48/actions/desktop.png"><br>
                        <h3><?= _("TLD WIN") ?></h3>
                    </button>
                </form>
            </td>
        <?php endif; ?>
        <?php if (in_array($ERP, array(220, 900, 500, 510, 520, 540))): ?>
            <td align="center">
                <a href="https://alvest.kelio.io/" target="_blank">
                    <img src="/shared/thirdparty/kelio.jpg" width="48px"><br>
                    <h3>Kelio</h3>
                </a>
            </td>
            <td align="center" width="150px">
                <a href="https://tld-group.javelo.io/auth/login/signin" target="_blank">
                    <img src="/shared/images/logo-javelo.png" alt="Javelo" title="Javelo" width="48px"><br>
                    <h3>Javelo</h3>
                </a>
            </td>
            <td align="center">
                <a href="help/AEITbadgeage.pdf" target="_blank">
                    <img src="/shared/icons/pdf_icon.png" width="48px"><br>
                    <h3>Procedure badgeage</h3>
                </a>
            </td>
            <td>&nbsp;</td>
        <?php else: ?>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        <?php endif; ?>
        <td>&nbsp;</td>
    </tr>
    <?php if (in_array($ERP, array(400, 410))): ?>
        <tr>
            <td colspan="6">
                <hr>
                <h3>Links</h3></td>
        </tr>

        <tr>
            <td align="center">
                <a href="http://www.paychexflex.com" target="_blank">
                    <img src="/shared/bluesphere/64x64/apps/payroll.png"><br>
                    <h3><?= _("Payroll") ?></h3>
                </a>
            </td>
            <td align="center">
                <a href="http://www.mycigna.com" target="_blank">
                    <img src="/shared/bluesphere/64x64/apps/medicine.png"><br>
                    <h3><?= _("Medical Insurance") ?></h3>
                </a>
            </td>
            <td align="center">
                <a href="https://www.guardianlife.com" target="_blank">
                    <img src="/shared/bluesphere/64x64/apps/insurance.png"><br>
                    <h3><?= _("Other Health Insurance") ?></h3>
                </a>
            </td>
            <td align="center">
                <a href="https://www.retiresmart.com" target="_blank">
                    <img src="/shared/bluesphere/64x64/apps/retirement.png"><br>
                    <h3><?= _("401(K)") ?></h3>
                </a>
            </td>
            <td align="center">
                <a href="https://www.grainger.com/benefits/registration/TLDAceCorpWindsorCT" target="_blank">
                    <img src="/shared/bluesphere/64x64/apps/footwear.png"><br>
                    <h3><?= _("Footwear Program") ?></h3>
                </a>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <td colspan="6">
            <h3>
                <hr><?= _("TLD Internal Portal") ?></h3>
        </td>
    </tr>
    <tr>
        <td align="center">
            <a href="<?= $DMS_URL ?>">
                <img src="/shared/bluesphere/32x32/filesystems/folder_blue_open.png" alt="TLD Document Managment System"
                     width="32px"/><br>
                <h3>TLD DMS<br>(Document Management System)</h3>
            </a>
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>

<p>
    <?= sprintf(_("Your IP address is %s"), tldUtils::getClientIp()); ?>
</p>

<?php
return ob_get_clean();
?>

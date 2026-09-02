<?php
ob_start();
?>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">

<?php
$body = include("$PATH/header.pi.tpl.php");
?>

    <table border=0 width=100%>
        <thead>
        <tr style="background:#2971a8; color:white;">
            <td width=15% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
                <b><?= _('Task') ?></b>
            </td>
            <td width=75% style="text-align: center; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
                <b><?= _('Description') ?></b>
            </td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tasks as $key => $task): ?>
            <?php if ((string)$task['code'] === (string)$_SESSION['pi_indirect']) : ?>
                <tr style="background:#FFFF80;">
            <?php else : ?>
                <tr style="background:<?=  (($key % 2) ? '#eeeeee' : '#d0d0d0')  ?>">
            <?php endif ?>
            <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
                <a href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=indirectTransact&m[3]=punchOut&task=<?= $task['code'] ?>&taskdesc=<?= $task['description'] ?>">
                    <?= $task['code'] ?>
                </a>
            </td>
            <td style="height: 40px; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
                <?= $task['description'] ?>
                <?php if ((string) $task['code'] === (string)$_SESSION['pi_indirect']) : ?>
                    &nbsp;&nbsp;(<?= $_SESSION['pi_indirect_start'] ?>)
                <?php endif ?>
            </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

<?php

return ob_get_clean();

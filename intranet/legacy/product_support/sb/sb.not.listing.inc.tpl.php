<?php
ob_start();

// Get all notes
$nots = $line->getNOT();
?>

    <h3>SB#<?= $id ?> Notifications for ER#<?= $line->getERSN() ?></h3>

    <table cellpadding="2" class="tld_table">
        <thead>
        <tr bgcolor="#2971A8">
            <th align="center">#</th>
            <th align="center">Date</th>
            <th align="center">From</th>
            <th align="center">Notification</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($nots as $k => $not):
            $k++;
            $bgColor = ($k % 2) ? "#dedede" : "#efefef";
            ?>
            <tr bgcolor="<?= $bgColor ?>">
                <td><?= $k ?></td>
                <td><?= $not['dt'] ?></td>
                <td><?= $not['poster_email'] ?></td>
                <td>
                    <p><b><?= $not['subject'] ?></b></p>
                    <p><?= $not['email'] ?></p>
                    <hr>
                    <p style="font-size: 0.9em; color: grey;">
                        <b>Sent to:</b> <?= $not['recipients'] ?>
                        <br><br><b>Copy:</b> <?= $not['cc'] ?>
                        <br><br><b>Hidden Copy:</b> <?= $not['bcc'] ?>
                    </p>
                    <p>
                        <a href="<?= "$php_self?m[0]=sb&m[1]=view&m[2]=lines&m[3]=NOT&m[4]=getEmailPDF&id=$id&lid=$lid&notid={$not['id']}" ?>">
                            <img src="/shared/icons/pdf-icon.gif" alt="PDF"/>
                            Click here to get the complete email content in PDF
                        </a>
                    </p>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php
return ob_get_clean();

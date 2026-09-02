<?php
include_once("common.inc.php");
$user = new tldUser($_SERVER["PHP_AUTH_USER"]);
include_once("calendar.inc.php");

?>
<div class="row">
    <div class="col-lg-9">
        <h3 class="text-center">Legacy Tasks</h3>
        <div class="table-responsive">
            <table class="table table-bordered matrix-table">
                <tbody>
                <tr>
                    <th></th>
                    <th class="top-label">LATE</th>
                    <th class="top-label">DUE</th>
                    <th class="top-label th-total">Totals</th>
                </tr>
                <?php
                $tasks = tldTask::countByModuleNotMigrated($user->getID());

                $xItems = ['LATE', 'DUE'];
                $yItems = [];
                $xField = "overdue";
                $yField = "module";
                $cellField = "num";

                $columnsTotals = array_reduce($tasks, function(array $memo, array $task) {
                    $memo[$task['overdue']] += $task['num'];
                    return $memo;
                }, ['LATE' => 0, 'DUE' => 0]);

                foreach ($tasks as $row) {
                    if (!in_array($row[$xField], $xItems)) {
                        $xItems[] = $row[$xField];
                    }

                    if (!in_array($row[$yField], $yItems)) {
                        $yItems[] = $row[$yField];
                    }

                    $data[$row[$xField]][$row[$yField]] = number_format($row[$cellField]);
                    $totals[$row[$yField]]["Total"] += $row[$cellField];
                }
                foreach ($yItems as $yItem) : ?>
                    <tr>
                        <th><?= $yItem ?></th>
                        <?php foreach ($xItems as $xItem) : ?>
                            <?php

                            $urlWithDate = "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byModule&module=$yItem&category=$xItem";
                            $url = "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byModule&module=$yItem";
                            ?>
                            <td>
                                <a href="<?= $urlWithDate ?>">
                                    <?= $data[$xItem][$yItem] ?></a>
                            </td>
                        <?php endforeach; ?>
                        <td class="total">
                            <a href="<?= $url ?>">
                                <?= $totals[$yItem]["Total"] ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tfoot>

                    <tr>
                        <th class="th-total">Totals</th>

                        <td class="total"><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byModulesNotMigrated&category=LATE"><?= $columnsTotals['LATE'] ?></a></td>
                        <td class="total"><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byModulesNotMigrated&category=DUE"><?= $columnsTotals['DUE'] ?></a></td>
                        <td class="total"><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byModulesNotMigrated"><?= $columnsTotals['DUE'] + $columnsTotals['LATE']?></a></td>
                </tr>
                </tfoot>
                </tbody>
            </table>
        </div>
        <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList" class="btn btn-success btn-xs">Tasks List</a>
        <?php $seq = tldSEQ::getUserQuickEditableSeqSummary($user->getID()); ?>
        <?php if (count($seq) > 0) : ?>
            <h3 class="text-center">Your Quick Editable Sequences</h3>
            <div class="table-responsive">
                <table class="table table-bordered matrix-table">
                    <tbody>
                    <tr>
                        <th></th>
                        <th class="top-label">LATE</th>
                        <th class="top-label">DUE</th>
                        <th class="top-label th-total">Totals</th>
                    </tr>

                    <?php
                    $xItems = ['LATE', 'DUE'];
                    $yItems = [];
                    $xField = "overdue";
                    $yField = "short_name";
                    $cellField = "seq_number";
                    $yFieldLink = 'id';

                    foreach ($seq as $row) {
                        if (!in_array($row[$xField], $xItems)) {
                            $xItems[] = $row[$xField];
                        }

                        if (!in_array($row[$yField], $yItems)) {
                            $yItems[] = $row[$yField];
                            $yItemsLinks[$row[$yField]] = $row[$yFieldLink];
                        }

                        $data[$row[$xField]][$row[$yField]] = number_format($row[$cellField]);
                        $totals[$row[$yField]]["Total"] += $row[$cellField];
                    }
                    foreach ($yItems as $yItem) : ?>
                        <tr>
                            <th><?= $yItem ?></th>
                            <?php foreach ($xItems as $xItem) : ?>
                                <td>
                                    <a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=form&m[2]=quickedit&x=<?= $xItem ?>&y=<?= $yItemsLinks[$yItem] ?>">
                                        <?= $data[$xItem][$yItem] ?></a>
                                </td>
                            <?php endforeach; ?>
                            <td class="total">
                                <a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=form&m[2]=quickedit&y=<?= $yItemsLinks[$yItem] ?>">
                                    <?= $totals[$yItem]["Total"] ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

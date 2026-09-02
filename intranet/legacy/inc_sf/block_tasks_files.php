<?php
include_once("common.inc.php");
include_once("calendar.inc.php");

$id = $_GET['id'];
$files = tldTask::byFileParent($id, $_GET['module'], 'ALL');
?>

<?php if ($files): ?>
    <div class="table-responsive">
        <table class="footable table table-hover toggle-arrow-tiny report-table">
            <thead>
                <tr>
                    <th>Files</th>
                    <th>Poster</th>
                    <th>Created at</th>
                    <th>Task</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($files as $key => $file): ?>
                <tr>
                    <td><a href="/en/private/uploads/tasks_comments/<?= $file['filename'] ?>"><?= $file['filename'] ?></a></td>
                    <td><a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=/<?= $file['poster'] ?>"><?= $file['poster_fullname'] ?></a></td>
                    <td><?= $file['date'] ?></td>
                    <td><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=/<?= $file['parent_id'] ?>"><?= $file['parent_id'] ?></a></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div>
        <h3>No records...</h3>
    </div>
<?php endif; ?>

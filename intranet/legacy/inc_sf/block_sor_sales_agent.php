<?php
include_once("sales_service.inc.php");

// SOL module
$id = $_GET['id'];
$sor = new tldSOR($id);

?>

<?php if (!$sor->isEmpty() && !empty(trim($sor->itsHeader['agnt_nama']))): ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover association-table">
                            <tbody>
                                <th>Sales agent</th>
                                <td><?= mb_convert_encoding($sor->itsHeader['agnt_nama'] , 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8') ?></td>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div>
<?php
    foreach ($session->getFlashBag()->all() as $type => $messages) {
        foreach ($messages as $message) {
?>
            <div class="alert alert-<?php echo $type; ?>">
                <?php echo $message ?>
            </div>
<?php
        }
    }
?>
</div>

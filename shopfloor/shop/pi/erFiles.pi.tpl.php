<?php
ob_start();
?>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">

<script type="text/javascript">
    $(document).ready(function() {
        $('form#erFile').on('submit', function (e) {
            $('input[type=file]').each(function(index) {
                $description = $('textarea[name="description['+index+']"]')
                var description = $description.val().trim();
                if ('' !== $(this).val() && '' === description) {
                    $description.closest('tr').after('<tr><td></td><td style="color: red">File description is mandatory</td></tr>')
                    e.preventDefault()
                }
            })
        })
    })
</script>
<?php
$body = include("$PATH/header.pi.tpl.php");
?>

<?php
return ob_get_clean();
?>

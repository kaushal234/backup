<div class="container">
    <div class="list-group">
        <label for="searchFilter">
            <input type="text" name="searchFilter" id="searchFilter" placeholder="Search...">
        </label>
        <div id="searchFilterContent">
            <?php foreach ($activeCustomerList as $id=>$customer): ?>
                <div id="<?= strtolower($customer) ?>">
                    <form method="post" class="clickable-link" action="<?= "$PHPSELF?page=customers&action=view" ?>">
                        <input type="hidden" name="customer_id" value="<?= $id ?>">
                        <input type="hidden" name="asm_id" value="<?= $ASM_ID ?>">
                        <a href="#" class="list-group-item submit-form">
                            <span class="float-end glyphicon glyphicon-arrow-right"></span><?= $customer ?>
                        </a>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

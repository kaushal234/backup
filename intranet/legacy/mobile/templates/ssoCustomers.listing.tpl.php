<div class="container">
    <div class="list-group">
        <label for="searchFilter">
            <input type="text" name="searchFilter" id="searchFilter" placeholder="Search...">
        </label>
        <div id="searchFilterContent">
            <?php foreach ($activeCustomerList as $activeCustomer): ?>
                <div id="<?= strtolower($activeCustomer['name']) ?>">
                    <form method="post" class="clickable-link" action="<?= "$PHPSELF?page=customers&action=listActivesForCustomer" ?>">
                        <input type="hidden" name="customer_id" value="<?= $activeCustomer['id'] ?>">
                        <input type="hidden" name="customer_legacy_id" value="<?= $activeCustomer['legacyId'] ?>">
                        <input type="hidden" name="sso_legacy_id" value="<?= $ssoLegacyId ?>">
                        <a href="#" class="list-group-item submit-form">
                            <span class="pull-right glyphicon glyphicon-arrow-right"></span><?= $activeCustomer['name'] ?>
                        </a>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

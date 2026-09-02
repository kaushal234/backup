<?php
        if($byregion) {
            $bu = $user->getBUID();
            $form = new HTML_QuickForm('FrmInventory');
            $form->addElement('header', 'title', 'Filter by BU');
            $form->addElement('hidden', 'm[0]', 'inventory');
            $form->addElement('hidden', 'byregion', '1');
	    $form->addElement('hidden', 'display', '1');
            $form->addElement('select', 'bu', 'Location',
                tldLocation::getERPList("smartyOptionsIDLocation"));
            $form->addElement('submit', 'btnSubmit', 'Submit');
            $body .= $form->toHTML();

            if ($form->validate() && !empty($bu)) {
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $bu = $vars['bu'];
                $bu_title = " - " . tldLocation::getLocationByID($bu);
            }
            $body .=<<<EOF
<a href="$php_self?m[0]=inventory&byregion=0&display=1">Show Matrix Report By Region</a>
EOF;
            $STATS = Tld_Mis_Inventory_Item::itemByDeptByType($bu);
            $report = new tldMatrix(
                $STATS,
                "buyer_dpt", "type_name", "qty",
                "$php_self?m[0]=inventory&m[1]=reports&m[2]=byDeptByType&z=HARDWARE&bu=$bu$extra_url_inv",
                "Inventory item by BU by item type $bu_title"
            );
            $body .= $report->fetch();
        }else{
            $bu = $user->getBUID();
            $region = tldLocation::getRegionByBUID($bu);
            $form = new HTML_QuickForm('FrmInventory');
            $form->addElement('header', 'title', 'Filter by Region');
            $form->addElement('hidden', 'm[0]', 'inventory');
            $form->addElement('hidden', 'byregion', '0');
	    $form->addElement('hidden', 'display', '1');
            $form->addElement('select', 'region', 'Region',
                tldLocation::getRegionList());
            $form->addElement('submit', 'btnSubmit', 'Submit');
            $body .= $form->toHTML();

            if ($form->validate() && !empty($bu)) {
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $region = $vars['region'];
                $bu_title = " - " . $region;
            }
            $body .=<<<EOF
<a href="$php_self?m[0]=inventory&byregion=1&display=1">Show Matrix Report By BU</a>
EOF;
            $STATS = Tld_Mis_Inventory_Item::itemByRegionByDeptByType($region);
            $report = new tldMatrix(
                $STATS,
                "buyer_dpt", "type_name", "qty",
                "$php_self?m[0]=inventory&m[1]=reports&m[2]=byDeptByType&z=HARDWARE&region=$region$extra_url_inv",
                "Inventory item by region by item type $bu_title"
            );
            $body .= $report->fetch();
        }
?>

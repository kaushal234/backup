<?php

use ApiBundle\Client;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\HttpClient\Exception\ClientException;

if (empty($id)) {
    $DEFAULT_ERROR[] = 'ERROR: no id set..';
    return;
}
$sor = new tldSOR((int)$id);
if ($sor->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: SOR#$id does not exist...";
    return;
}
$header = $sor->getHeader();

$DEFAULT_TITLE .= "\SOR#$id";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sor&m[1]=view&id=$id" title="General information page">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=files&id=$id" title="File attachments">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=log&id=$id" title="Event log">Logs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=form&m[2]=addSOL&pid=$id" title="Add SOR Line">Add SOL</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=dup&id=$id" title="Duplicate">Duplicate</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=edit&id=$id" title="Edit">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=del&id=$id" title="Delete">Delete</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=print&id=$id" title="Print Version" target="_blank">Print</a>
EOF;
if ($user->isInGroup(['gg_MIS'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=xmlSource&id=$id">XML</a>
EOF;
}

switch ($m[2]) {
    case 'transfer':
        $DEFAULT_TITLE .= "\Transfer eQuote to SOR";

        global $kernel;
        $client = $kernel->getContainer()->get(Client::class);

        try {
            $quote = $client->findOneBy('sales/quotes', ['quoteNumber' => $sor->getHeader()['eqno']]);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = 'ERROR: No quote found for this sale order...<br>';
            break;
        }

        $encoder = new XmlEncoder();

        $arrayXML = ($encoder->decode($quote->toArray()['xml'], 'xml'));
        $lines = $arrayXML['DataArea']['Quotation']['QuotationLines'] ?? [];
        if ('TLDFactoryBU' === key($lines)) {
            $lines =[$lines];
        }
        $xmlHeader = $arrayXML['DataArea']['Quotation']['QuotationHeader'] ?? [];

        if (!count($lines)) {
            $DEFAULT_ERROR[] = 'ERROR: No SOR Lines to transfer...<br>';
            break;
        }

        // Get lists for the form
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $models = tldUtils::getSqlToAssocArray("SELECT model FROM models WHERE hide=0 AND model<>' ALL_MODELS' ORDER BY model", 'smartyOptions', ['model', 'model']);
        $listInco = tldList::optionsByListNameAsListKeyListItem('list.inco.terms');
        $curList = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.common.currency');
        $TIERS = tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
        $internalCatList = tldSOL::getInternalCategoriesList();
        $externalCatList = tldSOL::getExternalCategoriesList();

        // Get the form
        $form = new HTML_QuickForm('frmNewSORLine', 'post');
        $form->addElement('hidden', 'm[0]', 'sor');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'transfer');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Transfer SOR line Form');

        // Get data for each sales order line
        foreach ($lines as $i => $line) {
            // Country cannot be map correctly

            // 1 - Get SOL general info
            $form->addElement('header', 'title', "Transfer of the Sales Order Line $i");
            $form->addElement('header', 'title', 'Factory and product information');
            $form->addElement('select', "line[$i][erp]", 'TLD Factory BU', ['' => ''] + $factoryList);
            $form->setDefaults([
                "line[$i][erp]" => $line['TLDFactoryBU'],
            ]);

            $form->addElement('hidden', "line[$i][qty]", $line['Quantity']);
            if (!in_array($line['ProductModel'], $models) && !$form->validate()) {
                $error .= 'ERROR: ' . $line['ProductModel'] . ' is not in online database..<br>';
                $form->addElement('select', "line[$i][model]",
                    'Product Model, ***' . $line['ProductModel'] . ' not in online database***',
                    ['' => ''] + $models
                );
            } else {
                $form->addElement('select', "line[$i][model]", 'Product Model',
                    [$line['ProductModel'] => $line['ProductModel']] + $models
                );
            }
            $form->addElement('select', "line[$i][eng_tier]", 'Emission Rating', $TIERS);
            $form->setDefaults(["line[$i][eng_tier]" => $line['EmissionRating']]);
            $form->addElement('select', "line[$i][parts_inc]", 'Ship with Spare Parts?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][notes]", 'Notes', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);

            // 2 - Warranty Information
            $form->addElement('header', 'title', 'Warranty details');
            $form->addElement('textarea', "line[$i][wrty_spec]",
                'Describe Special Warranty Conditions if any (Blank means the TLD Standard Warranty terms apply)',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
            );
            $form->setDefaults(["line[$i][wrty_spec]" => $xmlHeader['WarrantyDetails'] ?? '']);

            $form->addElement('select', "line[$i][conf_wrty_erp]",
                'Have these Special Warranty Conditions formaly accepted and backed-up by the TLD Factory?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']
            );

            // 3 - Payment Information
            $form->addElement('header', 'title', 'Payment details');
            $form->addElement('select', "line[$i][conf_lc]", 'Letter of Credit required?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][tpay]",
                'Describe Special Terms of Payment if any (Blank means the TLD Standard payment terms apply)',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
            );
            $form->setDefaults(["line[$i][tpay]" => $xmlHeader['SpecialTermsOfPayment'] ?? '']);
            $form->addElement('text', "line[$i][dp_pc]", 'Down Payment Percentage');

            // 4 - Delivery Information
            $form->addElement('header', 'title', 'Delivery details');
            $form->addElement('select', "line[$i][inco]", 'Inco Terms', ['' => ''] + $listInco);
            $form->addElement('text', "line[$i][inco_loc]", 'Inco Location');
            $form->setDefaults([
                "line[$i][inco]" => $arrayXML['DataArea']['Quotation']['QuotationHeader']['IncoTerms'],
                "line[$i][inco_loc]" => $arrayXML['DataArea']['Quotation']['QuotationHeader']['IncoLocations'],
            ]);

            $form->addElement('select', 'ctry', 'Country of sales',
                ['' => ''] + tldCountry::optionsAsNameName());
            $form->setDefaults(['ctry' => $xmlHeader['CountryOfSales']]);

            $form->addElement('text', "line[$i][trans]", 'Transportation responsability', ['maxlength' => 10]);
            $form->addElement('select', "line[$i][del_pen]", 'Delivery Penalties?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][delpen_cond]", 'Please describe delivery penalty',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
            $form->addElement('select', "line[$i][conf_sls]",
                'Have these late delivery penalties formaly approved by the TLD Sales Organization?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('select', "line[$i][conf_erp]", 'Are these delivery penalties approved and backed-up by the TLD factory and has DMS#3228 been completed ?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('select', "line[$i][conf_cis]", 'Customer inspection before shipment?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][docs_inc]", 'Special Documentary Requirements',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);

            // 5 - Currency Information
            $form->addElement('header', 'title',
                'Forex Rates, PLEASE NOTE THAT ALL RATES ARE PER USD REGARDLESS WHETHER YOU USE USD OR NOT...');
            $form->addElement('header', 'title', 'USD 1.0 = EUR ' . round(1 / tldForex::getUSDPerEUR(), 4));

            $form->addElement('header', 'title', 'Default currency for this SOR Line is ' . $xmlHeader['OrderCurrency']);

            $arrayVariants = $line['Variants'] ?? [];

            // Not sure it should be implemented
            // Find difference between TP currency and final currency
            // If exist, store the TP currency with the rate on the forex
            // Added on the form, to allowed change

            $variantItems = isset($arrayVariants['Variant']) && is_array($arrayVariants['Variant']) ? $arrayVariants['Variant'] : [];

            $currencies = array_reduce($variantItems, static function ($carry, $variant) {
                if ($variant['CurrencyNegotiatedTP'] === $variant['CurrencyActualSalesPrice']) {
                    return $carry;
                }

                $carry[$variant['CurrencyNegotiatedTP']] = tldForex::getRate('USD', $variant['CurrencyNegotiatedTP']);
                return $carry;
            }, []);

            // In case Variant in tne XML file does not contain any value of currency.
            // So we use the default value of Quotation currency.
            // This will add the DCUR value of currency in mod_list table, and then
            // it will correctly display the currency in the breakdown internal/external item form.
            // See TTS#29853 TTS#28784
            if (empty($currencies) && !empty($xmlHeader['OrderCurrency'])) {
                $currencies[$xmlHeader['OrderCurrency']] = tldForex::getRate('USD', $xmlHeader['OrderCurrency']);
            }

            foreach ($currencies as $cur => $rate) {
                $cur = strtoupper($cur);
                if (empty($rate) || $cur == 'USD') {
                    continue;
                }
                $form->addElement('text', "line[$i][curs][$cur]", "USD 1.0 = $cur");
                $form->addRule("line[$i][curs][$cur]", 'Required', 'required');
                $form->setDefaults(["line[$i][curs][$cur]" => $rate]);
            }
            // End Not sure it should be implemented

            $indexInternalOption = 0;
            $indexExternalOption = 0;
            // 6 - Get breakdown
            foreach ($variantItems as $j => $option) {
                // Internal
                if (in_array(strtoupper($option['Category']), array_keys($internalCatList))) {
                    $form->addElement('header', 'title', 'SOR Line #' . ($i + 1) . ' Internal Option #' . (++$indexInternalOption));

                    $form->addElement('select', "line[$i][breakdown][$j][caty]", 'Category', ['' => ''] + $internalCatList);
                    $form->setDefaults(["line[$i][breakdown][$j][caty]" => strtoupper($line['Variants']['Variant'][$j]['Category'])]);
                    $form->addRule("line[$i][breakdown][$j][caty]", 'Category is required for each breakdown item', 'required');
                    $form->addElement('text', "line[$i][breakdown][$j][dsca]", 'Description', ['size' => 30]);
                    $form->setDefaults(["line[$i][breakdown][$j][dsca]" => strtoupper($line['Variants']['Variant'][$j]['Description'])]);

                    $mrspGRP = [];
                    $mrspGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][mrsp_cur]", 'Currency', $curList);
                    $form->setDefaults(["line[$i][breakdown][$j][mrsp_cur]" => strtoupper($line['Variants']['Variant'][$j]['CurrencyPublishTP'])]);
                    $mrspGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][mrsp]", 'Published TP');
                    $field_mrspGRP = $form->addGroup($mrspGRP, null, 'Published TP', '&nbsp;');
                    $form->setDefaults(["line[$i][breakdown][$j][mrsp]" => stringNumberFormater($line['Variants']['Variant'][$j]['PublishTP'])]);

                    $pricGRP = [];
                    $pricGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pric_cur]", 'Currency', $curList);
                    $form->setDefaults(["line[$i][breakdown][$j][pric_cur]" => strtoupper($line['Variants']['Variant'][$j]['CurrencyNegotiatedTP'])]);
                    $pricGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pric]", 'Negotiated TP');
                    $form->setDefaults(["line[$i][breakdown][$j][pric]" => stringNumberFormater($line['Variants']['Variant'][$j]['NegotiatedTP'])]);
                    $form->addGroup($pricGRP, null, 'Negotiated TP', '&nbsp;');

                    $prisGRP = [];
                    $prisGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pris_cur]", 'Currency', $curList);
                    $form->setDefaults(["line[$i][breakdown][$j][pris_cur]" => strtoupper($line['Variants']['Variant'][$j]['CurrencyActualSalesPrice'])]);
                    $prisGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pris]", 'Actual Sales Price');
                    $form->setDefaults(["line[$i][breakdown][$j][pris]" => stringNumberFormater($line['Variants']['Variant'][$j]['ActualSalesPrice'])]);
                    $form->addGroup($prisGRP, null, 'Actual Sales Price', '&nbsp;');

                    // Set Required
                    $form->addGroupRule(
                        $field_mrspGRP->getName(),
                        [
                            "line[$i][breakdown][$j][mrsp_cur]" => [['Required', 'required']],
                            "line[$i][breakdown][$j][mrsp]" => [['Required', 'required']],
                        ]
                    );
                } // External
                else {
                    $form->addElement('header', 'title', 'SOR Line #' . ($i + 1) . ' External Option #' . (++$indexExternalOption));

                    $form->addElement('select', "line[$i][breakdown][$j][caty]", 'Category', ['' => ''] + $externalCatList);
                    $form->setDefaults(["line[$i][breakdown][$j][caty]" => strtoupper($line['Variants']['Variant'][$j]['Category'])]);
                    $form->addRule("line[$i][breakdown][$j][caty]", 'Category is required for each breakdown item', 'required');
                    $form->addElement('text', "line[$i][breakdown][$j][dsca]", 'Description', ['size' => 30]);
                    $form->setDefaults(["line[$i][breakdown][$j][dsca]" => strtoupper($line['Variants']['Variant'][$j]['Description'])]);

                    $pricGRP = [];
                    $pricGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pric_cur]", 'Currency', $curList);
                    $form->setDefaults(["line[$i][breakdown][$j][pric_cur]" => strtoupper($line['Variants']['Variant'][$j]['CurrencyCost'])]);
                    $pricGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pric]", 'Cost');
                    $form->setDefaults(["line[$i][breakdown][$j][pric]" => stringNumberFormater($line['Variants']['Variant'][$j]['Cost'])]);
                    $form->addGroup($pricGRP, null, 'Cost', '&nbsp;');

                    $prisGRP = [];
                    $prisGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pris_cur]", 'Currency', $curList);
                    $form->setDefaults(["line[$i][breakdown][$j][pris_cur]" => strtoupper($line['Variants']['Variant'][$j]['CurrencySalesPrice'] ?? $line['Variants']['Variant'][$j]['CurrencyActualSalesPrice'] ?? '')]);
                    $prisGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pris]", 'Sales Price');
                    $form->setDefaults(["line[$i][breakdown][$j][pris]" => stringNumberFormater($line['Variants']['Variant'][$j]['SalesPrice'] ?? $line['Variants']['Variant'][$j]['ActualSalesPrice'] ?? null)]);
                    $form->addGroup($prisGRP, null, 'Sales Price', '&nbsp;');
                    if ($option['Category'] == 'SPEC. DISCOUNT') {
                        $localDefaults = [
                            "line[$i][breakdown][$j][caty]" => 'SPECIAL DISCOUNT',
                        ];
                        if (0 > (float)$option['pric']) {
                            $localDefaults["line[$i][breakdown][$j][pric]"] = 0;
                        }
                        $form->setDefaults($localDefaults);
                    }
                }
            }
            // 7 - Get delivery dates for each unit
            $form->addElement('header', 'title', 'Requested delivery dates');
            $form->addElement('button', 'SetDelEarlyToYes', 'Set ALL units early delivery to Yes',
                ['onclick' => "javascript:$('.del_early').val('Y');"]
            );
            // Create 5 batches
            for ($j = 1; $j < 6; $j++) {
                $form->addElement('header', 'title', "Requested delivery dates - Batch $j");
                $form->addElement('text', "line[$i][batchs][$j][qty]", 'Quantity', ['size' => 5]);
                $form->addElement('date', "line[$i][batchs][$j][del_dat_array]", 'Requested Delivery Date',
                    ['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
                $form->addElement('text', "line[$i][batchs][$j][short_desc]", 'Short Description', ['size' => 40]);
                $form->addElement('text', "line[$i][batchs][$j][del_location]", 'Delivery location', ['size' => 40]);
                $form->addElement('select', "line[$i][batchs][$j][del_early]", 'Early delivery ok?',
                    ['N' => 'No', 'Y' => 'Yes'], ['class' => 'del_early']);
                $form->setDefaults(["line[$i][batchs][$j][del_dat_array]" => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')]]);
                $form->addRule("line[$i][batchs][$j][qty]", 'Should be numeric', 'numeric');
            }
            $form->setDefaults(["line[$i][batchs][1][qty]" => $line['Quantity']]);

            // SOL field to put required
            $fields = [
                'model', 'eng_tier', 'erp', 'qty', 'sls_orno', 'erp_orno', 'parts_inc',
                'wrty_std', 'conf_wrty_erp', 'tpay', 'conf_cxo', 'conf_lc', 'cu_ocur', 'inco',
                'inco_loc', 'del_pen', 'conf_sls', 'conf_erp', 'parts_inc', 'trans', 'ctry',
            ];
            // Put SOL required field
            foreach ($fields as $field) {
                $form->addRule("line[$i][$field]", 'Required', 'required');
            }
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');
        // set SOL default values
        if ($defaults) {
            $form->setDefaults(['line' => $defaults]);
        }

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();

        if (empty($id)) {
            $DEFAULT_ERROR[] = 'ERROR: no SOR ID# set';
            break;
        }
        if (!count($vars['line'])) {
            $DEFAULT_ERROR[] = 'ERROR: no SOR lines to add...';
            break;
        }

        $sorLines = $sor->getLines();
        //add the sor lines
        foreach ($vars['line'] as $i => $line) {
            // Check units quantities, if do not match we log it
            $qty_given = 0;
            foreach ($line['batchs'] as $batch) {
                $qty_given += is_numeric($batch['qty']) ? (int) $batch['qty'] : 0;
            }
            if ($qty_given <> $line['Quantity']) {
                tldUtils::log_event("SOR TRANSFER >> SOR#$id -> Line $i: Units qty ($qty_given) <> eQuote qty ({$sess['sor']['equote']['line'][$i]['qty']})");
            }
            // Check delivery penalties
            if ($line['del_pen'] == 'N') {
                $line['conf_sls'] = 'N';
                $line['conf_erp'] = 'N';
            }
            $line['bu'] = tldLocation::getIDByERP($line['erp']);
            $line = tldUtils::cleanupFormInput($line);

            // 1 - Create sales order line
            $solid = $sor->addLine($line, $user->getID());
            // Check if insert was good else error msg
            if (is_string($solid)) {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Problem inserting a SOR line. Reason: $solid";
                continue;
            }
            $sol = new tldSOL($solid);
            $sol->addLogEntry($user->getID(), "SOR Line# $solid added to SOR# $id");
            $sol->openIbsTask($sorLines, $sor->getHeader());
            $sol->openModelIbsTask($sorLines, $sor->getHeader());

            // 2 - Save the forex data
            //get the default currency from the xml data
            $sol->addListEntry('DCUR', '', $xmlHeader['OrderCurrency']);
            // we want actual change rate for EUR but don't want duplicate it if we add it with line['curs'] l:382
            if (!is_array($line['curs']) || !array_key_exists('EUR', $line['curs'])){
                $rate = tldForex::getRate('USD', 'EUR');
                $e = $sol->addListEntry('CURS', 'EUR', $rate);
            }

            //Not sure it should be implemented
            if (is_array($line['curs'])) {
                $line['curs'] = tldUtils::cleanupFormInput($line['curs']);
                foreach ($line['curs'] as $key => $value) {
                    $e = $sol->addListEntry('CURS', $key, $value);
                    if (!is_numeric($e)) {
                        $DEFAULT_ERROR[] = $e;
                    }
                    if ($defaults['line'][$i]['cur'][$key] <> $value) {
                        $DEFAULT_ERROR[] = "Forex Rate for $key was changed from default for SOR Line $solid";
                    }
                }
            }
            // End not sure it should be implemented

            $body .= 'SOR Line #' . $solid . " added to SOR #$id<br>";

            // 3 - Add breakdown

            foreach ($line['breakdown'] as $item) {
                // review publish price list for internal items
                if (in_array($item['caty'], array_keys($internalCatList, true))) {
                    $item['prip_cur'] = $item['mrsp_cur'];
                    $item['prip'] = round($item['mrsp'] / 0.9, 2);
                }
                if ($item['caty'] === 'SPECIAL DISCOUNT' && (float)$item['pric'] < 0.0) {
                    $item['pric'] = 0;
                }
                // clean data
                $item = tldUtils::cleanupFormInput($item);
                $soroptid = $sol->addOpt($item);
                if (is_numeric($soroptid)) {
                    $body .= 'SOR Option #' . $soroptid . " added to SOR Line#$solid<br>";
                }
            }


            // 4 - Add unit and delivery date info
            // Get type of the SOL equipment
            $datasheet = tldDatasheet::byModel($line['model']);
            // If trailers and Dollies (id=15) create batch qty for units
            if ($datasheet['parent_id'] == 15) {
                foreach ($line['batchs'] as $batch) {
                    // Check if qty given
                    if ($batch['qty'] <= 0) {
                        continue;
                    }
                    // Add units
                    $unit['parent_id'] = $solid;
                    $unit['del_dat'] = implode('-', $batch['del_dat_array']);
                    $unit['short_desc'] = $batch['short_desc'];
                    $unit['del_location'] = $batch['del_location'];
                    $unit['del_early'] = $batch['del_early'];
                    $unit['batch_qty'] = $batch['qty'];
                    $unit = tldUtils::cleanupFormInput($unit);
                    $e = tldSORUnit::insert($unit);
                    if (!is_numeric($e)) {
                        $DEFAULT_ERROR[] = $e;
                    }
                }
            } else {
                foreach ($line['batchs'] as $batch) {
                    // Check if qty given
                    if ($batch['qty'] <= 0) {
                        continue;
                    }
                    // Add units as much as requested
                    for ($x = 0; $x < $batch['qty']; $x++) {
                        $unit['parent_id'] = $solid;
                        $unit['del_dat'] = implode('-', $batch['del_dat_array']);
                        $unit['short_desc'] = $batch['short_desc'];
                        $unit['del_location'] = $batch['del_location'];
                        $unit['del_early'] = $batch['del_early'];
                        $unit['batch_qty'] = 1;
                        $unit = tldUtils::cleanupFormInput($unit);
                        $e = tldSORUnit::insert($unit);
                        if (!is_numeric($e)) {
                            $DEFAULT_ERROR[] = $e;
                        }
                    }
                }
            }
        }
        // Notify rates
        $sol->notifyForexRates();
        // Display SOL
        $body .= getGeneralTab($header);

        try {
            $client->remove('sales/quotes', $quote->toArray()['id']);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getContent(), true);
            return 'ERROR: Could not remove. Reason: ' . $errors['hydra:description'] . 'Please open a TTS to remove manually by MIS !';
        }
        break;
    case 'transfer_legacy':
        $DEFAULT_TITLE .= "\Transfer eQuote to SOR";

        $pid = $sess['sor']['equote']['header']['sorid'];
        $lines = $sess['sor']['equote']['line'];
        $defaults = $lines;
        if (!count($lines ?? [])) {
            $DEFAULT_ERROR[] = 'ERROR: No SOR Lines to transfer...<br>';
            break;
        }

        // Get lists for the form
        $factoryList = tldLocation::getFactoryList('smartyOptions');
        $models = tldUtils::getSqlToAssocArray("SELECT model FROM models WHERE hide=0 AND model<>' ALL_MODELS' ORDER BY model", 'smartyOptions', ['model', 'model']);
        $listInco = tldList::optionsByListNameAsListKeyListItem('list.inco.terms');
        $curList = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.common.currency');
        $TIERS = tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
        $internalCatList = tldSOL::getInternalCategoriesList();
        $externalCatList = tldSOL::getExternalCategoriesList();

        // Get the form
        $form = new HTML_QuickForm('frmNewSORLine', 'post');
        $form->addElement('hidden', 'm[0]', 'sor');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'transfer_legacy');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Transfer SOR line Form');

        // Get data for each sales order line
        foreach ($lines as $i => $line) {

            // 1 - Get SOL general info
            $form->addElement('header', 'title', "Transfert of the Sales Order Line $i");
            $form->addElement('header', 'title', 'Factory and product information');
            $form->addElement('select', "line[$i][erp]", 'TLD Factory BU', ['' => ''] + $factoryList);
            $form->addElement('hidden', "line[$i][qty]", $line['qty']);
            if (!in_array($line['model'], $models) && !$form->validate()) {
                $error .= 'ERROR: ' . $line['model'] . ' is not in online database..<br>';
                $form->addElement('select', "line[$i][model]",
                    'Product Model, ***' . $line['model'] . ' not in online database***',
                    ['' => ''] + $models
                );
            } else {
                $form->addElement('select', "line[$i][model]", 'Product Model',
                    [$line['model'] => $line['model']] + $models
                );
            }
            $form->addElement('select', "line[$i][eng_tier]", 'Emission Rating', $TIERS);
            $form->addElement('select', "line[$i][parts_inc]", 'Ship with Spare Parts?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][notes]", 'Notes', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);

            // 2 - Warranty Information
            $form->addElement('header', 'title', 'Warranty details');
            $form->addElement('textarea', "line[$i][wrty_spec]",
                'Describe Special Warranty Conditions if any (Blank means the TLD Standard Warranty terms apply)',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
            );
            $form->addElement('select', "line[$i][conf_wrty_erp]",
                'Have these Special Warranty Conditions formaly accepted and backed-up by the TLD Factory?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']
            );

            // 3 - Payment Information
            $form->addElement('header', 'title', 'Payment details');
            $form->addElement('select', "line[$i][conf_lc]", 'Letter of Credit required?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][tpay]",
                'Describe Special Terms of Payment if any (Blank means the TLD Standard payment terms apply)',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
            );
            $form->addElement('text', "line[$i][dp_pc]", 'Down Payment Percentage');

            // 4 - Delivery Information
            $form->addElement('header', 'title', 'Delivery details');
            $form->addElement('select', "line[$i][inco]", 'Inco Terms', ['' => ''] + $listInco);
            $form->addElement('text', "line[$i][inco_loc]", 'Inco Location');
            $form->setDefaults([
                    "line[$i][inco]" => $sess['sor']['equote']['header']['inco'],
                    "line[$i][inco_loc]" => $sess['sor']['equote']['header']['inco_loc']]
            );
            $form->addElement('select', 'ctry', 'Country of sales',
                ['' => ''] + tldCountry::optionsAsNameName());
            $form->setDefaults([
                    'ctry' => $sess['sor']['equote']['line'][0]['country']]
            );
            $form->addElement('text', "line[$i][trans]", 'Transportation responsability', ['maxlength' => 10]);
            $form->addElement('select', "line[$i][del_pen]", 'Delivery Penalties?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][delpen_cond]", 'Please describe delivery penalty',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
            $form->addElement('select', "line[$i][conf_sls]",
                'Have these late delivery penalties formaly approved by the TLD Sales Organization?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('select', "line[$i][conf_erp]", 'Are these delivery penalties approved and backed-up by the TLD factory and has DMS#3228 been completed ?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('select', "line[$i][conf_cis]", 'Customer inspection before shipment?',
                ['' => '', 'N' => 'N', 'Y' => 'Y']);
            $form->addElement('textarea', "line[$i][docs_inc]", 'Special Documentary Requirements',
                ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);

            // 5 - Currency Information
            $form->addElement('header', 'title',
                'Forex Rates, PLEASE NOTE THAT ALL RATES ARE PER USD REGARDLESS WHETHER YOU USE USD OR NOT...');
            $form->addElement('header', 'title', 'USD 1.0 = EUR ' . round(1 / tldForex::getUSDPerEUR(), 4));
            $form->addElement('header', 'title', 'Default currency for this SOR Line is ' . $line['dcur']);
            foreach ($line['cur'] as $cur => $rate) {
                $cur = strtoupper($cur);
                if (empty($rate) || $cur == 'USD') {
                    continue;
                }
                $form->addElement('text', "line[$i][curs][$cur]", "USD 1.0 = $cur");
                $form->addRule("line[$i][curs][$cur]", 'Required', 'required');
                $form->setDefaults(["line[$i][curs][$cur]" => $rate]);
            }

            // 6 - Get breakdown
            foreach ($line['breakdown'] as $j => $option) {
                // Internal
                if (in_array($option['caty'], array_keys($internalCatList))) {
                    $form->addElement('header', 'title', 'SOR Line #' . ($i + 1) . ' Internal Option #' . ($j + 1));

                    $form->addElement('select', "line[$i][breakdown][$j][caty]", 'Category', ['' => ''] + $internalCatList);
                    $form->addElement('text', "line[$i][breakdown][$j][dsca]", 'Description', ['size' => 30]);

                    $mrspGRP = [];
                    $mrspGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][mrsp_cur]", 'Currency', $curList);
                    $mrspGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][mrsp]", 'Published TP');
                    $field_mrspGRP = $form->addGroup($mrspGRP, null, 'Published TP', '&nbsp;');

                    $pricGRP = [];
                    $pricGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pric_cur]", 'Currency', $curList);
                    $pricGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pric]", 'Negotiated TP');
                    $form->addGroup($pricGRP, null, 'Negotiated TP', '&nbsp;');

                    $prisGRP = [];
                    $prisGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pris_cur]", 'Currency', $curList);
                    $prisGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pris]", 'Actual Sales Price');
                    $form->addGroup($prisGRP, null, 'Actual Sales Price', '&nbsp;');

                    // Set Required
                    $form->addGroupRule(
                        $field_mrspGRP->getName(),
                        [
                            "line[$i][breakdown][$j][mrsp_cur]" => [['Required', 'required']],
                            "line[$i][breakdown][$j][mrsp]" => [['Required', 'required']],
                        ]
                    );
                } // External
                else {
                    $form->addElement('header', 'title', 'SOR Line #' . ($i + 1) . ' External Option #' . ($j + 1));

                    $form->addElement('select', "line[$i][breakdown][$j][caty]", 'Category', ['' => ''] + $externalCatList);
                    $form->addElement('text', "line[$i][breakdown][$j][dsca]", 'Description', ['size' => 30]);

                    $pricGRP = [];
                    $pricGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pric_cur]", 'Currency', $curList);
                    $pricGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pric]", 'Cost');
                    $form->addGroup($pricGRP, null, 'Cost', '&nbsp;');

                    $prisGRP = [];
                    $prisGRP[] =& $form->createElement('select', "line[$i][breakdown][$j][pris_cur]", 'Currency', $curList);
                    $prisGRP[] =& $form->createElement('text', "line[$i][breakdown][$j][pris]", 'Sales Price');
                    $form->addGroup($prisGRP, null, 'Sales Price', '&nbsp;');
                    if ($option['caty'] == 'SPEC. DISCOUNT') {
                        $localDefaults = [
                            "line[$i][breakdown][$j][caty]" => 'SPECIAL DISCOUNT',
                        ];
                        if (0 > (float)$option['pric']) {
                            $localDefaults["line[$i][breakdown][$j][pric]"] = 0;
                        }
                        $form->setDefaults($localDefaults);
                    }
                }
            }
            // 7 - Get delivery dates for each unit
            $form->addElement('header', 'title', 'Requested delivery dates');
            $form->addElement('button', 'SetDelEarlyToYes', 'Set ALL units early delivery to Yes',
                ['onclick' => "javascript:$('.del_early').val('Y');"]
            );
            // Create 5 batches
            for ($j = 1; $j < 6; $j++) {
                $form->addElement('header', 'title', "Requested delivery dates - Batch $j");
                $form->addElement('text', "line[$i][batchs][$j][qty]", 'Quantity', ['size' => 5]);
                $form->addElement('date', "line[$i][batchs][$j][del_dat_array]", 'Requested Delivery Date',
                    ['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
                $form->addElement('text', "line[$i][batchs][$j][short_desc]", 'Short Description', ['size' => 40]);
                $form->addElement('text', "line[$i][batchs][$j][del_location]", 'Delivery location', ['size' => 40]);
                $form->addElement('select', "line[$i][batchs][$j][del_early]", 'Early delivery ok?',
                    ['N' => 'No', 'Y' => 'Yes'], ['class' => 'del_early']);
                $form->setDefaults(["line[$i][batchs][$j][del_dat_array]" => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')]]);
                $form->addRule("line[$i][batchs][$j][qty]", 'Should be numeric', 'numeric');
            }
            $form->setDefaults(["line[$i][batchs][1][qty]" => $line['qty']]);

            // SOL field to put required
            $fields = [
                'model', 'eng_tier', 'erp', 'qty', 'sls_orno', 'erp_orno', 'parts_inc',
                'wrty_std', 'conf_wrty_erp', 'tpay', 'conf_cxo', 'conf_lc', 'cu_ocur', 'inco',
                'inco_loc', 'del_pen', 'conf_sls', 'conf_erp', 'parts_inc', 'trans', 'ctry',
            ];
            // Put SOL required field
            foreach ($fields as $field) {
                $form->addRule("line[$i][$field]", 'Required', 'required');
            }
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');
        // set SOL default values
        if ($defaults) {
            $form->setDefaults(['line' => $defaults]);
        }

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        if (empty($pid)) {
            $DEFAULT_ERROR[] = 'ERROR: no SOR ID# set';
            break;
        }
        if (!count($vars['line'])) {
            $DEFAULT_ERROR[] = 'ERROR: no SOR lines to add...';
            break;
        }
        $sor = new tldSOR($pid);
        $sorLines = $sor->getLines();
        //add the sor lines
        foreach ($vars['line'] as $i => $line) {
            // Check units quantities, if do not match we log it
            $qty_given = 0;
            foreach ($line['batchs'] as $batch) {
                $qty_given += is_numeric($batch['qty']) ? (int)$batch['qty'] : 0;
            }
            if ($qty_given <> $sess['sor']['equote']['line'][$i]['qty']) {
                tldUtils::log_event("SOR TRANSFERT >> SOR#$pid -> Line $i: Units qty ($qty_given) <> eQuote qty ({$sess['sor']['equote']['line'][$i]['qty']})");
            }
            // Check delivery penalties
            if ($line['del_pen'] == 'N') {
                $line['conf_sls'] = 'N';
                $line['conf_erp'] = 'N';
            }
            $line['bu'] = tldLocation::getIDByERP($line['erp']);
            $line = tldUtils::cleanupFormInput($line);

            // 1 - Create sales order line
            $solid = $sor->addLine($line, $user->getID());
            // Check if insert was good else error msg
            if (is_string($solid)) {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Problem inserting a SOR line. Reason: $solid";
                continue;
            }
            $sol = new tldSOL($solid);
            $sol->addLogEntry($user->getID(), "SOR Line# $solid added to SOR# $pid");
            $sol->openIbsTask($sorLines, $sor->getHeader());
            $sol->openModelIbsTask($sorLines, $sor->getHeader());

            // 2 - Save the forex data
            //get the default currency from the xml data
            $sol->addListEntry('DCUR', '', $lines[$i]['dcur']);
            $e = $sol->addListEntry('CURS', 'EUR', round(1 / tldForex::getUSDPerEUR(), 4));
            if (is_array($line['curs'])) {
                $line['curs'] = tldUtils::cleanupFormInput($line['curs']);
                foreach ($line['curs'] as $key => $value) {
                    $e = $sol->addListEntry('CURS', $key, $value);
                    if (!is_numeric($e)) {
                        $DEFAULT_ERROR[] = $e;
                    }
                    if ($defaults['line'][$i]['cur'][$key] <> $value) {
                        $DEFAULT_ERROR[] = "Forex Rate for $key was changed from default for SOR Line $solid";
                    }
                }
            }
            $body .= 'SOR Line #' . $solid . " added to SOR #$pid<br>";

            // 3 - Add breakdown

            foreach ($line['breakdown'] as $item) {
                // review publish price list for internal items
                if (in_array($item['caty'], array_keys($internalCatList))) {
                    $item['prip_cur'] = $item['mrsp_cur'];
                    $item['prip'] = round($item['mrsp'] / 0.9, 2);
                }
                if ($item['caty'] === 'SPECIAL DISCOUNT' && (float)$item['pric'] < 0.0) {
                    $item['pric'] = 0;
                }
                // clean data
                $item = tldUtils::cleanupFormInput($item);
                $soroptid = $sol->addOpt($item);
                if (is_numeric($soroptid)) {
                    $body .= 'SOR Option #' . $soroptid . " added to SOR Line#$solid<br>";
                }
            }


            // 4 - Add unit and delivery date info
            // Get type of the SOL equipment
            $datasheet = tldDatasheet::byModel($line['model']);
            // If trailers and Dollies (id=15) create batch qty for units
            if ($datasheet['parent_id'] == 15) {
                foreach ($line['batchs'] as $batch) {
                    // Check if qty given
                    if ($batch['qty'] <= 0) {
                        continue;
                    }
                    // Add units
                    $unit['parent_id'] = $solid;
                    $unit['del_dat'] = implode('-', $batch['del_dat_array']);
                    $unit['short_desc'] = $batch['short_desc'];
                    $unit['del_location'] = $batch['del_location'];
                    $unit['del_early'] = $batch['del_early'];
                    $unit['batch_qty'] = $batch['qty'];
                    $unit = tldUtils::cleanupFormInput($unit);
                    $e = tldSORUnit::insert($unit);
                    if (!is_numeric($e)) {
                        $DEFAULT_ERROR[] = $e;
                    }
                }
            } else {
                foreach ($line['batchs'] as $batch) {
                    // Check if qty given
                    if ($batch['qty'] <= 0) {
                        continue;
                    }
                    // Add units as much as requested
                    for ($x = 0; $x < $batch['qty']; $x++) {
                        $unit['parent_id'] = $solid;
                        $unit['del_dat'] = implode('-', $batch['del_dat_array']);
                        $unit['short_desc'] = $batch['short_desc'];
                        $unit['del_location'] = $batch['del_location'];
                        $unit['del_early'] = $batch['del_early'];
                        $unit['batch_qty'] = 1;
                        $unit = tldUtils::cleanupFormInput($unit);
                        $e = tldSORUnit::insert($unit);
                        if (!is_numeric($e)) {
                            $DEFAULT_ERROR[] = $e;
                        }
                    }
                }
            }
        }
        // Notify rates
        $sol->notifyForexRates();
        // Display SOL
        $body .= getGeneralTab($header);
        break;
    case 'print':
        $DEFAULT_TITLE .= 'Print Version';
        $body .= getGeneralTab($header);
        $rows = tldSOL::byParent($id);
        foreach ($rows as $row) {
            $body .= _getPrintSOL($row['id']);
        }
        $template = 'empty.tpl';
        break;
    default:
        $body .= getGeneralTab($header);
        break;
}

function stringNumberFormater(?string $number): ?float
{
    return null === $number || '' === $number ? null : (float) \str_replace(',', '', $number);
}
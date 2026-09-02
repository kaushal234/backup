<?php
include_once("zip.inc.php");
switch($m[3]){
    case 'download':
        switch($m[4]){
            case 'confirm':
                $sess['sales']['customers']['zip']->out();
                exit;
                break;
            default:
                if(empty($cuno) || empty($erp)){
                    $DEFAULT_ERROR[] = "ERROR: ERP or Customer number not set";
                    break;
                }
                // GET LIST OF DOCS
                $SQ = tldSQ::byCustomerERP($cuno, $erp); 	// Sales Quotes
                $SO = tldSO::byCustomerERP($cuno, $erp); 	// Sales Orders
                $PS = tldDINO::byCustomerERP($cuno, $erp);	// Packing Slip
                $IN = tldINV::byOpenByCuno($erp, $cuno, array('returnAll'=>TRUE));    // Invoice
                if(empty($SO) && empty($PS) && empty($IN)){
                    $DEFAULT_ERROR[] = "ERROR: No documents to send for $erp and $cuno";
                    break;
                }
                $form = new HTML_QuickForm('frmEmail', 'get', '','','',true);
                $form->addElement(	'hidden', 'm[0]', 'customers');
                $form->addElement(	'hidden', 'm[1]', 'view');
                $form->addElement(	'hidden', 'm[2]', 'zip');
                $form->addElement(	'hidden', 'm[3]', 'download');
                $form->addElement(	'hidden', 'id', $id);
                $form->addElement(	'hidden', 'cuno', $cuno);
                $form->addElement(	'hidden', 'erp', $erp);
                $form->addElement(	'header', 'title', "Download Zipped PDFs");

                // Sales Quote DOC
                if(count($SQ)){
                    foreach($SQ as $row){
                        $sqs[$row['t_qono']] = $row['t_qono'].", ".$row['t_qdat'].", ".$row['t_refa'];
                    }
                    $ams =& $form->addElement('advmultiselect', 'sqs', null,
                        $sqs,
                        array('size' => 10,
                            'class' => 'pool',
                            'style' => 'width:200px;'
                        )
                    );
                    $ams->setLabel(array('Sales Quotes', 'SQ#', 'Selected'));
                    $ams->setButtonAttributes('add',    array('value' => '-->>',
                        'class' => 'inputCommand'
                    ));
                    $ams->setButtonAttributes('remove', array('value' => '<<--',
                        'class' => 'inputCommand' ));
                }
                //Sales Orders
                if(count($SO)){
                    foreach($SO as $row){
                        $soas[$row['t_orno']] = $row['t_orno'].", ".$row['t_odat'].", ".$row['t_refa'];
                    }
                    $ams =& $form->addElement('advmultiselect', 'soas', null,
                        $soas,
                        array('size' => 10,
                            'class' => 'pool',
                            'style' => 'width:200px;'
                        )
                    );
                    $ams->setLabel(array('Sales Order Acknowledgements', 'SO#', 'Selected'));
                    $ams->setButtonAttributes('add',    array('value' => '-->>',
                        'class' => 'inputCommand'
                    ));
                    $ams->setButtonAttributes('remove', array('value' => '<<--',
                        'class' => 'inputCommand' ));
                }

                //Packing Slips
                if(count($PS)){
                    foreach($PS as $row){
                        $dinos[$row['t_dino']] = $row['t_dino'];
                    }
                    $ams =& $form->addElement('advmultiselect', 'dinos', null,
                        $dinos,
                        array('size' => 10,
                            'class' => 'pool',
                            'style' => 'width:200px;'
                        )
                    );
                    $ams->setLabel(array('Packing Slips', 'Packing Slip#', 'Selected'));
                    $ams->setButtonAttributes('add',    array('value' => '-->>',
                        'class' => 'inputCommand'
                    ));
                    $ams->setButtonAttributes('remove', array('value' => '<<--',
                        'class' => 'inputCommand' ));
                }

                //Invoice list
                if(count($IN)){
                    foreach($IN as $row){
                        $invs[$row['t_ttyp'].$row['t_ninv']] = $row['t_ttyp'].", ".$row['t_ninv'].", ".$row['t_docd'];
                    }
                    $ams =& $form->addElement('advmultiselect', 'invs', null,
                        $invs,
                        array('size' => 10,
                            'class' => 'pool',
                            'style' => 'width:200px;'
                        )
                    );
                    $ams->setLabel(array('Invoices', 'Invoice#', 'Selected'));
                    $ams->setButtonAttributes('add',    array('value' => '-->>',
                        'class' => 'inputCommand'
                    ));
                    $ams->setButtonAttributes('remove', array('value' => '<<--',
                        'class' => 'inputCommand' ));
                }


                $form->addElement(	'submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                # If the form validates then freeze the data
                $form->freeze();
                $vars = $form->exportValues();

                $arch = new tldArchive($erp);
                $webroot = tldArchive::getWebRoot();

                $pdfs = [];
                if(count($vars['sqs'] ?? [])){
                    foreach($vars['sqs'] as $sqid){
                        $rows = $arch->byTypeID('SALES QUOTATION', $sqid);
                        if(empty($rows[0]['filepath'])){
                            $DEFAULT_ERROR[] = "ERROR: no pdf found for Sales Quotation $sqid";
                        }else{
                            $pdfs[] = $webroot."/".$rows[0]['filepath'];
                        }
                    }
                }
                if(count($vars['soas'] ?? [])){
                    foreach($vars['soas'] as $soid){
                        $rows = $arch->byTypeID('SALES ORDER ACK', $soid);
                        if(empty($rows[0]['filepath'])){
                            $DEFAULT_ERROR[] = "ERROR: no pdf found for Sales Order Ack $soid";
                        }else{
                            $pdfs[] = $webroot."/".$rows[0]['filepath'];
                        }
                    }
                }
                if(count($vars['dinos'] ?? [])){
                    foreach($vars['dinos'] as $dinoid){
                        $rows = $arch->byTypeID('PACKING SLIP', $dinoid);
                        if(empty($rows[0]['filepath'])){
                            $DEFAULT_ERROR[] = "ERROR: no pdf found for Packing Slip $dinoid";
                        }else{
                            $pdfs[] = $webroot."/".$rows[0]['filepath'];
                        }
                    }
                }
                if(count($vars['invs'] ?? [])){
                    foreach($vars['invs'] as $invid){
                        $rows = $arch->byTypeID('SALES INVOICE', $invid);
                        if(empty($rows[0]['filepath'])){
                            $DEFAULT_ERROR[] = "ERROR: no pdf found for Invoice $invid";
                        }else{
                            $pdfs[] = $webroot."/".$rows[0]['filepath'];
                        }
                    }
                }
                if(count($pdfs) == 0){
                    $DEFAULT_ERROR[] = "WARNING: No PDF documents selected.";
                    break;
                }

                $zip = new tldFileZIP(tempnam('/tmp', "baanpdf").".zip");

                foreach($pdfs as $pdf){
                    $zip->addFile($pdf);
                }
                if(count($DEFAULT_ERROR ?? [])){
                    $sess['sales']['customers']['zip'] = $zip;
                    $DEFAULT_ERROR[] = "WARNING: One or more files could not be found. To continue downloading hit the link below";
                    $body .=<<<EOF
<br><br>
    <a href="$php_self?m[0]=customers&m[1]=view&m[2]=zip&m[3]=download&m[4]=confirm&id=$id">Confirm</a>
EOF;
                }else{
                    $zip->out("baanpdf.zip");
                    exit;
                }
        }
        break;
    default:
        $rows = $cust->getCUNOList();
        $report = new tldReportColumnar($rows,
            array(
                "xItems"=>array(
                    "id"		=>"ID#",
                    "erp"		=>"ERP",
                    "cuno"		=>"ERP Customer#"
                ),
                "title"=>"Select ERP and Customer ...",
                "links"=>array(
                    "id"=>array("url"=>"$php_self?m[0]=customers&m[1]=view&m[2]=zip&m[3]=download&id=$id",
                        "params"=>array("cuno"=>"cuno","erp"=>"erp")))
            )
        );
        $body .= $report->fetch();
        break;
}


?>

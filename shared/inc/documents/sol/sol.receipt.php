<?php

//get ASM from SSO

$summary = new tldAssocTable(
    $data["header"],
    [
        "buyer_customer_display" => "Buyer Name",
        "user_customer_display" => "End User Name",
        "sor_cu_orno" => "Customer PO#",
        "id" => "SOL#",
        "sor_juridical_entity" => "Legal entity",
        "sor_asm_fullname" => "Area Sales Manager",
        "sor_eqno" => "eQuote#",
        "sor_orno" => "SSO SO#",
        "model" => "Model",
        "qty_sou" => "Quantity",
        "dcur" => "Currency",
        "sum_pris_unit" => "Unit Gross Selling Price",
        "sum_pris_xtot" => "Total Gross Selling Price",
        "inco" => "Inco Terms",
        "inco_loc" => "Inco Location",
    ],
    ["plain" => "tld"]
);

$options = new tldReportColumnar(
    $data["options"],
    [
        "xItems" => [
            "caty" => "Category",
            "dsca" => "Description - Options"
        ],
        "showItemNumbers" => TRUE,
        "sortable" => "no"
    ]
);

$delivery = new tldAssocTable(
    $data["header"],
    [
        "delivery_address" => "Delivery address"
    ],
    ["plain" => "tld"]
);

$equipment = new tldReportColumnar(
    $data["er"],
    [
        "xItems" => [
            "sn" => "SN#",
            "del_dat" => "Requested EXW Delivery Date",
            "ddel_asm" => "Promised EXW Delivery Date"
        ],
        "sortable" => "no"
    ]
);

$payment = new tldAssocTable(
    $data["header"],
    [
        "tpay" => "Payment Terms",
        "dcur" => "Currency",
        "dp_amt" => "Down Payment Amount",
        "receivedp_amt" => "Received Down Payment Amount"
    ],
    ["plain" => "tld"]
);

$warranty = new tldAssocTable(
    $data['header'],
    ['wrty_spec' => 'Warranty conditions'],
    ['plain' => 'tld']
);

ob_start();
?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style type="text/css">
            body, td, th {
                font-family: Helvetica, Arial, sans-serif;
                font-size: 15px;
                line-height: 21px;
                color: black;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-row-group;
            }

            tr {
                page-break-inside: avoid;
            }

            #page {
                background: white;
                background-color: white;
            }

            small {
                line-height: 14px;
            }

            a, a:visited {
                text-decoration: none;
            }

            a:hover {
                color: #FF9933;
            }

            .terms {
                font-size: 10px;
            }

            .item_info_title {
                background: #5475a0;
                color: white;
                font-size: 17px;
                text-align: center;
            }

            .smallwhite {
                color: white;
            }

            .item_reps_title {
                font-size: 17px;
                text-align: center;
            }

            .item_info_table_label {
                background: #e0e0e0;
                padding: 3px;
            }

            .item_info_table_value {
                background: #f4f4f4;
                padding: 3px;
            }

            .item_dear {
                font-weight: normal;
                font-size: 21px;
                color: black;
                margin: 0, 0, 4px, 0;
                padding: 0px;
            }

            .survey_msg {
                color: red;
            }

            p{
                text-align: justify;
            }

        </style>
    </head>
    <body>
    <div id="page" style="padding:0;">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <table border="0" cellspacing="0" cellpadding="20" align="center">
                        <tr>
                            <td align="left" valign="top" bgcolor="#FFFFFF">
                                <img src="https://www.tld-gse.com/shared/tld_logos/tld-1inch.jpg" alt="TLD"
                                     width="100" height="60" border="0">
                                <br><br><br>
                                <p>On, <?= date('Y-m-d'); ?></p>
                                <p class="item_dear">Dear Customer,</p>
                                <p>The purpose of this message is to officially acknowledge receipt of your order per the details indicated below.</p>
                                <p class="item_info_title"><strong>Order summary</strong></p>
                                <?= mb_convert_encoding($summary->fetch(), 'UTF-8', mb_list_encodings()); ?><?php if(empty($summary)) echo 'Unknown'; ?>
                                <br>
                                <?= mb_convert_encoding($options->fetch(), 'UTF-8', mb_list_encodings()); ?><?php if(empty($options)) echo 'Unknown'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" valign="top" bgcolor="#FFFFFF">
                                <p class="item_info_title"><strong>Key Delivery Dates</strong></p>
                                <?= mb_convert_encoding($delivery->fetch(), 'UTF-8', mb_list_encodings()); ?><?php if(empty($delivery)) echo 'Unknown'; ?>
                                <br>
                                <?= mb_convert_encoding($equipment->fetch(), 'UTF-8', mb_list_encodings()); ?><?php if(empty($equipment)) echo 'Unknown'; ?>
                                <br>
                                <p>EXW should be considered as from TLD factory location.
                                    <br>
                                    TLD commitment is the Promised EXW Delivery Date. In case the Promised EXW Delivery Date is beyond the Requested
                                    EXW Delivery Date, TLD will aim to reduce the delay between the two dates.</p>
                            </td>
                        </tr>
                        <?php if (!empty($data['header']['wrty_spec'])) : ?>
                        <tr>
                            <td align="left" valign="top" bgcolor="#FFFFFF">
                                <p class="item_info_title"><strong>Warranty conditions</strong></p>
                                <?= mb_convert_encoding($warranty->fetch(), 'UTF-8', mb_list_encodings()); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td align="left" valign="top" bgcolor="#FFFFFF">
                                <p class="item_info_title"><strong>Important Payment Information</strong></p>
                                <?= mb_convert_encoding($payment->fetch(), 'UTF-8', mb_list_encodings()); ?><?php if(empty($payment)) echo 'Unknown'; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table border="0" cellspacing="0" cellpadding="20" align="center"
                           style="page-break-inside: avoid">
                        <tr>
                            <td align="left" valign="top" bgcolor="#FFFFFF">
                                <p>In case the Buyer Name would need to be changed subsequently to this order acknowledgement,
                                    TLD reserves the right to refuse the modification, cancel the order and/or charge an administrative fee for the modification.</p>

                                <p>If you are financing this order, TLD reserves the right not to launch production until the financing documentation is provided.
                                    The Promised EXW Delivery Date will be modified accordingly. The financing documentation will need to be according to
                                    TLD standards, and typically would be a purchase order issued by the financing entity to TLD or a financing contract between
                                    buyer and financing entity clearly mentioning the covered PO.</p>

                                <p>A late payment interest rate will be charged at 1 % per month.
                                    Late payment may also postpone the Promised EXW Delivery Date.</p>

                                <p>Please review the above information carefully. In case of discrepancy between the purchase order and this document, this
                                    document will prevail. Please contact your sales representative urgently by phone or by email, if you find any incorrect data.
                                    <br>
                                    Beyond 72 hours, these conditions will be deemed accepted by you and binding on both parties.</p>

                            <p>Best regards,</p>
                            <?= $data['asm']['fullname'] ?><br>
                            <?= $data['asm']['email'] ?><br>
                            <?= $data['asm']['direct_phone'] ?><br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    </body>
    </html>
<?php
return ob_get_clean();
?>

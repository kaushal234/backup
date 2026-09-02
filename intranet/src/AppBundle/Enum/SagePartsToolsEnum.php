<?php

declare(strict_types=1);

namespace AppBundle\Enum;

use Symfony\Component\Routing\RouterInterface;

final readonly class SagePartsToolsEnum
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function getList(): array
    {
        return [
            'common_tools' => [
                [
                    'key' => 'crystal_reports',
                    'title' => 'sage_parts_tools.titles.crystal_reports',
                    'description' => 'Various reports for different business units created in Crystal Reports. <br>
                                  Reports can be accessed from Windows Explorer on the U: drive',
                    'link' => '',
                ],
                [
                    'key' => 'currency_converter',
                    'title' => 'sage_parts_tools.titles.currency_converter',
                    'description' => 'Currency Converter (based on exchange rates in P21)',
                    'link' => 'http://portaltools/Tools/ExchangeRate/ExchangeRate.aspx',
                ],
                [
                    'key' => 'email_archive_system',
                    'title' => 'sage_parts_tools.titles.email_archive_system',
                    'description' => "This system contains an archive of all emails sent or received from your email box. <br>
                                  Please refer to the User Guide located here for more information <br>
                                  S:\Inter-Department Share\IT Files\Computer Procedures\Email\How to use the Mail Archive System.docx",
                    'link' => 'http://emailarchive/',
                ],
                [
                    'key' => 'helpdesk',
                    'title' => 'sage_parts_tools.titles.helpdesk',
                    'description' => "Use this for reporting all computer related problems and requests.<br>
                                  Refer to the Policies and Procedures folder lacated at the following location for the proper use of the Helpdesk system: <br>
                                  S:\Inter-Department Share\IT Files\Computer Procedures\Helpdesk",
                    'link' => 'http://helpdesk/',
                ],
                [
                    'key' => 'item_maintenance_lite',
                    'title' => 'sage_parts_tools.titles.item_maintenance_lite',
                    'description' => 'A subset of the Item Maintenance functions in Commerce Center.',
                    'link' => 'http://portaltools/tools/Apps/ItemMaintenanceLite/ItemMaintenance.application',
                ],
                [
                    'key' => 'p21_docman_barcode_emails_files',
                    'title' => 'sage_parts_tools.titles.p21_docman_barcode_emails_files',
                    'description' => "This program is used to place a barcode on an email or pdf file for use with the P21 document management system.<br>
                                  You can find the procedure for this here: <br>
                                  S:\Inter-Department Share\IT Files\Computer Procedures\P21\Docman Procedure - Barcoding emails and PDFs.docx",
                    'link' => 'http://portaltools/tools/Apps/TransBarcode2Docman/TransBarcode2Docman.Client.application',
                ],
                [
                    'key' => 'ssrs_reports',
                    'title' => 'sage_parts_tools.titles.ssrs_reports',
                    'description' => 'Various reports for different business units created in SSRS',
                    'link' => 'https://ssrs01.sageparts.com/Reports/',
                ],
            ],
            'customer_service' => [
                [
                    'key' => 'air_canada_fleet_lookup',
                    'title' => 'sage_parts_tools.titles.air_canada_fleet_lookup',
                    'description' => 'Used to lookup information on a particular unit # or Alternate # from a static table provided by Air Canada.<br>
                                  (This table is not being updated).',
                    'link' => 'http://portaltools/tools/acfleetlookup/acfleetlookup.aspx',
                ],
                [
                    'key' => 'air_canada_part_lookup',
                    'title' => 'sage_parts_tools.titles.air_canada_part_lookup',
                    'description' => "Part lookup tool which allows a search for info on Air Canada Part #'s from a static table provided by Air Canada. <br>
                                  (This table is not being updated)",
                    'link' => 'http://portaltools/tools/ACPartLookup/ACPartLookup.aspx',
                ],
                [
                    'key' => 'amazon_bulk_uploader',
                    'title' => 'sage_parts_tools.titles.amazon_bulk_uploader',
                    'description' => 'This tool creates the files required to bulk upload invoices to Amazon.',
                    'link' => 'http://portaltools/tools/Apps/AmazonBulkUpload/AmazonBulkUpload.application',
                ],
                [
                    'key' => 'corrective_action_request',
                    'title' => 'sage_parts_tools.titles.corrective_action_request',
                    'description' => "Used to document and track customer inquiries and track resolution time for QC related issues.<br>
                                  This application can be found here: S:\Inter-Department Share\SageTools\CARLauncher\CARLauncher",
                    'link' => '',
                ],
                [
                    'key' => 'e_sage_error_reporting_tool',
                    'title' => 'sage_parts_tools.titles.e_sage_error_reporting_tool',
                    'description' => 'This tool can be used to show errors that occurred during the eSage order integration process.',
                    'link' => 'http://portaltools/tools/IntegrationQueueManager/default.aspx',
                ],
                [
                    'key' => 'e_sage_resend_tool',
                    'title' => 'sage_parts_tools.titles.e_sage_resend_tool',
                    'description' => "This application will allow you to edit eSage orders that failed to import into the Prophet 21 system.<br>
                                  The procedure for how to use this tool can be found here <br>
                                  S:\Inter-Department Share\Manuals On-Line\eSage Information\eSage - P21 Integration\eSage Integration - Handling Pricing Errors.doc",
                    'link' => 'http://portaltools/tools/eSageToP21/resend.aspx',
                ],
                [
                    'key' => 'image_search_tool',
                    'title' => 'sage_parts_tools.titles.image_search_tool',
                    'description' => 'Used to search for images, drawings, and reference cards for parts.',
                    'link' => 'https://portaltools.sageparts.com/SageApps/CardFile',
                ],
                [
                    'key' => 'item_maintenance_lite',
                    'title' => 'sage_parts_tools.titles.item_maintenance_lite',
                    'description' => 'A subset of the Item Maintenance functions in Commerce Center. <br>
                                  This program can be found on your Start Menu in the Programs folder.',
                    'link' => 'http://portaltools/tools/Apps/ItemMaintenanceLite/ItemMaintenance.application',
                ],
                [
                    'key' => 'open_orders_report',
                    'title' => 'sage_parts_tools.titles.open_orders_report',
                    'description' => 'Used to show Open Orders',
                    'link' => 'http://portaltools/tools/openorders/openorders.aspx',
                ],
                [
                    'key' => 'open_rma_report',
                    'title' => 'sage_parts_tools.titles.open_rma_report',
                    'description' => "Used to show Open RMA's",
                    'link' => 'http://portaltools/tools/openorders/openrma.aspx',
                ],
                [
                    'key' => 'p21_docman_barcode_emails_files',
                    'title' => 'sage_parts_tools.titles.p21_docman_barcode_emails_files',
                    'description' => "This program is used to place a barcode on an email or pdf file for use with the P21 Docman system.<br>
                                  You can find the procedure for this here: <br>
                                  S:\Inter-Department Share\IT Files\Computer Procedures\P21\Docman Procedure - Barcoding emails and PDFs.docx",
                    'link' => 'http://portaltools/tools/Apps/TransBarcode2Docman/TransBarcode2Docman.Client.application',
                ],
                [
                    'key' => 'sage_order_lookup',
                    'title' => 'sage_parts_tools.titles.sage_order_lookup',
                    'description' => 'Used to lookup orders based on data stored in Ref1, Ref2, Unit#, Source ID (esage order #), or Note field.',
                    'link' => 'http://portaltools/tools/sageorderlookup/sageorderlookup.aspx',
                ],
                [
                    'key' => 'unit_history_lookup',
                    'title' => 'sage_parts_tools.titles.unit_history_lookup',
                    'description' => 'This tool is used to lookup the history of items ordered for a unit, or a vehicle make/model, or an Inventory Code.',
                    'link' => 'https://portaltools.sageparts.com/SageApps/UnitHistory',
                ],
                [
                    'key' => 'gwf_11372',
                    'title' => 'sage_parts_tools.titles.bid_request',
                    'description' => 'Request to Match parts off lists to P21 for submission to customer with current pricing.',
                    'link' => $this->gwfLink(11372),
                ],
                [
                    'key' => 'gwf_11399',
                    'title' => 'sage_parts_tools.titles.cost_updates',
                    'description' => 'Match parts off supplier price list and upload new prices into P21.',
                    'link' => $this->gwfLink(11399),
                ],
                [
                    'key' => 'gwf_11400',
                    'title' => 'sage_parts_tools.titles.primary_supplier_changes',
                    'description' => 'Adjust primary suppliers in P21, and import new items into P21.',
                    'link' => $this->gwfLink(11400),
                ],
                [
                    'key' => 'gwf_11401',
                    'title' => 'sage_parts_tools.titles.adhoc_reports',
                    'description' => 'Request to create a new report or analysis.',
                    'link' => $this->gwfLink(11401),
                ],
            ],
            'finance' => [
                [
                    'key' => 'certify_downloader',
                    'title' => 'sage_parts_tools.titles.certify_downloader',
                    'description' => 'Downloads Expenses from Certify and generates import files.',
                    'link' => 'http://portaltools/tools/Apps/CertifyTransactionProcessor/CertifyTransactionProcessor.application',
                ],
                [
                    'key' => 'edicom_queue_manager',
                    'title' => 'sage_parts_tools.titles.edicom_queue_manager',
                    'description' => 'Used to release invoices to the SUNAT',
                    'link' => 'http://portaltools/tools/Apps/ediCOM/ediCOM.Client.application',
                ],
                [
                    'key' => 'europe_eft',
                    'title' => 'sage_parts_tools.titles.europe_eft',
                    'description' => 'Bank of America and Nat West',
                    'link' => 'http://portaltools/tools/APPS/EuropeEFT/EuropeEFT.application',
                ],
                [
                    'key' => 'hsbc_v_card_uploader',
                    'title' => 'sage_parts_tools.titles.hsbc_v_card_uploader',
                    'description' => 'Uploads P21VCards to HSBC',
                    'link' => 'http://portaltools/tools/Apps/HSBC_VCard_Uploader/HSBC_VCard_Uploader.application',
                ],
                [
                    'key' => 'inter_company_transfer_invoice_lookup',
                    'title' => 'sage_parts_tools.titles.inter_company_transfer_invoice_lookup',
                    'description' => 'Used to lookup the invoice number of a given inter-company transfer number.',
                    'link' => 'http://portaltools/tools/transferinvoicelookup/transferinvoicelookup.aspx',
                ],
                [
                    'key' => 'invoice_exporter',
                    'title' => 'sage_parts_tools.titles.invoice_exporter',
                    'description' => 'Export Invoices from DocMan.',
                    'link' => 'http://portaltools/tools/Apps//DocManExporter/DocMan.Exporter.application',
                ],
                [
                    'key' => 'i_payables_export',
                    'title' => 'sage_parts_tools.titles.i_payables_export',
                    'description' => 'This application is used to export invoice data to a .csv file for use with uploading to the IPayables website.',
                    'link' => 'http://portaltools/tools/IPayables_Export_Live/Export.aspx',
                ],
                [
                    'key' => 'menzies_invoice_po',
                    'title' => 'sage_parts_tools.titles.menzies_invoice_po',
                    'description' => 'Adds custom P.O.# prefix to invoice_hdr.po_no for Customer 134975 and 134991',
                    'link' => 'http://portaltools/tools/IPayables_Export_Live/Export.aspx',
                ],
                [
                    'key' => 'p21_check_linking',
                    'title' => 'sage_parts_tools.titles.p21_check_linking',
                    'description' => "This application is used to link reprinted checks to vouchers in P21. <br>
                                  The procedure for using this application is located here: <br>
                                  S:\Inter-Department Share\IT Files\Computer Procedures\P21\Check Linking Procedure.docx",
                    'link' => 'http://portaltools/tools/Apps/Checks2Vouchers/Checks2Vouchers.Client.application',
                ],
                [
                    'key' => 'receiving_ap_discrepancies',
                    'title' => 'sage_parts_tools.titles.receiving_ap_discrepancies',
                    'description' => "Used to track Receiving and AP Discrepancies.<br>
                                  This application can be found here: S:\Inter-Department Share\SageTools\Receiving_Discrepancies",
                    'link' => '',
                ],
            ],
            'logistics_and_freight' => [
                [
                    'key' => 'blindscan',
                    'title' => 'sage_parts_tools.titles.blindscan',
                    'description' => 'Pick Ticket Verification',
                    'link' => 'http://portaltools/tools/Apps/Blindscan/Blindscan.application',
                ],
                [
                    'key' => 'freight_management_system',
                    'title' => 'sage_parts_tools.titles.freight_management_system',
                    'description' => 'Add a new freight bill',
                    'link' => 'http://portaltools2/Tools/FreightManagementSystem/',
                ],
            ],
            'operations' => [
                [
                    'key' => 'add_employee_mechanic',
                    'title' => 'sage_parts_tools.titles.add_employee_mechanic',
                    'description' => "This application allows you to add an Employee/Mechanic for a customer so that it appears for that customer in the pop-up application in Commerce Center.<br>
                                  For a more detailed procedure on how to use this application go here <br>
                                  S:\Inter-Department Share\IT Files\Computer Procedures\Sage Applications\Add Mechanic Procedure.doc",
                    'link' => 'http://portaltools/tools/addmechanic/addmechanic.aspx',
                ],
                [
                    'key' => 'barcode_label_printing',
                    'title' => 'sage_parts_tools.titles.barcode_label_printing',
                    'description' => "Used to print barcode labels for all locations. For a more detailed procedure on how to use this application please view the document here:<br>
                                  S:\Inter-Department Share\IT Files\Computer Procedures\Sage Applications\Sage Label Printer.docx",
                    'link' => 'http://portaltools/tools/Apps/SageLabelPrinter/Sage.LabelPrinter.application',
                ],
                [
                    'key' => 'blindscan',
                    'title' => 'sage_parts_tools.titles.blindscan',
                    'description' => 'Pick Ticket Verification',
                    'link' => 'http://portaltools/tools/Apps/Blindscan/Blindscan.application',
                ],
                [
                    'key' => 'corrective_action_request',
                    'title' => 'sage_parts_tools.titles.corrective_action_request',
                    'description' => "Used to document and track customer inquiries and track resolution time for QC related issues. <br>
                                  This application can be found here:<br>
                                  S:\Inter-Department Share\SageTools\CARLauncher\CARLauncher",
                    'link' => '',
                ],
                [
                    'key' => 'cycle_count_tool',
                    'title' => 'sage_parts_tools.titles.cycle_count_tool',
                    'description' => 'Used to manage cycle counts.',
                    'link' => 'http://portaltools/tools/Apps/CycleCount/CycleCount.application',
                ],
                [
                    'key' => 'cycle_count_tool_agsa',
                    'title' => 'sage_parts_tools.titles.cycle_count_tool_agsa',
                    'description' => 'Used to manage cycle counts.',
                    'link' => 'http://portaltools/tools/Apps/CycleCountAGSA/CycleCount_AGSA.application',
                ],
                [
                    'key' => 'item_maintenance_lite',
                    'title' => 'sage_parts_tools.titles.item_maintenance_lite',
                    'description' => 'A subset of the Item Maintenance functions in Commerce Center.',
                    'link' => 'http://portaltools/tools/Apps/ItemMaintenanceLite/ItemMaintenance.application',
                ],
                [
                    'key' => 'pick_ticket_update',
                    'title' => 'sage_parts_tools.titles.pick_ticket_update',
                    'description' => 'Allows for a pick ticket to be updated with Tracking # and Shipping Instructions.',
                    'link' => 'https://portaltools.sageparts.com/SageApps/update',
                ],
                [
                    'key' => 'pick_ticket_update_agsa',
                    'title' => 'sage_parts_tools.titles.pick_ticket_update_agsa',
                    'description' => 'Allows for a pick ticket to be updated with Tracking # and Shipping Instructions.',
                    'link' => 'https://portaltools.sageparts.com/SageApps/update',
                ],
                [
                    'key' => 'receiving_ap_discrepancies',
                    'title' => 'sage_parts_tools.titles.receiving_ap_discrepancies',
                    'description' => "Used to track Receiving and AP Discrepancies. This application can be found here: <br>
                                  S:\Inter-Department Share\SageTools\Receiving_Discrepancies\Receiving_Discrepancies",
                    'link' => 'S:\Inter-Department Share\SageTools\Receiving_Discrepancies\Receiving_Discrepancies.mde',
                ],
                [
                    'key' => 'receiving_ap_discrepancies_x64',
                    'title' => 'sage_parts_tools.titles.receiving_ap_discrepancies_x64',
                    'description' => "Used to track Receiving and AP Discrepancies (for 64bit MSaccess or from new laptops) This application can be found here:<br>
                                  S:\Inter-Department Share\SageTools\Receiving_Discrepancies\Receiving_Discrepancies64",
                    'link' => 'S:\Inter-Department Share\SageTools\Receiving_Discrepancies\Receiving_Discrepancies64.mde',
                ],
                [
                    'key' => 'resend_tool',
                    'title' => 'sage_parts_tools.titles.resend_tool',
                    'description' => 'Used to handle errors with transactions submitted to various customer interfaces: AA-Datastream/Fleetfocus, GSEL, Delta, Fedex.',
                    'link' => 'http://portaltools/tools/InvoiceResend/WebForms/index.aspx',
                ],
                [
                    'key' => 'return_pick_ticket_update',
                    'title' => 'sage_parts_tools.titles.return_pick_ticket_update',
                    'description' => 'Allows for a Return pick ticket to be updated with Tracking # and Carrier',
                    'link' => 'https://portaltools.sageparts.com/SageApps/update',
                ],
                [
                    'key' => 'sage_truck_package_scan',
                    'title' => 'sage_parts_tools.titles.sage_truck_package_scan',
                    'description' => "Application used to scan packages onto truck prior to delivery.<br>
                                  This application can be found here:<br>
                                  S:\Inter-Department Share\SageTools\SageTruckPackageScan\SageTruckPackageScan",
                    'link' => '',
                ],
                [
                    'key' => 'serial_number_lookup',
                    'title' => 'sage_parts_tools.titles.serial_number_lookup',
                    'description' => 'Lookup Serial Number History',
                    'link' => 'http://portaltools/tools/serialnumberlookup/serialnumberlookup.aspx',
                ],
                [
                    'key' => 'stock_status_monitor',
                    'title' => 'sage_parts_tools.titles.stock_status_monitor',
                    'description' => 'Used to monitor status of inventory by Product Group and Purchase Class for each location.',
                    'link' => 'https://portaltools.sageparts.com/SageApps/StockStatus/status',
                ],
                [
                    'key' => 'summ_data_import',
                    'title' => 'sage_parts_tools.titles.summ_data_import',
                    'description' => 'Imports data into the Sage Unit Maintenance Module',
                    'link' => 'http://portaltools/tools/Apps/SUMMImport/ImportData.application',
                ],
                [
                    'key' => 'transfer_update_tool',
                    'title' => 'sage_parts_tools.titles.transfer_update_tool',
                    'description' => 'Allows transfers to be updated with tracking #',
                    'link' => 'https://portaltools.sageparts.com/SageApps/update',
                ],
                [
                    'key' => 'warranty_processing',
                    'title' => 'sage_parts_tools.titles.warranty_processing',
                    'description' => 'Used to search for and update additional warranty related information on parts sold.',
                    'link' => 'http://portaltools/tools/sage_warranty_processing/sage_warranty_processing.aspx',
                ],
            ],
            'purchasing' => [
                [
                    'key' => 'critical_parts_request_form',
                    'title' => 'sage_parts_tools.titles.critical_parts_request_form',
                    'description' => 'Critical Parts Request Form.',
                    'link' => 'http://portaltools/tools/Apps/StockRequest/StockRequest.Client.application',
                ],
                [
                    'key' => 'inventory_trend_application',
                    'title' => 'sage_parts_tools.titles.inventory_trend_application',
                    'description' => 'Determines inventory trends based on supplier id, discount group or ship to',
                    'link' => 'http://portaltools/tools/Apps/InventoryTrend/InventoryTrend.application',
                ],
                [
                    'key' => 'item_maintenance_lite',
                    'title' => 'sage_parts_tools.titles.item_maintenance_lite',
                    'description' => 'A subset of the Item Maintenance functions in Commerce Center',
                    'link' => 'http://portaltools/tools/Apps/ItemMaintenanceLite/ItemMaintenance.application',
                ],
                [
                    'key' => 'p21_docman_barcode_emails_files',
                    'title' => 'sage_parts_tools.titles.p21_docman_barcode_emails_files',
                    'description' => 'This program is used to place a barcode on an email or pdf file for use with the P21 Docman system.<br>
                                  Here is the procedure for using this application:<br>
                                  http://sageportal/Departments/IT/Shared%20Documents/Policies%20and%20Procedures/P21/Docman%20Procedure%20-%20Barcoding%20emails%20and%20PDFs.docx',
                    'link' => 'http://portaltools/tools/Apps/TransBarcode2Docman/TransBarcode2Docman.Client.application',
                ],
                [
                    'key' => 'po_spend_report_generator',
                    'title' => 'sage_parts_tools.titles.po_spend_report_generator',
                    'description' => 'Generates PO Spend Reports.',
                    'link' => 'http://portaltools/tools/Apps/POSpendReportGenerator/POSpendReportGenerator.application',
                ],
                [
                    'key' => 'purchasing_savings_tracker',
                    'title' => 'sage_parts_tools.titles.purchasing_savings_tracker',
                    'description' => 'Purchasing Performance & Savings Tracker.',
                    'link' => 'http://portaltools/tools/Apps/PurchaseTracking/PurchaseTracking.Client.application',
                ],
                [
                    'key' => 'stock_status_monitor',
                    'title' => 'sage_parts_tools.titles.stock_status_monitor',
                    'description' => 'Used to monitor status of inventory by Product Group and Purchase Class for each location.',
                    'link' => 'http://portaltools/tools/StockStatusMonitor/summary.aspx',
                ],
            ],
        ];
    }

    public function getByCategory(string $category): array
    {
        $list = $this->getList();

        return $list[$category] ?? [];
    }

    private function gwfLink(int $id): string
    {
        return $this->router->generate('legacy_calendar', [
            'm' => ['gwf', 'view'],
            'id' => $id,
        ]);
    }
}

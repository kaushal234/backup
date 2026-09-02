<?php
use ApiBundle\Client;

class ApiLogger
{
    public static function log(object $csr, object $customerServiceRecord)
    {
        $textLog = "CSR billing updated:";
        $fieldLog = self::generateChangeListLog([
            'erp_inv' => 'ERP invoice#',
            'bill_to' => 'Bill to',
            'bill_instruction' => 'Billing instruction'
        ], $csr->itsHeader, $csr->getHeader());

        if ($fieldLog) {
            global $kernel;
            $container = $kernel->getContainer();

            $client = $container->get(Client::class);

            $client->save('comments', [
                'message' => mb_convert_encoding(TldDatabase::escape($textLog . $fieldLog), 'UTF-8', mb_list_encodings()),
                'resource' => $customerServiceRecord['@id'],
            ]);
        }
    }

    public static function generateChangeListLog($fields, $DataBefore, $DataAfter)
    {
        $list = array();
        foreach ($fields as $field => $label) {
            if (!isset($DataBefore[$field]) || !isset($DataAfter[$field])) continue;
            if ($DataBefore[$field] == $DataAfter[$field]) continue;
            $list[] = "<li><b>$label</b> from {$DataBefore[$field]} to {$DataAfter[$field]}</li>";
        }
        // if nothing changed
        if (empty($list)) return FALSE;
        return "<ul>" . implode("", $list) . "</ul>";
    }
}
<?php

declare(strict_types=1);

namespace App\Job\Purchasing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'purchasing:sage:inventory')]
class SageInventoryCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Send inventory to sage through FTP';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $time_start = microtime(true);
        \define('SAGE_FTP_ADDRESS', 'ftp3.sageparts.com');  // 69.74.209.106
        \define('SAGE_FTP_USERNAME', 'ftp_tld3');
        \define('SAGE_FTP_PASSWORD', 'skt83T2!7');
        \define('SAGE_XREF_SRCFILE', 'TLD/TLDItemXref/TLDItemXref.csv');
        \define('SAGE_INV_DIR', 'TLD/EDI');
        \define('SAGE_INV_FILENAME', 'TLD_XREF_Sage_Item_Feed.xml');

        // Define TLD ERP locations array for inventory lookup
        $cunos = [
            300 => '676',
            540 => 'AC7700',
            600 => 'CS001',
            680 => '99988',
        ];

        $erps = array_keys($cunos);

        // Set debug mode
        $debug_limit = 0; // If greater than zero, then debug mode is active

        // Run processes
        $is_debug = $debug_limit > 0;
        $time = time();

        if ($is_debug) {
            $this->logger->debug('RUNNING IN DEBUG MODE');
        }

        // Process XREF
        $this->logger->info('PROCESSING XREF FILE');

        $file = file('ftp://'.SAGE_FTP_USERNAME.':'.SAGE_FTP_PASSWORD.'@'.SAGE_FTP_ADDRESS.'/'.SAGE_XREF_SRCFILE);

        if ($file) {
            $this->logger->info('DOWNLOADED FILE: ftp://'.SAGE_FTP_ADDRESS.'/'.SAGE_XREF_SRCFILE);
            $header = str_getcsv(trim(array_shift($file)));
            $xref = [];
            foreach ($file as $line) {
                if (!empty($line)) {
                    $array = [];
                    $line = str_getcsv(trim($line));
                    foreach ($header as $key => $column) {
                        $array[$column] = $line[$key];
                    }
                    $xref[trim($array['TLD_Item'])] = $array;
                }
            }

            $this->logger->info('FOUND '.\count($xref).' ITEMS IN SAGE XREF FILE');

            if ($is_debug) {
                $this->logger->debug('Shuffling');
                shuffle($xref);
            }

            if (!empty($xref)) {
                // Empty table
                \tldUtils::sqlQuery('TRUNCATE sage_xref');

                $limit = 0;
                $query = 'INSERT IGNORE INTO sage_xref (Sage_UID, TLD_Item) VALUES ';
                $values = [];
                foreach ($xref as $item) {
                    $values[] = \sprintf("('%s', '%s')", \TldDatabase::escape($item['UID']), \TldDatabase::escape(trim($item['TLD_Item'])));
                    if ($is_debug && ++$limit >= $debug_limit) {
                        break;
                    }
                }
                if ($values) {
                    $query .= implode(',', $values);
                    \tldUtils::sqlQuery($query);
                }
            }
        }

        unset($xref, $file, $header);

        // Process Daily Feed
        $this->logger->info('PROCESSING INVENTORY FILE');

        // Get Sage XREF PN
        $query = 'SELECT
            sage_xref.Sage_UID,
            sage_xref.TLD_Item,
            sage_xref_prices.data
            FROM sage_xref
            LEFT JOIN sage_xref_prices ON sage_xref.TLD_Item = sage_xref_prices.TLD_Item';

        if ($is_debug) {
            $this->logger->debug("Set limit $debug_limit in query");
            $query .= " LIMIT $debug_limit";
        }

        $partsSQL = \tldUtils::getSqlToAssocArray($query);
        $files = [];
        if (!empty($partsSQL)) {
            $parts = [];
            foreach ($partsSQL as $values) {
                $parts[$values['TLD_Item']] = $values;
            }
            $partsSQL = null;

            // Begin XML creation
            $xmlDocument = new \DOMDocument('1.0', 'UTF-8');
            $xmlDocument->formatOutput = true;

            $xmlRoot = $xmlDocument->createElement('InventoryRoot');
            $xmlDocument->appendChild($xmlRoot);

            $xmlAttribute = $xmlDocument->createAttribute('DateFormat');
            $xmlAttribute->value = 'ISO8601';
            $xmlRoot->appendChild($xmlAttribute);

            $xmlAttribute = $xmlDocument->createAttribute('DateCreated');
            $xmlAttribute->value = date('c', $time);
            $xmlRoot->appendChild($xmlAttribute);

            // Interate PNs
            $pns = [];
            foreach ($parts as $part) {
                $pns[] = \TldDatabase::escape($part['TLD_Item']);
            }

            $slice = 5000;

            $inventoriesByErp = array_fill_keys(array_values($erps), []);

            $ceil = ceil(\count($pns) / $slice);
            for ($i = 0; $i < $ceil; ++$i) {
                $pn = implode("', '", \array_slice($pns, $i * $slice, $slice));

                foreach ($erps as $erp) {
                    // get inventories by erp by batch
                    $query = "SELECT
                        RTRIM(inv.t_item) AS item,
                        SUM(ROUND(inv.t_stoc,5)) AS stoc,
                        SUM(ROUND(inv.t_ordr,5)) AS ordr,
                        SUM(ROUND(inv.t_allo,5)) AS allo,
                        RTRIM(pri.t_cwar) AS cwar,
                        RTRIM(pri.t_ncmp) AS ncmp,
                        RTRIM(itm.t_cuni) AS cuni,
                        RTRIM(edm.t_dsca) AS dsca,
                        RTRIM(dsls.t_coef) AS mipCategory
                    FROM
                        ttdinv001$erp inv
                        INNER JOIN ttiedm010400 edm ON edm.t_eitm=inv.t_item
                        LEFT JOIN ttiitm001$erp itm ON inv.t_item=itm.t_item
                        LEFT JOIN ttdsls909300 dsls ON itm.t_item = dsls.t_item
                        LEFT JOIN ttdinv016300 pri ON inv.t_cwar=pri.t_cwar
                    WHERE
                        inv.t_item IN ('$pn')
                        AND pri.t_ncmp=$erp
                        AND pri.t_prio=10
                        AND ROUND(inv.t_stoc,5)+ROUND(inv.t_ordr,5)-ROUND(inv.t_allo,5) > 0
                    GROUP BY
                    inv.t_item,
                    edm.t_dsca,
                    itm.t_cuni,
                    pri.t_cwar,
                    pri.t_ncmp,
                    dsls.t_coef";
                    $inventoriesByErp[$erp] = array_merge(
                        $inventoriesByErp[$erp],
                        (array) \tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan'])
                    );
                }
            }

            $pns = null;
            $partsInventory = [];

            // Re-organize the results by P/N
            foreach ($inventoriesByErp as $erp => $inventoryRecords) {
                foreach ($inventoryRecords as $partInfo) {
                    if (!\array_key_exists($partInfo['item'], $partsInventory)) {
                        $partsInventory[$partInfo['item']] = [
                            'dsca' => $partInfo['dsca'],
                            'mipCategory' => $partInfo['mipCategory'],
                            'inv' => [$erp => $partInfo],
                        ];
                    } else {
                        $partsInventory[$partInfo['item']]['inv'][$erp] = $partInfo;
                    }
                    unset(
                        $partsInventory[$partInfo['item']]['inv'][$erp]['item'],
                        $partsInventory[$partInfo['item']]['inv'][$erp]['mipCategory'],
                        $partsInventory[$partInfo['item']]['inv'][$erp]['dsca']
                    );
                }
            }

            $inventoriesByErp = null;
            $defaultData = ['price' => 0, 'currency' => ''];

            // Building XML
            foreach ($partsInventory as $pn => $part) {
                if (!\array_key_exists('inv', $part) || !\is_array($part['inv'])) {
                    continue;
                }
                // Populate XML Item Data
                $Item = $xmlDocument->createElement('Item');
                $xmlRoot->appendChild($Item);
                $ItemAttribute = $xmlDocument->createAttribute('MasterID');
                $ItemAttribute->value = $pn;
                $Item->appendChild($ItemAttribute);

                $ItemDescription = $xmlDocument->createElement('ItemDescription');
                $Item->appendChild($ItemDescription);
                $ItemDescription->appendChild($xmlDocument->createTextNode(htmlentities($part['dsca'], \ENT_QUOTES | \ENT_XML1, 'ISO-8859-1')));

                $SageUID = $xmlDocument->createElement('SageUID');
                $Item->appendChild($SageUID);
                $SageUID->appendChild($xmlDocument->createTextNode($parts[$pn]['Sage_UID']));

                $MipCategory = $xmlDocument->createElement('MipCategory');
                $Item->appendChild($MipCategory);
                $MipCategory->appendChild($xmlDocument->createTextNode((string) $part['mipCategory']));

                foreach ($part['inv'] as $inv) {
                    // Populate XML inventory
                    $Location = $xmlDocument->createElement('Location');
                    $Item->appendChild($Location);

                    $LocationAttribute = $xmlDocument->createAttribute('Name');
                    $LocationAttribute->value = "{$inv['ncmp']}_{$inv['cwar']}";
                    $Location->appendChild($LocationAttribute);
                    // Get OnHand
                    $OnHand = $xmlDocument->createElement('OnHand');
                    $Location->appendChild($OnHand);
                    $OnHand->appendChild($xmlDocument->createTextNode((string) $inv['stoc']));

                    $OnHandAttribute = $xmlDocument->createAttribute('Unit');
                    $OnHandAttribute->value = $inv['cuni'];
                    $OnHand->appendChild($OnHandAttribute);

                    // Get OnOrder
                    $OnOrder = $xmlDocument->createElement('OnOrder');
                    $Location->appendChild($OnOrder);
                    $OnOrder->appendChild($xmlDocument->createTextNode((string) $inv['ordr']));

                    $OnOrderAttribute = $xmlDocument->createAttribute('Unit');
                    $OnOrderAttribute->value = $inv['cuni'];
                    $OnOrder->appendChild($OnOrderAttribute);

                    // Get Allocated
                    $Allocated = $xmlDocument->createElement('Allocated');
                    $Location->appendChild($Allocated);
                    $Allocated->appendChild($xmlDocument->createTextNode((string) $inv['allo']));

                    $AllocatedAttribute = $xmlDocument->createAttribute('Unit');
                    $AllocatedAttribute->value = $inv['cuni'];
                    $Allocated->appendChild($AllocatedAttribute);
                    // Get Available
                    $Available = $xmlDocument->createElement('Available');
                    $Location->appendChild($Available);
                    $Available->appendChild($xmlDocument->createTextNode((string) ($inv['stoc'] + $inv['ordr'] - $inv['allo'])));

                    $AvailableAttribute = $xmlDocument->createAttribute('Unit');
                    $AvailableAttribute->value = $inv['cuni'];
                    $Available->appendChild($AvailableAttribute);

                    // Get Price
                    $invErp = $inv['ncmp'];
                    if (false === $data = unserialize($parts[$pn]['data'] ?? '')) {
                        $data = [$invErp => $defaultData];
                    }

                    $Price = $xmlDocument->createElement('Price');
                    $Location->appendChild($Price);

                    $text = $xmlDocument->createTextNode((string) round($data[$invErp]['price'] ?? 0, 3));
                    $Price->appendChild($text);

                    $PriceAttribute = $xmlDocument->createAttribute('Currency');

                    $PriceAttribute->value = $data[$invErp]['currency'] ?? '';
                    $Price->appendChild($PriceAttribute);
                }
            }

            // Save temp XML file
            $sage_filename = ($is_debug) ? 'DEBUG_TEST_DO-NOT-DELETE.TXT' : SAGE_INV_FILENAME;
            $this->logger->info("SAVING FILE: $sage_filename");
            $filePath = tempnam(sys_get_temp_dir(), 'SAG');
            file_put_contents($filePath, $xmlDocument->saveXML());
            $files[$sage_filename] = $filePath;
        } else {
            $this->logger->warning('NO PARTS FOUND IN SAGE XREF');
        }

        foreach ($erps as $erp) {
            $this->logger->info("PROCESSING MIP FILES FOR ERP $erp");
            $query = <<<SQL
SELECT
    RTRIM(sls032.t_item) as item,
    (SELECT RTRIM(t_dsca) FROM ttiedm010400 WHERE t_eitm=sls032.t_item) AS description,
    sls032.t_qanp as [quantity or amount],
    sls032.t_pric as price,
    (SELECT t_ccur FROM ttcmcs034$erp WHERE t_cplt='MIP') AS [sales currency],
    RTRIM(itm001.t_cups) AS [sales price unit],
    RTRIM(itm001.t_cpgs) AS [sales price group],
    CONVERT(DATE, sls032.t_stdt) as [effective date],
    CONVERT(DATE, sls032.t_tdat) as [expirity date]
FROM
    ttdsls032$erp AS sls032
    INNER JOIN ttiitm001$erp AS itm001 ON itm001.t_item=sls032.t_item
WHERE sls032.t_cpls='MIP' AND ((GETDATE() BETWEEN sls032.t_stdt AND sls032.t_tdat))
    AND (SELECT TOP 1 t_item FROM ttdsls032$erp as dup WHERE dup.t_cpls='MIP' AND (GETDATE() BETWEEN dup.t_stdt AND dup.t_tdat) AND dup.t_item=sls032.t_item AND dup.t_tdat > sls032.t_tdat ORDER BY dup.t_tdat DESC) IS NULL
SQL;
            if ($rows = \tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan'])) {
                $keys = array_keys($rows[0]);
                $xItems = array_combine($keys, $keys);

                $csv = new \tldCSV($rows, ['showTitles' => true, 'xItems' => $xItems]);
                $filePath = tempnam(sys_get_temp_dir(), 'SAG');
                $csv->save($filePath);
                $files["MIP for company $erp.csv"] = $filePath;
                unset($csv);
            }
        }

        // Get FTP connection to Sage
        if ($connect = ftp_connect(SAGE_FTP_ADDRESS)) {
            $this->logger->info('Opening FTP Connection...DONE');
        } else {
            $this->logger->error('Opening FTP Connection...FAILED');
        }

        if ($login = @ftp_login($connect, SAGE_FTP_USERNAME, SAGE_FTP_PASSWORD)) {
            $this->logger->info('Logging In FTP Connection...DONE');
        } else {
            $this->logger->error('Logging In FTP Connection...FAILED');
        }

        if ($cwd = @ftp_chdir($connect, SAGE_INV_DIR)) {
            $this->logger->info('Change FTP Directory...DONE');
        } else {
            $this->logger->error('Change FTP Directory...FAILED');
        }

        if ($passv = @ftp_pasv($connect, true)) {
            $this->logger->info('Set FTP Passive Mode...DONE');
        } else {
            $this->logger->error('Set FTP Passive Mode...FAILED');
        }

        foreach ($files as $filename => $file) {
            if ($upload = @ftp_put($connect, $filename, $file, 'xml' === pathinfo($filename, \PATHINFO_EXTENSION) ? \FTP_ASCII : \FTP_BINARY)) {
                $this->logger->info("Uploading FTP File '$filename'...DONE");
            } else {
                $this->logger->error("Uploading FTP File '$filename'...FAILED");
            }
        }

        if ($close = @ftp_close($connect)) {
            $this->logger->info('Closing FTP Connection...DONE');
        } else {
            $this->logger->error('Closing FTP Connection...FAILED');
        }
        // DONE!
        foreach ($files as $file) {
            @unlink($file);
        }

        $this->logger->info('processed in '.(microtime(true) - $time_start).' seconds');

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Pi;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Parts
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getPIDocList($erp, $cprj, $translateLanguage = 'en'): array
    {
        try {
            $customizedBillOfMaterials = $this->client->request('GET', \sprintf('ion/customized_bill_of_materials/site=%d;project=%s', $erp, $cprj),
                [
                    'query' => [
                        'depth' => 20,
                        'flatResult' => 1,
                        'itemsSignalCodeFilter' => 'DCL',
                        'itemsSignalCodeFilterMethod' => 'Equals',
                        'itemsSignalCodeAttribute' => 'itemSignalCode',
                        'productSignalCodeFilter' => 'DCL',
                        'productSignalCodeFilterMethod' => 'Equals',
                        'productSignalCodeAttribute' => 'itemSignalCode',
                        'otherLanguage' => $translateLanguage,
                    ],
                ]
            )->toArray();
        } catch (\Exception $exception) {
            error_log(\sprintf('CBOM error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            $_SESSION['message'] .= $exception->getMessage();
        }

        return $customizedBillOfMaterials['items'];
    }

    public function getPIDSchemes($erp, $cprj, $translateLanguage = 'en'): array
    {
        try {
            $schemesCodesFilter = implode('|', ['ESC', 'HSC', 'BSC', 'PRG', 'PRM', 'RTD', 'FLD']);
            $customizedBillOfMaterials = $this->client->request('GET', \sprintf('ion/customized_bill_of_materials/site=%d;project=%s', $erp, $cprj),
                [
                    'query' => [
                        'itemsSignalCodeFilter' => $schemesCodesFilter,
                        'itemsSignalCodeFilterMethod' => 'Equals',
                        'itemsSignalCodeAttribute' => 'itemSignalCode',
                        'productSignalCodeFilter' => $schemesCodesFilter,
                        'productSignalCodeFilterMethod' => 'Equals',
                        'productSignalCodeAttribute' => 'itemSignalCode',
                        'otherLanguage' => $translateLanguage,
                    ],
                ]
            )->toArray();
        } catch (\Exception $exception) {
            error_log(\sprintf('CBOM error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            $_SESSION['message'] .= $exception->getMessage();
        }

        return $customizedBillOfMaterials['items'];
    }

    public function getPIParts($erp, $pdno, $opno = null, $lang = 'en'): array
    {
        try {
            $response = $this->client->request('GET', \sprintf('ion/material_lists/site=%d;productionOrder=%s', $erp, $pdno), [
                'query' => [
                    'operation' => $opno,
                    'otherLanguage' => 'ch' === mb_strtolower($lang) ? 'zh' : mb_strtolower($lang),
                ],
            ])->toArray();
        } catch (\Exception $exception) {
            error_log(\sprintf('Material List error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            $_SESSION['message'] .= $exception->getMessage();
        }

        $materials = $response['materials'] ?? [];

        foreach ($materials as &$material) {
            if ('' !== $material['itemOtherDescription']) {
                $material['itemDescription'] = $material['itemOtherDescription'];
            }
        }

        return $materials;
    }

    public function getPIDocumentation($erp, $cprj, $opno, $pi_family, $translateLanguage = 'en'): array
    {
        try {
            $customizedBillOfMaterials = $this->client->request('GET', \sprintf('ion/customized_bill_of_materials/site=%d;project=%s', $erp, $cprj),
                [
                    'query' => [
                        'depth' => 20,
                        'itemsSignalCodeFilter' => 'I',
                        'itemsSignalCodeFilterMethod' => 'StartsWith',
                        'itemsSignalCodeAttribute' => 'itemSignalCode',
                        'productSignalCodeFilter' => 'I',
                        'productSignalCodeFilterMethod' => 'StartsWith',
                        'productSignalCodeAttribute' => 'itemSignalCode',
                        'otherLanguage' => $translateLanguage,
                    ],
                ]
            )->toArray();
        } catch (\Exception $exception) {
            error_log(\sprintf('CBOM error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            $_SESSION['message'] .= $exception->getMessage();
        }

        if (empty($customizedBillOfMaterials['items'])) {
            return [];
        }
        $documentations = $this->getDocumentations($customizedBillOfMaterials['items']);
        // ### BIJECTION --- INJECTION ####
        if ($opno < 900) {
            $opnoMax = $opno;
            $opnoMin = '9' === mb_substr((string) $opno, -1) ? $opno - 9 : $opno;
            // Get injected PNs
            $query = <<<SQL
SELECT pibi.item
FROM pi_bijection as pibi
LEFT JOIN pi_family as pif ON pif.id=pibi.family_id
WHERE operation_number>=$opnoMin and operation_number<=$opnoMax
AND pif.family='$pi_family'
AND pibi.erp=$erp
SQL;

            $pns = array_column(\tldUtils::getSqlToAssocArray($query), 'item');
            if (!$pns) {
                $pns = [];
            }
            // Filter all by $opno & $pns
            $documentations = array_filter($documentations, static function ($documentation) use ($opno, $pns) {
                return $documentation['operation'] === (string) $opno || \in_array($documentation['partNumber'], $pns, false) || ('9' === mb_substr((string) $opno, -1) && ($opno - 9 <= $documentation['operation'] && $documentation['operation'] <= $opno));
            });
        }
        // sort by operation number ASC
        usort($documentations, static function ($row1, $row2) {
            if ($row1['operation'] === $row2['operation']) {
                return 0;
            }

            return ($row1['operation'] < $row2['operation']) ? -1 : 1;
        });

        return $documentations;
    }

    private function getDocumentations(array $items): array
    {
        if ([] === $items) {
            return [];
        }
        $documentations = [];
        foreach ($items as $item) {
            $documentations[] = $this->getDocumentations($this->updateOperationOfChildrenFromParent($item['children'], $item));
        }

        return array_merge($items, ...$documentations);
    }

    private function updateOperationOfChildrenFromParent(array $children, array $parent): array
    {
        if (empty($children)) {
            return [];
        }

        $operation = $parent['operation'];
        if (1 === $parent['level'] && 0 !== $parent['customOperation']) {
            $operation = $parent['customOperation'];
        }

        $updatedChildren = [];
        foreach ($children as $child) {
            $child['operation'] = $operation;
            $updatedChildren[] = $child;
        }

        return $updatedChildren;
    }
}

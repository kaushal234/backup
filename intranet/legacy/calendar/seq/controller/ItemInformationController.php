<?php

declare(strict_types=1);

require_once 'ION/ItemBySite.php';

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ItemInformationController
{
    public function index(): JsonResponse
    {
        $inventoryErp = tldLocation::getERPByID($_POST['bu_id']);

        try {
            $itemBySite = new ItemBySite();
            $row = $itemBySite->getItem((int) $inventoryErp, $_POST['itemno']);
            $row['status'] = '1';
        } catch (ClientException) {
            $row['status'] = '0';
            $row['bu'] = $_POST['bu_id'];
            $row['itemno'] = $_POST['itemno'];
            $row['inv_erp'] = $inventoryErp;
        }

        header('Content-type: text/json');

        return new JsonResponse($row);
    }
}

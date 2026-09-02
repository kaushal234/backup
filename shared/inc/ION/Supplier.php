<?php

declare(strict_types=1);

use ApiBundle\Client;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Supplier
{
    private Client $client;

    public function __construct()
    {
        global $kernel;
        $this->client = $kernel->getContainer()->get(Client::class);
    }

    public function getSupplierByCode(string $supplierCode): array
    {
        $supplier = $this->client->get(sprintf('/ion/business_partners?code=%s', urlencode(htmlspecialchars_decode($supplierCode))));
        if (count($supplier['hydra:member']) !== 1){
            return throw new NotFoundHttpException();
        }
        return $supplier['hydra:member'][0];
    }

    public function getSuppliers(): array
    {
        return $this->client->get(sprintf('/ion/business_partners'))['hydra:member'];

    }

    public function getListSuppliersCode(): array
    {
        $suppliers = $this->getSuppliers();
        return  $this->list($suppliers, 'code', 'code');
    }

    private function list(array $originalArray, string $key = 'code', string $value = 'name') :array
    {
        $resultArray = [];
        foreach ($originalArray as $item) {
            $resultArray[$item[$key]] = $item[$value];
        }
        return $resultArray;
    }
}
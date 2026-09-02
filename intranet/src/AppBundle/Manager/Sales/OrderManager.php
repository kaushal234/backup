<?php

declare(strict_types=1);

namespace AppBundle\Manager\Sales;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use Symfony\Component\HttpFoundation\Request;

readonly class OrderManager
{
    public function __construct(private Client $client)
    {
    }

    public function buildOrder(Request $request): array
    {
        $quoteNumber = $request->attributes->get('quoteNumber');

        if ($quoteNumber) {
            $quote = $this->client->findOneBy('sales/quotes', ['quoteNumber' => $quoteNumber]);

            return $this->buildOrderFromXMLHeader($quote->xml);
        }

        if ($request->hasSession() && $request->getSession()->has('_legacy_equote_data')) {
            return $this->buildOrderFromLegacyHeader($request->getSession()->get('_legacy_equote_data'));
        }

        throw new \Exception('No quote provided');
    }

    public function buildOrderFromXMLHeader(string $header): array
    {
        $arrayXML = new \SimpleXMLElement($header);
        $header = $arrayXML->DataArea->Quotation->QuotationHeader;

        $order = [
            'equoteId' => (string) $header->Equote,
            'inforLnBusinessPartnerCode' => (string) $header->CUNO,
            'contact' => null,
            'baanOrderNumbers' => [],
            'customerPurchaseOrders' => [],
            'newCustomer' => null,
            'salesAgent' => null,
            'juridicalLocation' => null,
            'note' => null,
            'buyer' => null,
            'endUser' => null,
            'asm' => null,
            'sso' => null,
        ];

        try {
            /** @var ApiData $sso */
            $sso = $this->client->findOneBy('locations', ['erp' => (int) $header->SSO, 'capability.sso' => true]);
            $order['sso'] = $sso->toArray();
        } catch (\RangeException $e) {
            // do nothing
        }

        try {
            /** @var ApiData $user */
            $user = $this->client->findOneBy('people', ['id' => (int) $header->ASM, 'hidden' => 0, 'disabled' => 0, 'normalization_groups_override' => ['people_list']]);
            $order['asm'] = $user->toArray();
        } catch (\RangeException $e) {
            // do nothing
        }

        try {
            /** @var ApiData $buyer */
            $buyer = $this->client->findOneBy('sales/customers', ['inforLnBusinessPartnerCodes' => (string) $header->Buyer]);
            $order['buyer'] = $buyer->toArray();
        } catch (\RangeException $e) {
            // do nothing
        }

        try {
            /** @var ApiData $endUser */
            $endUser = $this->client->findOneBy('sales/customers', ['inforLnBusinessPartnerCodes' => (string) $header->EndUser]);
            $order['endUser'] = $endUser->toArray();
        } catch (\RangeException $e) {
            // do nothing
        }

        return $order;
    }

    public function buildOrderFromLegacyHeader(array $header): array
    {
        $order = [
            'equoteId' => $header['eqno'],
            'newCustomer' => 'Y' === $header['cu_new'],
            'customerPurchaseOrders' => 0 !== mb_strlen(trim((string) $header['cu_orno'])) ? [$header['cu_orno']] : [],
        ];

        try {
            /** @var ApiData $sso */
            $sso = $this->client->findOneBy('locations', ['erp' => (int) $header['bu'], 'capability.sso' => true]);
            $order['sso'] = $sso->toArray();
        } catch (\RangeException $e) {
            // do nothing
        }

        try {
            /** @var ApiData $user */
            $user = $this->client->findOneBy('people', ['legacyId' => (int) $header['asm'], 'hidden' => 0, 'disabled' => 0, 'normalization_groups_override' => ['people_list']]);
            $order['asm'] = $user->toArray();
        } catch (\RangeException $e) {
            // do nothing
        }
        try {
            /** @var ApiData $endUser */
            $endUser = $this->client->findOneBy('sales/customers', ['q' => $header['cu_nama'], 'normalization_groups_override' => ['customer_list']]);
            $order['endUser'] = $endUser->toArray();
        } catch (\RangeException $e) {
            // do nothing
        }

        if (isset($header['agnt_nama'])) {
            try {
                /** @var ApiData $salesAgent */
                $salesAgent = $this->client->findOneBy('sales/customers', ['q' => $header['agnt_nama'], 'normalization_groups_override' => ['customer_list']]);
                $order['salesAgent'] = $salesAgent->toArray();
            } catch (\RangeException $e) {
                // do nothing
            }
        }

        return $order;
    }
}

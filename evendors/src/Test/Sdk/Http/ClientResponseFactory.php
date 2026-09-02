<?php

declare(strict_types=1);

namespace App\Test\Sdk\Http;

use LogicException;
use Psl\Json;
use Psl\Str;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\String\u;

final class ClientResponseFactory
{
    private const PURCHASE_ORDER_FUTURE = [
        '@id' => '/ion/purchase_orders/1',
        '@type' => 'PurchaseOrder',
        'orderIdentifier' => '1',
        'purchaseOfficeCode' => '1',
        'orderTypeCode' => '1',
        'buyFromSupplierCode' => '1',
        'reference1' => 'some referenceA',
        'reference2' => 'some referenceB',
        'orderDatetime' => '2022-08-05',
        'plannedReceiptDate' => '2022-08-05',
        'lines' => [[
            '@id' => '/ion/purchase_orders/1/lines/1',
            '@type' => 'PurchaseOrderLine',
            'lineIdentifier' => '1',
            'sequence' => 1,
            'itemCode' => 'B12',
            'supplierItemCode' => null,
            'description' => 'description object',
            'engineeringItemRevision' => 'REVISED',
            'quantity' => [
                '@id' => '/ion/quantity/1',
                '@type' => 'Quantity',
                'value' => 1,
                'unitOfMeasure' => 'EA',
            ],
            'price' => [
                '@id' => '/ion/amount/1',
                '@type' => 'Amount',
                'value' => 1.1,
                'currency' => 'USD',
                'unitOfMeasure' => 'ea',
            ],
            'late' => true,
            'unconfirmed' => true,
            'toBeDeliveredWithin7Days' => false,
            'lineText' => [],
            'backOrderQuantity' => [
                '@id' => '/ion/quantity/1',
                '@type' => 'Quantity',
                'value' => 1,
                'unitOfMeasure' => 'EA',
            ],
            'plannedReceiptDate' => '2022-08-05',
            'confirmedSupplierDate' => '2022-08-05',
            'site' => 500,
            'project' => null,
            'engineeringRevisionEffectiveDate' => null,
            'engineeringRevisionExpiryDate' => null,
            'isToCancel' => true,
            'canceled' => false,
            'expired' => false,
            'isConfirmable' => true,
            'lineState' => 'to be canceled',
        ]],
        'activity' => [],
        'status' => ['PENDING'],
    ];

    private const DMS = [
        '@id' => '/dms/1',
        '@type' => 'dms',
        'id' => 13,
        'legacyId' => 130,
        'title' => 'some_title',
        'subject' => 'some_subject',
        'description' => 'some_description',
        'type' => 'some_type',
        'language' => 'en',
        'portal' => 'some_portal',
        'owner' => [
            '@id' => '/person/3',
            'username' => 'some_username',
            'email' => 'some_email',
            'firstname' => 'some_firstname',
            'lastname' => 'some_lastname',
        ],
    ];

    private const REQUEST_FOR_QUOTATION_FUTURE = [
        '@id' => '/ion/rfq/1',
        '@type' => 'RequestForQuotation',
        'site' => self::SITE,
        'requestForQuotationCode' => '123FOO',
        'buyerEmail' => 'foo@example.com',
        'responseDate' => '23-03-2023',
        'businessPartnerCodes' => ['XXXX'],
        'status' => 'responded',
    ];

    private const LOCATION = [
        '@id' => '/locations/1',
        '@type' => 'Location',
        'name' => 'SOR',
        'erp' => 1,
        'currency' => [
            '@id' => '/currency/1',
            '@type' => 'Currency',
            'id' => 2,
            'name' => 'EUR',
        ],
    ];

    private const NCR_VENDOR_WARRANTY_CLAIM = [
        '@id' => '/vendorwarrantyclaims/1',
        '@type' => 'NcrVendorWarrantyClaim',
        'nonConformity' => [
            '@id' => '/nonconformities/1',
            '@type' => 'NonConformity',
            'id' => 1,
            'parts' => [],
        ],
        'type' => [
            '@id' => '/vendorwarrantyclaimtypes/1',
            '@type' => 'VendorWarrantyClaimType',
            'name' => 'some name',
            'description' => 'some description',
        ],
        'id' => 1,
        'status' => 'VENDOR_TO_RESPOND',
        'statusUpdatedAt' => '2022-08-04',
        'createdAt' => '2022-08-04',
        'requestedSupplierAction' => 'some requestedSupplierAction',
        'requestedCreditAmount' => 15,
        'actualCreditAmount' => 1,
        'scarRequested' => true,
        'supplierName' => 'some supplierName',
        'supplierNumber' => 'some supplierNumber',
        'supplierErp' => 316,
        'parts' => [],
        'assignee' => null,
        'location' => self::LOCATION,
        'activity' => [],
        'vendorToRespondAt' => '1990-08-11',
    ];

    private const WC_VENDOR_WARRANTY_CLAIM = [
        '@id' => '/vendorwarrantyclaims/1',
        '@type' => 'WcVendorWarrantyClaim',
        'id' => 1,
        'status' => 'VENDOR_TO_RESPOND',
        'type' => [
            '@id' => '/vendorwarrantyclaimtypes/1',
            '@type' => 'VendorWarrantyClaimType',
            'name' => 'some name',
            'description' => 'some description',
        ],
        'warrantyClaimId' => 1,
        'requestedSupplierAction' => 'some requestedSupplierAction',
        'statusUpdatedAt' => '2022-08-05',
        'createdAt' => '2022-08-05',
        'requestedCreditAmount' => 10,
        'actualCreditAmount' => 11,
        'supplierName' => 'some supplierName',
        'supplierNumber' => 'some supplierNumber',
        'supplierErp' => 314,
        'parts' => [[
            '@id' => '/vendorwarrantyclaimparts/1',
            '@type' => 'VendorWarrantyClaimPart',
            'id' => 1,
            'serialNumber' => 'XXXX',
            'vendorPartNumber' => 'vendorPartNumber',
            'vendorSerialNumber' => 'vendorSerialNumber',
            'failureType' => 'failure type',
            'failureSystem' => 'failure system',
            'ship' => true,
            'receivedQuantity' => 12,
            'createdAt' => '2022-08-05',
            'createdBy' => [
                '@id' => '/persons/1',
                'username' => 'some username',
                'email' => 'some email',
                'firstname' => 'some firstname',
                'lastname' => 'some lastname',
            ],
            'partNumber' => 'partNumber',
            'description' => 'description',
            'quantity' => 13,
            'unitOfMeasure' => 'some unitOfMeasure',
            'deletedAt' => '2022-08-05',
            'deletedBy' => [
                '@id' => '/persons/2',
                'username' => 'username',
                'email' => 'email',
                'firstname' => 'firstname',
                'lastname' => 'lastname',
            ],
        ]],
        'poster' => [
            '@id' => '/persons/2',
            'username' => 'some username',
            'email' => 'some email',
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
        ],
        'assignee' => [
            '@id' => '/persons/4',
            'username' => 'some username',
            'email' => 'some email',
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
        ],
        'location' => [
            '@id' => '/location/1',
            '@type' => 'Location',
            'name' => 'some name',
            'erp' => 315,
            'currency' => [
                '@id' => '/currencies/1',
                '@type' => 'Currency',
                'id' => 1,
                'name' => 'some currency name',
            ],
        ],
        'supplierReturnMerchandiseAuthorization' => 'some supplierReturnMerchandiseAuthorization',
        'supplierShippingInstruction' => 'some supplierShippingInstruction',
        'supplierShipperName' => 'some supplierShipperName',
        'trackingNumber' => 'some trackingNumber',
        'supplierCreditAmount' => 14,
        'supplierCreditNote' => 'some supplierCreditNote',
        'shipBackDefectivePart' => true,
        'scarRequested' => true,
        'accepted' => true,
        'activity' => [[
            '@id' => '/activities/1',
            '@type' => 'Comment',
            'resource' => 'some resource',
            'message' => 'some comment',
            'user' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'createdAt' => '2022-08-05',
            'updatedAt' => '2022-08-05',
            'public' => false,
            'metadata' => [],
            'files' => [],
        ]],
        'vendorToRespondAt' => '1990-08-11',
    ];

    private const COMMENT = [
        '@id' => '/comment/1',
        '@type' => 'Comment',
        'resource' => 'foo',
        'message' => 'hello',
        'user' => [
            '@id' => '/foo/bar',
            'username' => 'foo',
            'email' => 'foo',
            'firstname' => 'foo',
            'lastname' => 'foo',
        ],
        'createdAt' => '2022-08-05',
        'updatedAt' => '2022-05-05',
        'public' => true,
    ];

    private const SUPPLIER_CORRECTIVE_ACTION_REQUESTS = [
        '@id' => '/suppliercorrectiveactionrequest/1',
        '@type' => 'SupplierCorrectiveActionRequest',
        'id' => 1,
        'description' => 'some_description',
        'shortDescription' => 'some shortDescription',
        'issueOrigin' => 'some issueOrigin',
        'correctiveAction' => 'some correctiveAction',
        'commercialAgreement' => 'some commercialAgreement',
        'verificationDescription' => 'some verificationDescription',
        'preventiveAction' => 'some preventiveAction',
        'conclusion' => 'some conclusion',
        'mainFile' => [
            '@id' => '/suppliercorrectiveactionrequestmainfile/1',
            '@type' => 'SupplierCorrectiveActionRequestMainFile',
            'id' => 1,
            'filePath' => 'some filePath',
            'poster' => [
                '@id' => '/person/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'createdAt' => '2022-08-05',
            'description' => 'some description',
            'sha' => 'some sha',
            'mimeType' => 'some mimeType',
            'extension' => 'some extension',
            'size' => 256000,
        ],
        'createdAt' => '2022-08-05',
        'approvedAt' => '2022-08-05',
        'closedAt' => '2022-08-05',
        'poster' => [
            '@id' => '/persons/1',
            'username' => 'some_username',
            'email' => 'some_email',
            'firstname' => 'some_firstname',
            'lastname' => 'some_lastname',
        ],
        'representative' => [
            '@id' => '/persons/1',
            'username' => 'some_username',
            'email' => 'some_email',
            'firstname' => 'some_firstname',
            'lastname' => 'some_lastname',
        ],
        'supplierRepresentative' => [
            '@id' => '/vendorusze/1',
            'username' => 'some_username',
            'email' => 'some_email',
            'firstname' => 'some_firstname',
            'lastname' => 'some_lastname',
        ],
        'leader' => [
            '@id' => '/persons/1',
            'username' => 'some_username',
            'email' => 'some_email',
            'firstname' => 'some_firstname',
            'lastname' => 'some_lastname',
        ],
        'iFactor' => 'some factor',
        'factory' => [
            '@id' => '/locations/1',
            '@type' => 'Location',
            'name' => 'foo',
            'erp' => 314,
            'currency' => [
                '@id' => '/currency/1',
                '@type' => 'Currency',
                'id' => 2,
                'name' => 'EUR',
            ],
        ],
        'supplierErp' => 1,
        'supplierNumber' => 'some supplierNumber',
        'supplierName' => 'some supplierName',
        'status' => 'VENDOR TO FILL FORM',
        'vendorWarrantyClaims' => [],
        'parts' => [[
            '@id' => '/parts/1',
            '@type' => 'SupplierCorrectiveActionRequestPart',
            'partNumber' => 'some part number',
            'description' => 'some description',
            'quantity' => 1,
            'unitOfMeasure' => 'some unitOfMeasure',
        ]],
        'files' => [[
            '@id' => '/suppliercorrectiveactionrequestfile/1',
            '@type' => 'SupplierCorrectiveActionRequestFile',
            'id' => 1,
            'filePath' => 'some filePath',
            'poster' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'createdAt' => '2022-08-05',
            'description' => 'some description',
            'sha' => 'some sha',
            'mimeType' => 'some mimeType',
            'extension' => 'some extension',
            'size' => 25000,
            'public' => true,
        ]],
        'activity' => [[
            '@id' => '/activities/1',
            '@type' => 'Comment',
            'resource' => 'some resource',
            'message' => 'some comment',
            'user' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'createdAt' => '2022-08-05',
            'updatedAt' => '2022-08-05',
            'public' => true,
            'metadata' => [],
            'files' => [],
        ]],
    ];

    private const SITE = [
        '@id' => 'ion/site',
        '@type' => 'Site',
        'siteID' => 'SITE01',
        'siteDescription' => 'City',
        'siteAddressCode' => 'Montargie',
        'siteAddressName' => 'Quentin',
    ];

    private const SUPPLIERS = [
        '@id' => 'ion/business_partners',
        '@type' => 'Supplier',
        'name' => 'DANA SAS FRANCE',
        'code' => 'DAN0013',
    ];

    private const REPORT = [
        '@id' => '/reports/1',
        '@type' => 'Report',
        'x' => 'x',
        'y' => 'y',
        'total' => 2,
        'xTotals' => [],
        'yTotals' => [],
        'rows' => [],
        'metadata' => [],
    ];

    /**
     * @todo Each mock response can be isolated in a method or an invokable object.
     *
     * @param non-empty-string     $method
     * @param non-empty-string     $uri
     * @param array<string, mixed> $options
     */
    public function __invoke(string $method, string $uri, array $options): MockResponse
    {
        // ignore GET paramerters
        if (str_contains($uri, '?')) {
            $uri = Str\before($uri, '?');
        }

        // remove the base url to test uri only
        $uriObj = u($uri)->trimPrefix('https://example.com');

        if (Request::METHOD_GET === $method && $uriObj->endsWith('/ion/sites')) {
            return new MockResponse($this->getHydraCollection([self::SITE]));
        }
        if (Request::METHOD_GET === $method && $uriObj->endsWith('/ion/purchase_orders')) {
            return new MockResponse($this->getHydraCollection([self::PURCHASE_ORDER_FUTURE]));
        }

        if (Request::METHOD_GET === $method && $uriObj->endsWith('/materials/planned_material_requirements_plannings')) {
            return new MockResponse($this->getHydraCollection()); // @todo add an item
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/ion/planned_orders')) {
            return new MockResponse($this->getHydraCollection()); // @todo add an item
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/people')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse($this->getHydraCollection()); // @todo add idem
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/people')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse($this->getHydraCollection()); // @todo add idem
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/buyers')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse($this->getHydraCollection()); // @todo add idem
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/purchasing/ncr_vendor_warranty_claims/')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{]', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(
                json_encode(self::NCR_VENDOR_WARRANTY_CLAIM),
                ['http_code' => Response::HTTP_OK]
            );
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/purchasing/vendor_warranty_claims/')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{]', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(
                json_encode(self::NCR_VENDOR_WARRANTY_CLAIM),
                ['http_code' => Response::HTTP_OK]
            );
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/purchasing/wc_vendor_warranty_claims/')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{]', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(
                json_encode(self::WC_VENDOR_WARRANTY_CLAIM),
                ['http_code' => Response::HTTP_OK]
            );
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/dms/')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(
                json_encode(self::DMS),
                ['http_code' => Response::HTTP_OK]
            );
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/dms')) {
            return new MockResponse($this->getHydraCollection([self::DMS]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/locations')) {
            return new MockResponse($this->getHydraCollection([self::LOCATION]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/ion/purchase_orders/')) {
            $params = $uriObj->trimPrefix('/ion/purchase_orders/');
            if ($params->containsAny('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(json_encode(self::PURCHASE_ORDER_FUTURE), ['http_code' => Response::HTTP_OK]);
        }

        if (Request::METHOD_PUT === $method && $uriObj->endsWith('/ion/purchase_orders/1/pdf_labels')) {
            return new MockResponse(json_encode(self::PURCHASE_ORDER_FUTURE), [
                'http_code' => Response::HTTP_OK,
                'response_headers' => [
                    'Content-Disposition' => 'Content-Disposition',
                    'Content-Type' => 'application/pdf',
                ],
            ]);
        }

        if (Request::METHOD_PUT === $method && $uriObj->startsWith('/ion/purchase_orders/')) {
            return new MockResponse(json_encode(self::PURCHASE_ORDER_FUTURE), ['http_code' => Response::HTTP_OK]);
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/ion/request_for_quotations/')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(json_encode(self::REQUEST_FOR_QUOTATION_FUTURE));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/ion/request_for_quotations')) {
            return new MockResponse($this->getHydraCollection([self::REQUEST_FOR_QUOTATION_FUTURE]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/ion/rfq') && $uriObj->endsWith('zip')) {
            return new MockResponse(json_encode(self::REQUEST_FOR_QUOTATION_FUTURE), ['http_code' => Response::HTTP_OK]);
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/purchasing/vendor_warranty_claims')) {
            return new MockResponse($this->getHydraCollection([self::WC_VENDOR_WARRANTY_CLAIM, self::NCR_VENDOR_WARRANTY_CLAIM]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/quality/supplier_corrective_action_requests/')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(json_encode(self::SUPPLIER_CORRECTIVE_ACTION_REQUESTS));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/quality/supplier_corrective_action_requests')) {
            return new MockResponse($this->getHydraCollection([self::SUPPLIER_CORRECTIVE_ACTION_REQUESTS]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/ion/business_partners')) {
            return new MockResponse($this->getHydraCollection([self::SUPPLIERS]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/reports')) {
            return new MockResponse($this->getHydraCollection([self::REPORT]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/lcoations')) {
            return new MockResponse($this->getHydraCollection([self::LOCATION]));
        }

        if (Request::METHOD_GET === $method && $uriObj->startsWith('/comments')) {
            if ($uriObj->endsWith('404')) {
                return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
            }

            return new MockResponse(json_encode(self::COMMENT), ['http_code' => Response::HTTP_CREATED]);
        }

        if (Request::METHOD_POST === $method && $uriObj->startsWith('/comments')) {
            return new MockResponse(json_encode(self::COMMENT), ['http_code' => Response::HTTP_CREATED]);
        }

        if (Request::METHOD_POST === $method && $uriObj->startsWith('/quality/supplier_corrective_action_requests')) {
            return new MockResponse(json_encode(self::SUPPLIER_CORRECTIVE_ACTION_REQUESTS), ['http_code' => Response::HTTP_CREATED]);
        }

        if (Request::METHOD_PUT === $method && $uriObj->startsWith('/vendorwarrantyclaims/')) {
            return new MockResponse(json_encode(self::NCR_VENDOR_WARRANTY_CLAIM), ['http_code' => Response::HTTP_OK]);
        }

        if (Request::METHOD_POST === $method && $uriObj->endsWith('/token')) {
            return new MockResponse('{}', ['http_code' => Response::HTTP_NOT_FOUND]);
        }

        throw new LogicException("Mock no implemented for $uri and method $method");
    }

    /**
     * @param list<array<string, mixed>> $members
     */
    private function getHydraCollection(array $members = []): string
    {
        return Json\encode([
            '@type' => 'hydra:Collection',
            'hydra:member' => $members,
        ], pretty: true);
    }
}

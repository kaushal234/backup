<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Support;

use App\Entity\EquipmentRecord;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EquipmentRecordSpreadsheetNormalizer implements NormalizerInterface
{
    final public const string ID = '#ID';
    final public const string FACTORY = 'Factory';
    final public const string ER_SN = 'ER SN#';
    final public const string TYPE = 'Type';
    final public const string END_USER = 'End User';
    final public const string BUYER = 'Buyer';
    final public const string CUSTOMER_ASSET = 'Customer Asset #';
    final public const string FIRST_GT_DATE = 'First GT Date';
    final public const string FIRST_ESTIMATED_GT_DATE = 'First Estimated GT Date';
    final public const string ESTIMATED_GT_DATE = 'Estimated GT Date';
    final public const string GT_DATE = 'GT Date';
    final public const string YELLOW_TAG_DATE = 'Yellow Tag Date';
    final public const string DATE_SHIPPED = 'Date Shipped';
    final public const string WORK_ORDER = 'Work Order';
    final public const string EMISSION_RATING = 'Emission Rating';
    final public const string REQUESTED_DELIVERY_DATE = 'Requested Delivery Date';
    final public const string FACTORY_PROMISED_DELIVERY_DATE = 'Factory Promised Delivery Date';
    final public const string FACTORY_BU = 'Factory BU';
    final public const string PURCHASE_ORDER_ACCEPTED_DATE = 'Purchase Order Accepted Date';
    final public const string SOL = 'SOL';
    final public const string MODEL = 'Model';
    final public const string PRE_DELIVERY_INSPECTION = 'Pre Delivery Inspection';
    final public const string INCOTERM = 'Incoterm';
    final public const string INCOTERM_LOCATION = 'Incoterm Location';
    final public const string INVOICE_NUMBER = 'Invoice Number';
    final public const string COMMISSIONING = 'Commissioning';
    final public const string LENGTH = 'Length (in mm)';
    final public const string WIDTH = 'Width (in mm)';
    final public const string HEIGHT = 'Height (in mm)';
    final public const string WEIGHT = 'Weight (in Kg)';
    final public const string CUSTOMER_PO = 'Customer PO';
    final public const string LIGHT = 'Light';
    final public const string APC = 'APC';
    final public const string COUNTRY = 'Country';
    final public const string PAYMENT_TERMS = 'Payment Terms';
    final public const string SALES_ORGANISATION = 'Sales Organisation';
    final public const string ESTIMATED_PICK_UP_DATE = 'Estimated Pick Up Date';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EquipmentRecord && CsvEncoder::FORMAT === $format;
    }

    /**
     * @param EquipmentRecord $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /* @var array $normalizedData */
        $normalizedData[self::ID] = $object->getId();
        $normalizedData[self::ER_SN] = $object->getSerialNumber();
        $normalizedData[self::MODEL] = $object->getModel() ?? '';
        $normalizedData[self::TYPE] = $object->getType() ?? '';
        $normalizedData[self::END_USER] = $object->getEndUser()?->getName();
        $normalizedData[self::BUYER] = $object->getBuyer()?->getName();
        $normalizedData[self::APC] = $object->getAirport()?->getCode();
        $normalizedData[self::COUNTRY] = $object->getDeliveredCountry()?->getName();
        $normalizedData[self::SALES_ORGANISATION] = $object->getSalesOrganisation()?->getName() ?? '';
        $normalizedData[self::CUSTOMER_ASSET] = $object->getCustomerSerialNumber() ?? '';
        $normalizedData[self::CUSTOMER_PO] = $object->getOrder() ? implode(', ', $object->getOrder()->getCustomerPurchaseOrders()) : '';
        $normalizedData[self::FIRST_GT_DATE] = $object->getFirstGreenTagDate()?->format('Y-m-d') ?? '';
        $normalizedData[self::FIRST_ESTIMATED_GT_DATE] = $object->getFirstEstimatedGreenTagDate()?->format('Y-m-d') ?? '';
        $normalizedData[self::ESTIMATED_GT_DATE] = $object->getEstimatedGreenTagDate()?->format('Y-m-d') ?? '';
        $normalizedData[self::GT_DATE] = $object->getGreenTagDate()?->format('Y-m-d') ?? '';
        $normalizedData[self::YELLOW_TAG_DATE] = $object->getYellowTagDate()?->format('Y-m-d') ?? '';
        $normalizedData[self::ESTIMATED_PICK_UP_DATE] = $object->getLastEquipmentShippingRecordsLine()?->estimatedPickUpDate?->format('Y-m-d') ?? '';
        $normalizedData[self::DATE_SHIPPED] = $object->getDateShipped()?->format('Y-m-d') ?? '';
        $normalizedData[self::WORK_ORDER] = $object->getWorkOrder();
        $normalizedData[self::EMISSION_RATING] = $object->getEmissionRating()?->getName();
        $normalizedData[self::REQUESTED_DELIVERY_DATE] = $object->orderFactory?->requestedDeliveryDate?->format('Y-m-d') ?? '';
        $normalizedData[self::FACTORY_PROMISED_DELIVERY_DATE] = $object->orderFactory?->factoryPromisedDeliveryDate?->format('Y-m-d') ?? '';
        $normalizedData[self::FACTORY_BU] = $object->orderFactory?->orderLine?->factory?->getName() ?? '';
        $normalizedData[self::SOL] = $object->orderFactory?->orderLine?->getLegacyId() ?? '';
        $normalizedData[self::PURCHASE_ORDER_ACCEPTED_DATE] = $object->orderFactory?->orderLine?->purchaseOrderAcceptedDate?->format('Y-m-d') ?? '';
        $normalizedData[self::PRE_DELIVERY_INSPECTION] = $object->orderFactory?->orderLine?->inspection ? 'YES' : 'NO';
        $normalizedData[self::INCOTERM] = $object->orderFactory?->orderLine?->incoterm->code ?? '';
        $normalizedData[self::INCOTERM_LOCATION] = $object->orderFactory?->orderLine?->incotermLocation;
        $normalizedData[self::INVOICE_NUMBER] = $object->orderTransaction->invoice ?? '';
        $normalizedData[self::COMMISSIONING] = $object->orderFactory?->commissioning ? 'YES' : 'NO';
        $normalizedData[self::LENGTH] = $object->getLength() ?? '';
        $normalizedData[self::WIDTH] = $object->getWidth() ?? '';
        $normalizedData[self::HEIGHT] = $object->getHeight() ?? '';
        $normalizedData[self::WEIGHT] = $object->getWeight() ?? '';
        $normalizedData[self::LIGHT] = $object->isLight() ? 'YES' : 'NO';
        $normalizedData[self::PAYMENT_TERMS] = $object->orderFactory?->orderLine->paymentTerms ?? '';

        return $normalizedData;
    }
}

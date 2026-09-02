<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Sales;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\Sales\SalesForecast;

final readonly class SalesForecastFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'sales_forecast';
    }

    public function entityClass(): string
    {
        return SalesForecast::class;
    }

    public function defaultAlias(): string
    {
        return 'sfr';
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Sales forecast records (SFR — opportunities/deals tracked by sales), including status, SSO and factory, ASM and poster, buyer/end-user customers, product, country, quantity, success percentage, estimated sale date and delinquency.';
    }

    public function fields(): array
    {
        $statuses = [
            SalesForecast::BUDGET, SalesForecast::IN_PROGRESS, SalesForecast::DELAYED,
            SalesForecast::ORDERED, SalesForecast::LOST, SalesForecast::ORDER_CANCELLED,
            SalesForecast::CANCELLED, SalesForecast::PARTIAL,
        ];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'Exact SFR statuses. Open = BUDGET, IN_PROGRESS, DELAYED; closed = ORDERED, LOST, ORDER_CANCELLED, CANCELLED, PARTIAL. Multiple = OR.'),
            Filter::in('ssoNames', 'sso.name', desc: 'Exact SSO (sales office) location names. Multiple = OR.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory location names. Multiple = OR.'),
            Filter::inInt('asmPeopleIds', 'asm', desc: 'IDs of the ASM (area sales managers) who own the SFR (People). Multiple = OR.'),
            Filter::inInt('posterPeopleIds', 'poster', desc: 'IDs of the people who created the SFR (People). Multiple = OR.'),
            Filter::inInt('buyerCustomerIds', 'buyer', desc: 'IDs of the buyer customers. Multiple = OR.'),
            Filter::like('buyerNameLike', 'buyer.name', desc: 'Partial, case-insensitive match on the buyer customer name (LIKE %value%).'),
            Filter::inInt('endUserCustomerIds', 'endUser', desc: 'IDs of the end-user customers. Multiple = OR.'),
            Filter::like('productNameLike', 'product.name', desc: 'Partial, case-insensitive match on the product name (LIKE %value%).'),
            Filter::in('countryNames', 'country.name', desc: 'Exact customer country names. Multiple = OR.'),
            Filter::eq('equoteId', 'equoteId', desc: 'Exact eQuote identifier linked to the SFR.'),
            Filter::bool('delinquent', 'delinquent', desc: 'True = only delinquent SFRs, false = only non-delinquent, omit for both.'),
            Filter::intRange('successPercentage', minName: 'successPercentageMin', maxName: 'successPercentageMax', minDesc: 'Minimum GSE success percentage (0-100), inclusive.', maxDesc: 'Maximum GSE success percentage (0-100), inclusive.'),
            Filter::dateRange('estimatedSaleDate', afterName: 'estimatedSaleAfter', beforeName: 'estimatedSaleBefore', afterDesc: 'ISO-8601 date — estimated sale date on/after this date.', beforeDesc: 'ISO-8601 date — estimated sale date on/before this date.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — SFRs created on/after this date.', beforeDesc: 'ISO-8601 date — SFRs created on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — SFRs closed on/after this date.', beforeDesc: 'ISO-8601 date — SFRs closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, SalesForecast::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->getStatus(),
            'sso' => $entity->getSso()->getName(),
            'factory' => $entity->getFactory()->getName(),
            'asm' => $this->personName($entity->getAsm()),
            'buyer' => $entity->getBuyer()?->getName(),
            'endUser' => $entity->getEndUser()?->getName(),
            'product' => $entity->getProduct()?->getName(),
            'country' => $entity->getCountry()?->getName(),
            'quantity' => $entity->getQuantity(),
            'successPercentage' => $entity->getSuccessPercentage(),
            'delinquent' => $entity->isDelinquent(),
            'equoteId' => $entity->getEquoteId(),
            'estimatedSaleDate' => $entity->getEstimatedSaleDate()->format('Y-m-d'),
            'createdAt' => $entity->getCreatedAt()->format(\DATE_ATOM),
            'closedAt' => $entity->getClosedAt()?->format(\DATE_ATOM),
        ];
    }
}

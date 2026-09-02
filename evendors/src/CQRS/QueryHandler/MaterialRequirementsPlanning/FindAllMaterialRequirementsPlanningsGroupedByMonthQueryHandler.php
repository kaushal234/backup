<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\MaterialRequirementsPlanning;

use App\CQRS\Query\MaterialRequirementsPlanning\FindAllMaterialRequirementsPlanningsGroupedByMonthQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\MaterialRequirementsPlanning;
use DateTimeImmutable;
use Psl\Dict;
use Psl\Math;

/**
 * @phpstan-type ForecastStructure array{erp: int, partNumber: string, supplierPartNumber: string, supplierNumber: string, warehouse: string, purchaseOrder: string, revision: string, description: string, quantity: string, orderDate: DateTimeImmutable, plannedDeliveryDate: DateTimeImmutable, vendorPartNumber: string, price: ?float, currency: ?string}
 *
 * @psalm-type ForecastStructure = array{erp: int, partNumber: string, supplierPartNumber: string, supplierNumber: string, warehouse: string, purchaseOrder: string, revision: string, description: string, quantity: string, orderDate: DateTimeImmutable, plannedDeliveryDate: DateTimeImmutable, vendorPartNumber: string, price: ?float, currency: ?string}
 */
final class FindAllMaterialRequirementsPlanningsGroupedByMonthQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return array<string, list<MaterialRequirementsPlanning>>
     */
    public function __invoke(FindAllMaterialRequirementsPlanningsGroupedByMonthQuery $query): array
    {
        /** @var array<string, list<MaterialRequirementsPlanning>> $plannings */
        $plannings = Dict\group_by(
            $this->client->findAll(MaterialRequirementsPlanning::class),
            static fn (MaterialRequirementsPlanning $planning): string => $planning->partNumber,
        );

        /** @var array<string, list<MaterialRequirementsPlanning>> */
        return Dict\map(
            $plannings,
            /**
             * @param list<MaterialRequirementsPlanning> $plannings
             *
             * @return list<MaterialRequirementsPlanning>
             */
            static function (array $plannings): array {
                /** @var list<ForecastStructure> $plannings */
                $plannings = Dict\map(
                    $plannings,
                    static fn (MaterialRequirementsPlanning $planning): array => [
                        'erp' => $planning->erp,
                        'partNumber' => $planning->partNumber,
                        'supplierPartNumber' => $planning->supplierPartNumber,
                        'purchaseOrder' => $planning->purchaseOrder,
                        'supplierNumber' => $planning->supplierNumber,
                        'warehouse' => $planning->warehouse,
                        'revision' => $planning->revision,
                        'description' => $planning->description,
                        'quantity' => $planning->orderedQuantity,
                        'orderDate' => new DateTimeImmutable($planning->plannedOrderDate),
                        'plannedDeliveryDate' => new DateTimeImmutable($planning->plannedDeliveryDate),
                        'vendorPartNumber' => $planning->vendorPartNumber,
                        'price' => $planning->price,
                        'currency' => $planning->currency,
                    ],
                );
                /** @var array<non-empty-string, non-empty-list<ForecastStructure>> */
                $plannings = Dict\filter(
                    Dict\group_by(
                        $plannings,
                        /**
                         * @param ForecastStructure $planning
                         *
                         * @return non-empty-string
                         */
                        static function (array $planning): string {
                            return $planning['plannedDeliveryDate']->format('Y-m').'-'.$planning['erp'];
                        }
                    ),
                    static fn (array $list): bool => [] !== $list
                );

                /** @var array<non-empty-string, MaterialRequirementsPlanning> */
                return Dict\map($plannings, static function (array $plannings): MaterialRequirementsPlanning {
                    $orderedQuantity = Math\sum(Dict\map(
                        $plannings,
                        /**
                         * @param ForecastStructure $planning
                         */
                        static fn (array $planning): int => (int) $planning['quantity'],
                    ));

                    $orderedPrice = Math\sum_floats(Dict\map(
                        $plannings,
                        /**
                         * @param ForecastStructure $planning
                         */
                        static fn (array $planning): float => (float) $planning['price'] * (int) $planning['quantity'],
                    ));

                    return new MaterialRequirementsPlanning(
                        ':invalid',
                        $plannings[0]['erp'],
                        $plannings[0]['purchaseOrder'],
                        $plannings[0]['supplierNumber'],
                        $plannings[0]['warehouse'],
                        $plannings[0]['partNumber'],
                        $plannings[0]['supplierPartNumber'],
                        $plannings[0]['description'],
                        (string) $orderedQuantity,
                        $plannings[0]['revision'],
                        $plannings[0]['orderDate']->format('Y-m-d'),
                        $plannings[0]['plannedDeliveryDate']->format('Y-m'),
                        $plannings[0]['vendorPartNumber'],
                        $orderedPrice,
                        $plannings[0]['currency'],
                    );
                });
            },
        );
    }
}

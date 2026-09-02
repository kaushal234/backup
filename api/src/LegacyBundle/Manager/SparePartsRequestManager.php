<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\SparePartsRequestPart;
use App\Entity\Parts\TOCSparePartsRequest;
use Doctrine\DBAL\Connection;
use LegacyBundle\Entity\ServiceBulletinLine;

class SparePartsRequestManager
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function handlePartDeletion(SparePartsRequestPart $part): void
    {
        $deletedPartQueryBuilder = $this->legacyConnection->createQueryBuilder();
        $deletedPartQueryBuilder
            ->update('spr_lines')
            ->set('oqua', ':quantity')
            ->where('id = :legacyId')
            ->setParameters([
                'legacyId' => $part->getLegacyId(),
                'quantity' => 0,
            ])
        ;

        $stmt = $this->legacyConnection->prepare($deletedPartQueryBuilder->getSQL());
        foreach ($deletedPartQueryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->executeStatement();
    }

    public function handleWarrantyPartDoubleWriting(SparePartsRequest $sparePartsRequest): void
    {
        if (SparePartsRequest::TYPE_WARRANTY !== $sparePartsRequest->type || !$sparePartsRequest instanceof TOCSparePartsRequest) {
            return;
        }

        $warrantyClaimQueryBuilder = $this->legacyConnection->createQueryBuilder();
        $warrantyClaimQueryBuilder
            ->select('warranty_id')
            ->from('toc')
            ->where('id = :id')
            ->setParameter('id', $sparePartsRequest->tocId)
        ;

        $stmt = $this->legacyConnection->prepare($warrantyClaimQueryBuilder->getSQL());
        foreach ($warrantyClaimQueryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $result = $stmt->executeQuery();
        $warrantyId = $result->fetchOne();

        if (null === $warrantyId) {
            return;
        }

        $parts = $sparePartsRequest->getParts();
        $sortedParts = [];
        foreach ($parts as $part) {
            $index = $part->partNumber;
            if (null !== ($sortedPart = $sortedParts[$index] ?? null)) {
                $sortedPart->quantity += $part->quantity;
                if ('' !== (string) $sortedPart->comment) {
                    if ('' !== (string) $part->comment) {
                        $sortedPart->comment .= ' / '.$part->comment;
                    }
                    continue;
                }
                $sortedPart->comment = $part->comment;
                continue;
            }
            $sortedParts[$index] = $part;
        }

        $partsQueryBuilder = $this->legacyConnection->createQueryBuilder();

        $partsQueryBuilder
            ->select('part_number', 'id', 'part_description', 'quantity', 'um')
            ->from('warranty_parts')
            ->where('parent_id = :warrantyId')
            ->andWhere('spr_id = :sparePartsRequestId')
            ->setParameters([
                'warrantyId' => $warrantyId,
                'sparePartsRequestId' => $sparePartsRequest->getId(),
            ])
        ;

        $stmt = $this->legacyConnection->prepare($partsQueryBuilder->getSQL());
        foreach ($partsQueryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $existingWarrantyParts = $stmt->executeQuery()->fetchAllAssociativeIndexed();

        $this->legacyConnection->beginTransaction();

        foreach ($sortedParts as $part) {
            if (null !== ($existingWarrantyPart = $existingWarrantyParts[$part->partNumber] ?? null)) {
                $updateQueryBuilder = $this->legacyConnection->createQueryBuilder();
                $updateQueryBuilder
                    ->update('warranty_parts')
                    ->set('quantity', ':quantity')
                    ->set('part_description', ':description')
                    ->set('um', ':unitOfMeasure')
                    ->set('notes', ':comment')
                    ->where('id = :id')
                    ->setParameters([
                        'id' => $existingWarrantyPart['id'],
                        'quantity' => $part->quantity,
                        'unitOfMeasure' => $part->unitOfMeasure,
                        'description' => $part->description,
                        'comment' => $part->comment,
                    ])
                ;

                $stmt = $this->legacyConnection->prepare($updateQueryBuilder->getSQL());
                foreach ($updateQueryBuilder->getParameters() as $key => $value) {
                    $stmt->bindValue($key, $value);
                }

                $stmt->executeStatement();
                unset($existingWarrantyParts[$part->partNumber]);
                continue;
            }
            $insertQueryBuilder = $this->legacyConnection->createQueryBuilder();
            $insertQueryBuilder
                ->insert('warranty_parts')
                ->values([
                    'parent_id' => ':warrantyId',
                    'part_description' => ':description',
                    'part_number' => ':partNumber',
                    'um' => ':unitOfMeasure',
                    'quantity' => ':quantity',
                    'qty_in' => ':quantityIn',
                    'd_in' => ':date',
                    'sn' => ':sn',
                    'spr_id' => ':sparePartsRequestId',
                    'notes' => ':comment',
                ])
                ->setParameters([
                    'warrantyId' => $warrantyId,
                    'description' => $part->description,
                    'partNumber' => $part->partNumber,
                    'unitOfMeasure' => $part->unitOfMeasure,
                    'quantity' => $part->quantity,
                    'quantityIn' => 0,
                    'date' => date('Y-m-d'),
                    'sn' => '',
                    'sparePartsRequestId' => $sparePartsRequest->getId(),
                    'comment' => $part->comment,
                ])
            ;

            $stmt = $this->legacyConnection->prepare($insertQueryBuilder->getSQL());
            foreach ($insertQueryBuilder->getParameters() as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->executeStatement();
        }

        foreach ($existingWarrantyParts as $existingWarrantyPart) {
            $deletedQueryBuilder = $this->legacyConnection->createQueryBuilder();
            $deletedQueryBuilder
                ->delete('warranty_parts')
                ->where('id = :id')
                ->setParameter('id', $existingWarrantyPart['id'])
            ;

            $stmt = $this->legacyConnection->prepare($deletedQueryBuilder->getSQL());
            foreach ($deletedQueryBuilder->getParameters() as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->executeStatement();
        }

        $this->legacyConnection->commit();
    }

    public function handleSBSparePartsRequestClosing(SBSparePartsRequest $partsRequest, string $previousStatus): void
    {
        $status = $partsRequest->getStatus();

        if (SparePartsRequest::STATUS_CLOSED === $status && $previousStatus !== $status) {
            $updateQueryBuilder = $this->legacyConnection->createQueryBuilder();
            $updateQueryBuilder
                ->update('sb_lines')
                ->set('status', ':tldToImplement')
                ->where('spr_id = :sprId')
                ->andWhere('sb_lines.status != :closed')
                ->andWhere('parent_id = :sbId')
                ->setParameters([
                    'tldToImplement' => ServiceBulletinLine::SB_LINE_STATUS_TLD_TO_IMPLEMENT,
                    'sprId' => $partsRequest->getLegacyId(),
                    'sbId' => $partsRequest->sbId,
                    'closed' => ServiceBulletinLine::SB_LINE_STATUS_CLOSED,
                ])
            ;

            $stmt = $this->legacyConnection->prepare($updateQueryBuilder->getSQL());
            foreach ($updateQueryBuilder->getParameters() as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->executeStatement();
        }
    }
}

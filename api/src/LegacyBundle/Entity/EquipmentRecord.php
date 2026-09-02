<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Repository\EquipmentRecordRepository;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: EquipmentRecordRepository::class, readOnly: true)]
#[ORM\Table(name: 'service')]
class EquipmentRecord
{
    #[ORM\Column(name: 'customer_id', type: 'integer')]
    public int $endUser;

    #[ORM\Column(name: 'maintainer_customer_id', type: 'integer', nullable: true)]
    public ?int $maintainer = null;

    #[ORM\Column(name: 'buyer_customer_id', type: 'integer')]
    public int $buyer;

    #[ORM\Column(name: 'dgt_act', type: 'datetime')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public \DateTimeInterface $greenTagDate;

    #[ORM\Column(name: 'date_shipped', type: 'datetime')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public \DateTimeInterface $shippedDate;

    #[ORM\Column(name: 'warranty_length', type: 'integer')]
    public int $warrantyLength;

    #[ORM\Column(name: 'date_warranty_end', type: Types::DATETIME_MUTABLE)]
    public \DateTime $dateWarrantyEnd;

    #[ORM\Column(name: 'hours', type: 'integer')]
    public int $hours;

    #[ORM\Column(name: 'warranty_conditions', type: 'string')]
    public string $warrantyConditions;

    #[ORM\Column(name: 'customer_name', length: 60)]
    public string $customerName;

    #[ORM\Column(name: 'delivery_location', type: Types::TEXT)]
    public string $deliveryLocation;

    #[ORM\Column(length: 50)]
    public string $type;

    #[ORM\Column]
    public string $model;

    #[ORM\Column(name: 'man_location', length: 20)]
    public string $factory;

    #[ORM\Column(name: 'sales_org', length: 20)]
    public string $salesOrganisation;

    #[ORM\Column(name: 'sn', length: 20)]
    public string $serialNumber;

    #[ORM\ManyToOne(targetEntity: SalesOrderUnit::class)]
    #[ORM\JoinColumn(name: 'sor_uid', referencedColumnName: 'id')]
    public SalesOrderUnit $salesOrderUnit;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}

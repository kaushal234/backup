<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'sb_lines')]
class ServiceBulletinLine
{
    final public const string SB_LINE_STATUS_CUSTOMER_TO_DECIDE = 'CUSTOMER_TO_DECIDE';
    final public const string SB_LINE_STATUS_TLD_TO_SHIP = 'TLD_TO_SHIP';
    final public const string SB_LINE_STATUS_TLD_TO_IMPLEMENT = 'TLD_TO_IMPLEMENT';
    final public const string SB_LINE_STATUS_CLOSED = 'CLOSED';

    /**
     * Statuses at which a bulletin becomes actionable for the customer (kit parts confirmed).
     * Below this (e.g. still TLD_TO_NOTIFY), showing the SB as COMPULSORY/RECOMMENDED would be
     * misleading since TLD may not have inventory available yet (TTS-1062, TTS-56529).
     *
     * @var list<string>
     */
    final public const array VISIBLE_ON_EXTRANET_STATUSES = [
        self::SB_LINE_STATUS_CUSTOMER_TO_DECIDE,
        self::SB_LINE_STATUS_TLD_TO_SHIP,
        self::SB_LINE_STATUS_TLD_TO_IMPLEMENT,
    ];

    #[ORM\ManyToOne(targetEntity: ServiceBulletin::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    public ServiceBulletin $serviceBulletin;

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class)]
    #[ORM\JoinColumn(name: 'er_id', referencedColumnName: 'id')]
    public EquipmentRecord $equipmentRecord;

    #[ORM\Column(length: 60)]
    public string $status;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}

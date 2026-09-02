<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'mod_files', options: ['charset' => 'latin1'])]
#[ORM\Index(name: 'fid', columns: ['fid'])]
#[ORM\Index(name: 'parent_id', columns: ['parent_id', 'module'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'module', type: 'string')]
#[ORM\DiscriminatorMap([
    ServiceBulletinFile::MODULE => ServiceBulletinFile::class,
])]
class ModFile
{
    #[ORM\Column(name: 'parent_id', type: 'integer', options: ['default' => 0])]
    public int $parentId = 0;

    #[ORM\Column(name: 'date', type: 'date', nullable: true)]
    public ?\DateTimeInterface $date = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(name: 'filename', type: 'string', length: 255, options: ['default' => ''])]
    public string $filename = '';

    #[ORM\Column(name: 'level', type: 'integer')]
    public int $level;

    #[ORM\Column(name: 'expiration_date', type: 'integer', nullable: true)]
    public ?int $expirationDate = null;

    #[ORM\ManyToOne(targetEntity: File::class, inversedBy: 'modFiles')]
    #[ORM\JoinColumn(name: 'fid', referencedColumnName: 'id', nullable: false)]
    public File $file;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}

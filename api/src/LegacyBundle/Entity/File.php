<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'file', options: ['charset' => 'latin1'])]
class File
{
    #[ORM\Column(name: 'parent_id', type: 'integer')]
    public int $parentId;

    #[ORM\Column(name: 'dt', type: 'datetime')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'portal', type: 'string', length: 10)]
    public string $portal;

    #[ORM\Column(name: 'filepath', type: 'string', length: 200)]
    public string $filePath;

    #[ORM\Column(name: 'md5', type: 'string', length: 32)]
    public string $md5;

    #[ORM\Column(name: 'mime', type: 'string', length: 20)]
    public string $mime;

    #[ORM\Column(name: 'extension', type: 'string', length: 10)]
    public string $extension;

    #[ORM\Column(name: 'size', type: 'integer')]
    public int $size;

    #[ORM\Column(name: 'filename', type: 'string', length: 100)]
    public string $filename;

    #[ORM\OneToMany(targetEntity: ModFile::class, mappedBy: 'file')]
    public Collection $modFiles;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function __construct()
    {
        $this->modFiles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFilePath(): string
    {
        return mb_substr($this->filePath, mb_strpos($this->filePath, 'uploads/') + mb_strlen('uploads/'));
    }
}

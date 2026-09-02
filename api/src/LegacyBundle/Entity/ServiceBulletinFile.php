<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\LegacyBundle\Dto\ServiceBulletinFileOutput;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Controller\DownloadFileController;
use LegacyBundle\DataProvider\ServiceBulletinFileOutputProvider;
use LegacyBundle\Doctrine\ORM\Extension\ServiceBulletinSecurityAware;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Get(
            uriTemplate: '/service_bulletin_files/{id}/download',
            controller: DownloadFileController::class,
            output: null,
            name: 'download',
            provider: ItemProvider::class
        ),
    ],
    routePrefix: '/legacy',
    output: ServiceBulletinFileOutput::class,
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
    provider: ServiceBulletinFileOutputProvider::class
)]
#[ServiceBulletinSecurityAware(parentIdProperty: 'parentId')]
#[ApiFilter(SearchFilter::class, properties: ['parentId'])]
#[ORM\Entity(readOnly: true)]
class ServiceBulletinFile extends ModFile
{
    public const MODULE = 'SB3';
    public const CUSTOMER_LEVEL = 0;
}

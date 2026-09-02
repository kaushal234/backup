<?php

declare(strict_types=1);

namespace App\Alice\Loader;

use App\Entity\Activity\CommentFile;
use App\Entity\AI\AIFile;
use App\Entity\Materials\EvendorsNewsFile;
use App\Entity\Purchasing\VendorWarrantyClaimFile;
use App\Entity\Quality\NonConformityFile;
use App\Entity\Quality\SupplierCorrectiveActionRequestFile;
use App\Entity\Quality\SupplierCorrectiveActionRequestMainFile;
use App\Entity\Sales\CustomerFile;
use App\Entity\Service\TechnicianOnCallFile;
use App\Entity\SPQ\AttachedFile;
use App\Entity\Support\ManualDocumentFile;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;

/**
 * Order for files fixtures. Executed after all other fixtures.
 * This method only concerned joined table inheritance, because force ID is not working.
 * So don't use loaders if you can force ID on fixtures.
 */
final class FilesOrderLoader implements LoaderInterface
{
    // Entities loaded after all.
    public array $files = [
        SupplierCorrectiveActionRequestFile::class,
        SupplierCorrectiveActionRequestMainFile::class,
        AttachedFile::class,
        NonConformityFile::class,
        ManualDocumentFile::class,
        EvendorsNewsFile::class,
        CustomerFile::class,
        CommentFile::class,
        VendorWarrantyClaimFile::class,
        AIFile::class,
        TechnicianOnCallFile::class,
    ];

    public function __construct(
        private readonly LoaderInterface $decoratedLoader
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], ?PurgeMode $purgeMode = null): array
    {
        $objects = $this->decoratedLoader->load($fixturesFiles, $parameters, $objects, $purgeMode);

        $end = $this->extractObjectsByArrayOfClassName($objects, $this->files);

        $rest = array_diff_key($objects, $end);

        return $rest + $end;
    }

    private function extractObjectsByArrayOfClassName(array $objects, array $needle): array
    {
        return array_reduce($needle, static function ($carry, $needle) use ($objects) {
            return $carry + array_filter($objects, static fn ($entity) => $entity::class === $needle);
        }, []);
    }
}

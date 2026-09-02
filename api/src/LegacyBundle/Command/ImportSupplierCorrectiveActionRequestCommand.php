<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Parts\SupplierCorrectiveActionRequestPart;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:scar')]
class ImportSupplierCorrectiveActionRequestCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper, IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports SCAR from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'legacyId');

        // Import SCAR
        $sql = <<<'SQL'
             SELECT id, poster_id, dt, dt_closed, dt_approval, ifactor, status, bu_id, supplier_bu_id, supplier_ref, supplier_name, short_desc, description, root_cause, action, commercial, conclusion, tld_assignor, sup_assignee, leader, verification_description, preventive_action
             FROM scar
             WHERE scar.poster_id != 0
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, SupplierCorrectiveActionRequest::class, 'id', 'id',
            function (SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest, array $data) use ($peopleCache, $locationCache) {
                $metadata = $this->entityManager->getClassMetaData(SupplierCorrectiveActionRequest::class);
                $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                $metadata->setIdGenerator(new AssignedGenerator());

                $reflectionProperty = new \ReflectionProperty(SupplierCorrectiveActionRequest::class, 'id');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($supplierCorrectiveActionRequest, $data['id']);

                $supplierCorrectiveActionRequest->poster = $peopleCache->fetch($data['poster_id']);
                $supplierCorrectiveActionRequest->createdAt = new \DateTime($data['dt']);
                $supplierCorrectiveActionRequest->closedAt = new \DateTime($data['dt_closed']);
                $supplierCorrectiveActionRequest->approvedAt = new \DateTime($data['dt_approval']);
                $supplierCorrectiveActionRequest->iFactor = \sprintf('IF%s', $data['ifactor']);
                $supplierCorrectiveActionRequest->factory = $locationCache->fetch($data['bu_id']);
                $supplierCorrectiveActionRequest->shortDescription = mb_trim($this->sanitationHelper->parse($data['short_desc']));
                $supplierCorrectiveActionRequest->description = mb_trim($this->sanitationHelper->parse($data['description']));
                if (null !== $data['root_cause']) {
                    $supplierCorrectiveActionRequest->issueOrigin = mb_trim($this->sanitationHelper->parse($data['root_cause']));
                }
                if (null !== $data['action']) {
                    $supplierCorrectiveActionRequest->correctiveAction = mb_trim($this->sanitationHelper->parse($data['action']));
                }
                if (null !== $data['commercial']) {
                    $supplierCorrectiveActionRequest->commercialAgreement = mb_trim($this->sanitationHelper->parse($data['commercial']));
                }
                if (null !== $data['conclusion']) {
                    $supplierCorrectiveActionRequest->conclusion = mb_trim($this->sanitationHelper->parse($data['conclusion']));
                }
                if ('0' !== $data['tld_assignor']) {
                    $supplierCorrectiveActionRequest->representative = $peopleCache->fetch($data['tld_assignor']);
                }
                if ('0' !== $data['leader']) {
                    $supplierCorrectiveActionRequest->leader = $peopleCache->fetch($data['leader']);
                }
                if (null !== $data['verification_description']) {
                    $supplierCorrectiveActionRequest->verificationDescription = mb_trim($this->sanitationHelper->parse($data['verification_description']));
                }
                if (null !== $data['preventive_action']) {
                    $supplierCorrectiveActionRequest->preventiveAction = mb_trim($this->sanitationHelper->parse($data['preventive_action']));
                }

                /** @var Location $supplierLocation */
                $supplierLocation = $locationCache->fetch($data['supplier_bu_id']);

                $supplierCorrectiveActionRequest
                    ->setStatus($data['status'])
                    ->setSupplierNumber($data['supplier_ref'])
                    ->setSupplierName($data['supplier_name'])
                ;
            }, false
        );

        // Import SCAR Parts
        $sql = <<<'SQL'
                SELECT mod_parts.id, mod_parts.parent_id, pn, dsc, qty, um
                FROM mod_parts
                LEFT JOIN scar on mod_parts.parent_id = scar.id AND mod_parts.module = 'SCAR'
                WHERE scar.poster_id != 0
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $scarCache = $this->cacheFactory->createEntityCache(SupplierCorrectiveActionRequest::class, 'id');

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, SupplierCorrectiveActionRequestPart::class, 'id', 'id',
            function (SupplierCorrectiveActionRequestPart $part, array $data) use ($scarCache) {
                $supplierCorrectiveActionRequest = $scarCache->fetch($data['parent_id']);
                if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
                    return;
                }

                $part->supplierCorrectiveActionRequest = $supplierCorrectiveActionRequest;
                $part->createdAt = $supplierCorrectiveActionRequest->createdAt;
                $part->createdBy = $supplierCorrectiveActionRequest->poster;
                $part->partNumber = mb_trim($this->sanitationHelper->parse($data['pn']));
                $part->quantity = 1;
                $part->description = mb_trim($this->sanitationHelper->parse($data['dsc']));
                $part->unitOfMeasure = 'EA';
            }, false
        );

        // Import SCAR Comment
        $sql = <<<'SQL'
                SELECT comment.id, comment.parent_id, dt, poster_type, poster_id, comment.comment, supplier_contact.lastname, supplier_contact.firstname
                FROM scar_comments AS comment
                LEFT JOIN vendors AS supplier_contact ON supplier_contact.id = comment.poster_id
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, Comment::class, 'legacyId', 'id',
            function (Comment $comment, array $data) use ($peopleCache) {
                $comment->discriminator = SupplierCorrectiveActionRequest::SCAR_COMMENT_DISCRIMINATOR;

                $metadata = [];
                if ('EVENDOR' === $data['poster_type']) {
                    $metadata = [
                        'firstname' => $data['firstname'],
                        'lastname' => $data['lastname'],
                    ];
                }

                if ('TLD' === $data['poster_type']) {
                    $comment->setUser($peopleCache->fetch($data['poster_id']));
                }

                $comment
                    ->setMessage(mb_trim($this->sanitationHelper->parse($data['comment'])))
                    ->setCreatedAt(new \DateTime($data['dt']))
                    ->setResource(\sprintf('%s/%s', $this->iriConverter->getIriFromResource(SupplierCorrectiveActionRequest::class, UrlGeneratorInterface::ABS_PATH, new GetCollection()), $data['parent_id']))
                ;

                $comment->metadata = $metadata;
            }, false
        );

        return Command::SUCCESS;
    }
}

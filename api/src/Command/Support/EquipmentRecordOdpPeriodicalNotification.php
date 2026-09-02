<?php

declare(strict_types=1);

namespace App\Command\Support;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Doctrine\Change;
use App\Entity\EquipmentRecord;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Repository\Common\LogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Parameter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:equipment_record:odp_notification')]
class EquipmentRecordOdpPeriodicalNotification extends Command
{
    private readonly LogRepository $logRepository;
    private readonly EquipmentRecordNotifier $notifier;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(LogRepository $logRepository, EquipmentRecordNotifier $notifier, IriConverterInterface $iriConverter)
    {
        parent::__construct();
        $this
            ->setDescription('Check the change on ER date and notify')
            ->addArgument('days', InputArgument::REQUIRED)
        ;

        $this->logRepository = $logRepository;
        $this->notifier = $notifier;
        $this->iriConverter = $iriConverter;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = $input->getArgument('days');

        $queryBuilder = $this->logRepository->createQueryBuilder('l');
        $queryBuilder
            ->where($queryBuilder->expr()->like('l.resource', ':iri'))
            ->andWhere('l.action = :update')
            ->andWhere('l.createdAt > :lastWeek')
            ->orderBy('l.id', Criteria::ASC)
            ->setParameters(new ArrayCollection([
                new Parameter('iri', $this->iriConverter->getIriFromResource(EquipmentRecord::class, UrlGeneratorInterface::ABS_PATH, new GetCollection()).'%'),
                new Parameter('update', Change::ACTION_UPDATE),
                new Parameter('lastWeek', new \DateTime("$days days ago")),
            ]));

        $lastEquipmentRecordsUpdated = $queryBuilder->getQuery()->getArrayResult();
        $asmList = [];
        $factoriesList = [];

        foreach ($lastEquipmentRecordsUpdated as $key => $log) {
            if (!\array_key_exists('estimatedGreenTagDate', $log['changeSet'])) {
                continue;
            }

            if (null === $log['changeSet']['estimatedGreenTagDate'][0]
                || null === $log['changeSet']['estimatedGreenTagDate'][1]
                || (new \DateTime($log['changeSet']['estimatedGreenTagDate'][0]))->format('Y-m-d') === (new \DateTime($log['changeSet']['estimatedGreenTagDate'][1]))->format('Y-m-d')
            ) {
                continue;
            }

            if ('1' === $days && (new \DateTime($log['changeSet']['estimatedGreenTagDate'][0]) > new \DateTime('+ 7 days'))) {
                continue;
            }

            try {
                /** @var EquipmentRecord $equipmentRecord */
                $equipmentRecord = $this->iriConverter->getResourceFromIri($log['resource']);
            } catch (\Exception $exception) {
                continue;
            }

            // Equipment Record without Order Factory, therefor without Order line are skipped
            if (null === $equipmentRecord->orderFactory) {
                continue;
            }

            $log['equipmentRecord'] = $equipmentRecord;

            $asmEmail = $equipmentRecord->getBuyer()?->getMainSalesRepresentative()?->asm->getEmail();
            if (null !== $asmEmail) {
                $asmList[$asmEmail][$equipmentRecord->getId()] = $log;
            }
            $factory = $equipmentRecord->getManufacturerLocation()?->getName();
            if (null !== $factory) {
                $factoriesList[$factory][$equipmentRecord->getId()] = $log;
            }
        }

        foreach ($asmList as $email => $equipmentRecords) {
            $this->notifier->sendAsmEstimatedGreenTagDatePeriodicalEmail($email, $equipmentRecords, $days);
        }

        foreach ($factoriesList as $factory => $equipmentRecords) {
            $this->notifier->sendLocationEstimatedGreenTagDatePeriodicalEmail($factory, $equipmentRecords, $days);
        }

        return Command::SUCCESS;
    }
}

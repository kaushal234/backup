<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:service:technician_on_call:survey')]
class ImportTechnicianOnCallSurveyCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<SQL
                SELECT id, survey_work, survey_responsiveness, survey_communication, survey_attitude, survey_comment
                FROM toc
                WHERE (
                    survey_work != '' OR
                    survey_responsiveness != '' OR
                    survey_communication != '' OR
                    survey_attitude != '' OR
                    survey_comment != ''
                )
                AND id in ($this->tocIdList)
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);

        $i = 0;
        foreach ($stmt->fetchAllAssociative() as $technicianOnCallSurvey) {
            $technicianONCall = $technicianOnCallRepository->findOneBy(['legacyId' => $technicianOnCallSurvey['id']]);

            if (!$technicianONCall) {
                $output->writeln(\sprintf('<error>TechnicianOnCall #%s not found</error>', $technicianOnCallSurvey['id']));
                continue;
            }

            $survey = new TechnicianOnCallSurvey();
            $survey->technicianOnCall = $technicianONCall;
            $survey->execution = (int) $technicianOnCallSurvey['survey_work'];
            $survey->responsiveness = (int) $technicianOnCallSurvey['survey_responsiveness'];
            $survey->communication = (int) $technicianOnCallSurvey['survey_communication'];
            $survey->attitude = (int) $technicianOnCallSurvey['survey_attitude'];
            $survey->comment = $technicianOnCallSurvey['survey_comment'];

            $this->entityManager->persist($survey);

            ++$i;

            if ($i > 500) {
                $this->entityManager->flush();
                $i = 0;
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

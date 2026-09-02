<?php

declare(strict_types=1);

namespace App\Command\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'api:toc:set_commissioning_confidential',
    description: 'Set all SOLVED and CLOSED Commissioning TOCs as confidential and add a comment',
)]
class TechnicianOnCallSetCommissioningConfidentialCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $technicianOnCalls = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(TechnicianOnCall::class, 't')
            ->join('t.serviceActivity', 'sa')
            ->andWhere('sa.name = :activity')
            ->andWhere('t.confidential = false')
            ->setParameter('activity', ServiceActivity::COMMISSIONING)
            ->getQuery()
            ->getResult();

        if ([] === $technicianOnCalls) {
            $io->success("No SOLVED or CLOSED Commissioning TOCs found with 'confidential=false'");

            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($technicianOnCalls as $toc) {
            $toc->confidential = true;

            $comment = new Comment();
            $comment->setResource($this->iriConverter->getIriFromResource($toc));
            $comment->setPublic(false);
            $comment->setMessage('Commissioning Period');
            $comment->discriminator = TechnicianOnCall::MODULE_NAME;

            $this->entityManager->persist($comment);

            ++$count;
        }

        $this->entityManager->flush();

        $io->success(\sprintf('%d Commissioning Solved or Closed TOCs set to confidential', $count));

        return Command::SUCCESS;
    }
}

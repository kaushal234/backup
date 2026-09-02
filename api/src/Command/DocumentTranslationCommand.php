<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\DocumentTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:document:translation:cleanup')]
class DocumentTranslationCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentTranslationRepository $documentTranslationRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $i = 0;
        foreach ($this->documentTranslationRepository->findDocumentTranslationToRemove() as $documentTranslation) {
            $this->entityManager->remove($documentTranslation);
            ++$i;

            if (0 === $i % 1000) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

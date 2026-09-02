<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Survey\PublishedSurvey;
use App\Notifier\Survey\SurveyNotifier;
use App\Repository\Survey\PublishedSurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'tld:survey:send')]
class SurveyEmailingCommand extends Command
{
    private readonly EntityManagerInterface $em;
    private readonly SurveyNotifier $notifier;
    private readonly ParameterBagInterface $parameters;

    public function __construct(EntityManagerInterface $em, SurveyNotifier $notifier, ParameterBagInterface $parameters)
    {
        parent::__construct();
        $this->setDescription('Send pending surveys');

        $this->addArgument('batch_size', InputArgument::OPTIONAL, 'Number of surveys sent', '100');

        $this->em = $em;
        $this->parameters = $parameters;
        $this->notifier = $notifier;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PublishedSurveyRepository $surveyRepository */
        $surveyRepository = $this->em->getRepository(PublishedSurvey::class);
        /** @var string $batchSize */
        $batchSize = $input->getArgument('batch_size');
        $surveys = $surveyRepository->getUnsentSurveys((int) $batchSize);

        $output->writeln(\sprintf('%s Sending %d emails', (new \DateTime())->format(\DateTimeInterface::ATOM), \count($surveys)));

        foreach ($surveys as $survey) {
            $survey->setSent(true);
            $this->em->persist($survey);

            $this->notifier->notifyCreation($survey);
        }

        $output->writeln(\sprintf('%s Emailing finished', (new \DateTime())->format(\DateTimeInterface::ATOM)));

        $this->em->flush();

        return 0;
    }
}

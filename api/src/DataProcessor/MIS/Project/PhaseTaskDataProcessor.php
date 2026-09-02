<?php

declare(strict_types=1);

namespace App\DataProcessor\MIS\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MIS\Project\Phase;
use App\Entity\Task\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @template T
 */
class PhaseTaskDataProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param Task $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $repository = $this->entityManager->getRepository(Phase::class);

        /** @var Phase $phase */
        $phase = $repository->find($uriVariables['id']);
        $phase->addTask($data);

        $this->persistProcessor->process($phase, $operation);

        return $data;
    }
}

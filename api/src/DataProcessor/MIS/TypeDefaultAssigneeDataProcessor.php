<?php

declare(strict_types=1);

namespace App\DataProcessor\MIS;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\MIS\TypeDefaultAssigneeBatch;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @template T
 */
class TypeDefaultAssigneeDataProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param TypeDefaultAssigneeBatch $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        foreach ($data->getModules() as $module) {
            $this->persistProcessor->process($module, $operation, $uriVariables, $context);
        }

        return $data;
    }
}

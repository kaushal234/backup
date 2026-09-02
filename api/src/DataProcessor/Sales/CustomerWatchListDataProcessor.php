<?php

declare(strict_types=1);

namespace App\DataProcessor\Sales;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Sales\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @template T
 */
class CustomerWatchListDataProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $repository = $this->entityManager->getRepository(Customer::class);

        /** @var Customer $customer */
        $customer = $repository->find($uriVariables['id']);

        $this->validator->validate($data);

        $customer
            ->setWatchList($data->isWatchList())
            ->setWatchListReason($data->isWatchList() ? $data->getWatchListReason() : null)
        ;

        return $this->persistProcessor->process($customer, $operation);
    }
}

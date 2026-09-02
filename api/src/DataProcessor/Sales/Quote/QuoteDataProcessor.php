<?php

declare(strict_types=1);

namespace App\DataProcessor\Sales\Quote;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Sales\Quote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Processor used on post operation to perform creation or update depending on the quote number.
 *
 * @template T
 */
class QuoteDataProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Quote $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $quoteRepository = $this->entityManager->getRepository(Quote::class);

        $queryBuilder = $quoteRepository->createQueryBuilder('quote');
        $quote = $queryBuilder
            ->where('quote.quoteNumber = :quoteNumber')
            ->andWhere('quote.deletedAt IS NULL')
            ->setParameter('quoteNumber', $data->quoteNumber)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if (null === $quote) {
            $quote = new Quote();
            $quote->quoteNumber = $data->quoteNumber;
        }

        $quote->xml = $data->xml;

        return $this->persistProcessor->process($quote, $operation);
    }
}

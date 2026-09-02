<?php

declare(strict_types=1);

namespace App\DataProcessor\Sales;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Dto\Manufacturing\LeadTimeBatch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @template T
 */
class LeadTimeDataProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private readonly ValidatorInterface $validator,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param LeadTimeBatch $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $violations = $this->validator->validate($data);
        $invalidLines = [];
        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $matches = [];
            if (!preg_match_all('#^leadTimes\[(?P<id>.+)].+#', $violation->getPropertyPath(), $matches, \PREG_SET_ORDER)) {
                continue;
            }
            $invalidLines[] = (int) $matches[0]['id'];
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        foreach ($data->getLeadTimes() as $index => $leadTime) {
            if (!\in_array($index, $invalidLines, true)) {
                if (!$this->security->isGranted('LEAD_TIME_WRITE_VOTER', $leadTime)) {
                    continue;
                }

                /** @var array<string, int> $previousLeadTime  Not totally true but will please PHPStan for the next operation */
                $previousLeadTime = $unitOfWork->getOriginalEntityData($leadTime);
                if ([] !== $previousLeadTime && ($data->fullUpdate || $leadTime->weeks !== $previousLeadTime['weeks'])) {
                    $leadTime->previousValue = $previousLeadTime['weeks'];
                }
                $data->addPersistedLeadTime($leadTime);
            }
        }

        foreach ($data->getPersistedLeadTimes() as $leadTime) {
            $this->persistProcessor->process($leadTime, $operation, $uriVariables, $context);
        }

        if (0 !== $violations->count()) {
            throw new ValidationException($violations);
        }

        return $data;
    }
}

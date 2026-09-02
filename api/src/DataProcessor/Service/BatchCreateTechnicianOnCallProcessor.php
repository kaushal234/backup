<?php

declare(strict_types=1);

namespace App\DataProcessor\Service;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Dto\Service\TechnicianOnCallDuplicateInput;
use App\Dto\Service\TechnicianOnCallDuplicateOutput;
use App\Entity\Service\TechnicianOnCall;
use App\Factory\Service\TechnicianOnCallCreatePayloadFactory;
use App\Factory\Service\TechnicianOnCallDuplicateFactory;
use App\Manager\Service\TechnicianOnCallDuplicateLineManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<TechnicianOnCallDuplicateInput, TechnicianOnCallDuplicateOutput>
 */
final class BatchCreateTechnicianOnCallProcessor implements ProcessorInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private TechnicianOnCallDuplicateFactory $cloneFactory,
        private TechnicianOnCallCreatePayloadFactory $payloadFactory,
        private TechnicianOnCallDuplicateLineManager $lineManager,
        private RequestStack $requestStack
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TechnicianOnCallDuplicateOutput
    {
        $this->validator->validate($data->inputLines);

        $originalTechnicianOnCall = $this->entityManager
            ->getRepository(TechnicianOnCall::class)
            ->find($uriVariables['id']);

        if (!$originalTechnicianOnCall instanceof TechnicianOnCall) {
            throw new NotFoundHttpException(\sprintf('TechnicianOnCall %s not found', $uriVariables['id'] ?? ''));
        }

        $output = new TechnicianOnCallDuplicateOutput();

        foreach ($data->inputLines as $input) {
            $clonedToc = $this->cloneFactory->duplicate($originalTechnicianOnCall, $input);
            $payload = $this->payloadFactory->fromClone($clonedToc, $input);

            $outputLine = $this->lineManager->createFromPayload($payload);
            $output->addLine($outputLine);

            if ($outputLine->technicianOnCallErrorMessage || $outputLine->customerServiceRecordErrorMessage) {
                $this->requestStack->getCurrentRequest()?->attributes->set('_toc_duplicate_has_errors', true);
            }
        }

        return $output;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use App\Doctrine\Voter\ActivityLogVoter;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportHelper
{
    private const DEFAULT_BATCH_SIZE = 100;
    private readonly EntityManagerInterface $em;
    private readonly ValidatorInterface $validator;
    private readonly PropertyAccessorInterface $accessor;
    private readonly SynchronizationVoter $syncVoter;
    private readonly ActivityLogVoter $activityLogVoter;
    private bool $validation = true;

    private bool $keepId = false;
    private int $batchSize = self::DEFAULT_BATCH_SIZE;

    /**
     * ImportHelper constructor.
     */
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        PropertyAccessorInterface $accessor,
        SynchronizationVoter $syncVoter,
        ActivityLogVoter $activityLogVoter,
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->accessor = $accessor;
        $this->syncVoter = $syncVoter;
        $this->activityLogVoter = $activityLogVoter;
    }

    public function progressiveImport(OutputInterface $output, Result $stmt, $repositoryName, $descProperty, $legacyDescProperty, callable $fillCallback, $replace = false, ?callable $skipCallback = null, bool $ignoreAlreadyExists = false)
    {
        $this->disableVoters();
        $this->em->getConnection()->getConfiguration()->setMiddlewares([new Middleware(new NullLogger())]);

        $itemRepository = $this->em->getRepository($repositoryName);

        $knownItems = [];
        foreach ($itemRepository->findAll() as $item) {
            $descr = $this->accessor->getValue($item, $descProperty);
            $knownItems[$descr] = $item;
        }

        $class = $itemRepository->getClassName();
        $output->writeln(\sprintf('<info>Already <comment>%d</comment> known %s</info>', \count($knownItems), $class));

        if (0 === $stmt->rowCount()) {
            $output->writeln('<info>Nothing to import</info>');
            $this->enableVoters();

            return $knownItems;
        }

        $progress = new ProgressBar($output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
        $progress->start($stmt->rowCount());

        $counter = 0;
        $operationCounter = 0;
        while ($data = $stmt->fetchAssociative()) {
            $descr = $this->accessor->getValue($data, '['.$legacyDescProperty.']');
            if (isset($knownItems[$descr]) && !$ignoreAlreadyExists) {
                if (!$replace || (null !== $skipCallback && $skipCallback($knownItems[$descr], $data))) {
                    $progress->advance();
                    continue;
                }

                $item = $knownItems[$descr];
            } else {
                $item = (new $class());
                try {
                    $this->accessor->setValue($item, $descProperty, $descr);
                } catch (NoSuchPropertyException $exception) {
                    // nothing
                }
                $knownItems[$descr] = $item;
            }

            try {
                $fillCallback($item, $data);

                if ($this->keepId) {
                    $this->setLegacyIdAsId($item, $data['id']);
                }
            } catch (\InvalidArgumentException $e) {
                $output->writeln('');
                $output->writeln(\sprintf('<error>%s</error>', $e->getMessage()));
                continue;
            }

            $this->em->persist($item);

            if ($this->validation && 0 < \count($violations = $this->validator->validate($item))) {
                $output->writeln('');
                /** @var ConstraintViolation $violation */
                foreach ($violations as $violation) {
                    $value = $violation->getInvalidValue();
                    if ($value instanceof \DateTime) {
                        $value = $value->format(\DATE_ATOM);
                    } elseif (\is_object($value)) {
                        $value = $value::class;
                    } elseif (\is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $output->writeln(\sprintf(
                        '<info>Validation failed for <comment>#%s</comment>: <comment>%s</comment> => <comment>%s</comment>: <comment>%s</comment></info>',
                        $descr,
                        (string) $violation->getMessage(),
                        $violation->getPropertyPath(),
                        $value
                    ));
                }
            }

            $progress->advance();
            ++$operationCounter;
            if ($counter++ > $this->batchSize) {
                $counter = 0;
                $this->em->flush();
            }
        }

        $this->em->flush();
        $progress->finish();

        $output->writeln('');
        $output->writeln(\sprintf('<info>Imported <comment>%d</comment> %s</info>', $operationCounter, $class));

        $this->enableVoters();
        $this->enableValidation();

        return $knownItems;
    }

    public function disableValidation()
    {
        $this->validation = false;
    }

    public function enableValidation()
    {
        $this->validation = true;
    }

    public function setKeepId(bool $keepId): void
    {
        $this->keepId = $keepId;
    }

    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    private function disableVoters()
    {
        $this->syncVoter->disable();
        $this->activityLogVoter->disable();
    }

    private function enableVoters()
    {
        $this->syncVoter->enable();
        $this->activityLogVoter->enable();
    }

    private function setLegacyIdAsId(object $object, int $legacyId)
    {
        $metadata = $this->em->getClassMetaData($object::class);
        $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        $reflectionProperty = new \ReflectionProperty($object::class, 'id');
        $reflectionProperty->setValue($object, $legacyId);
    }
}

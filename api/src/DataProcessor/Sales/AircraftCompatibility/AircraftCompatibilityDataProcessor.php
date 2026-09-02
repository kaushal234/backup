<?php

declare(strict_types=1);

namespace App\DataProcessor\Sales\AircraftCompatibility;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Sales\AircraftCompatibility\AircraftCompatibility;
use App\Entity\Sales\AircraftCompatibility\AircraftCompatibilityFile;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @template T
 */
class AircraftCompatibilityDataProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly PersistableFileManagerFactory $persistableFileManagerRegistry,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param AircraftCompatibility $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $request = $this->requestStack->getCurrentRequest();

        $files = $request->files->all();
        $types = $request->request->get('types');

        if (null === $types && \count($files) > 0) {
            throw new UnprocessableEntityHttpException('Type should be set if a file is sent.');
        }

        if (null !== $types) {
            $types = json_decode($request->request->get('types'), true);

            if (0 !== \count($files) && \count($types) !== \count($files)) {
                throw new UnprocessableEntityHttpException('Number of types sent should match number of files.');
            }

            foreach ($files as $index => $file) {
                $type = $types[$index];

                if ($file instanceof UploadedFile) {
                    $this->persistableFileManagerRegistry->getManagerForClass(AircraftCompatibilityFile::class)->attach($data, $file, ['type' => $type]);
                }
            }
        }

        $this->persistProcessor->process($data, $operation, $context);

        return $data;
    }
}

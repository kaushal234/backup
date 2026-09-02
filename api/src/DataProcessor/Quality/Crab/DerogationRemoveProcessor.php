<?php

declare(strict_types=1);

namespace App\DataProcessor\Quality\Crab;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Quality\Derogation;
use App\Entity\Quality\DerogationFile;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DerogationRemoveProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private readonly ProcessorInterface $decorated,
        #[Autowire(param: 'legacy.upload_dir')]
        private readonly string $legacyUploadDir,
        private readonly PersistableFileManagerFactory $persistableFileManagerRegistry,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (Derogation::ACCEPTED === $data->getStatus()) {
            throw new BadRequestHttpException('Not possible to delete an accepted derogation.');
        }

        foreach ($data->getCrabs() as $crab) {
            $crab->derogation = null;
        }

        foreach ($data->getFiles()->toArray() as $file) {
            try {
                $fileToDelete = new File($this->legacyUploadDir.'/'.$file->getFilePath());
            } catch (FileNotFoundException $e) {
                continue;
            }

            $this->persistableFileManagerRegistry->getManagerForClass(DerogationFile::class)->detach($data, $fileToDelete);
        }

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}

<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\File as ApiFile;
use App\Entity\User;
use App\Exception\InvalidFileException;
use App\FileSystem\AbstractAdapter;
use App\FileSystem\Persistence\ContextProviders\FileAdapterContextProviderInterface;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FileAdapter extends AbstractAdapter
{
    /**
     * @throws InvalidFileException
     */
    public function attach($subject, File $file, array &$metadata = []): string
    {
        /** @var FileAdapterContextProviderInterface $contextProvider */
        $contextProvider = $this->getContextProvider();
        if (false !== ($metadata['validate'] ?? true) && null !== $contextProvider->getFileConstraint($subject)) {
            $constraints = new Collection([$contextProvider->getFileProperty() => [$contextProvider->getFileConstraint($subject)]]);
            $errors = $this->locator->get(ValidatorInterface::class)->validate([$contextProvider->getFileProperty() => $file], $constraints);

            if ($errors->count() > 0) {
                InvalidFileException::cleanViolationPath($errors);
                throw new ValidationException($errors);
            }
        }

        $contextProvider->processFile($file, $metadata);

        $extension = $file->guessExtension() ?? $file->getExtension();
        $filename = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();
        $fileExtension = pathinfo($filename, \PATHINFO_EXTENSION);
        if (!$extension || ('bin' === $extension && $extension !== $fileExtension)) {
            $extension = $fileExtension;
        }

        if ((false === $nameLength = mb_strrpos($filename, \sprintf('.%s', $extension))) || $nameLength > ($metadata['maxNameLength'] ?? 15)) {
            $nameLength = $metadata['maxNameLength'] ?? 15;
        }
        $createdAt = null;
        if (isset($metadata['created_at']) && $metadata['created_at'] instanceof \DateTime) {
            $createdAt = $metadata['created_at'];
        }

        $newFilename = $contextProvider->getGeneratedFilename($subject, ['filename' => $filename, 'createdAt' => $createdAt, 'nameLength' => $nameLength, 'extension' => $extension, 'description' => ($metadata['description'] ?? null)]);

        $fileClass = $contextProvider::getClass();

        /** @var ApiFile $persistedFile */
        $persistedFile = $this->locator->get(PersistedFileFactory::class)->create($file, new $fileClass());
        $persistedDate = (new \DateTime())->format('Y/m');

        $persistedFile->setFilePath($contextProvider->getDirectory().'/'.$persistedDate.'/'.$newFilename);
        $persistedFile->setExtension($extension);

        $persistedFile->setDescription($metadata['description'] ?? null);
        if (isset($metadata['poster']) && $metadata['poster'] instanceof User) {
            $persistedFile->setPoster($metadata['poster']);
        } elseif (null !== ($user = $this->locator->get(Security::class)->getUser()) && $user instanceof User) {
            $persistedFile->setPoster($user);
        }

        if (null !== $createdAt) {
            $persistedFile->setCreatedAt($createdAt);
        }

        // Write metadatas values into persisted file properties.
        // Remove metadatas previously set.
        unset($metadata['created_at'], $metadata['poster'], $metadata['description']);
        $propertyAccessor = $this->locator->get(PropertyAccessorInterface::class);
        foreach ($metadata as $property => $value) {
            if ($propertyAccessor->isWritable($persistedFile, $property)) {
                $propertyAccessor->setValue($persistedFile, $property, $value);
            }
        }

        $persistedFile->setStatus(ApiFile::LIVE);

        $existingValue = $propertyAccessor->getValue($subject, $contextProvider->getFileProperty());

        if ($existingValue instanceof DoctrineCollection) {
            $value = $existingValue->toArray();
            $value[] = $persistedFile;
        } else {
            $value = $persistedFile;
            if (null !== $existingValue) {
                $propertyAccessor->setValue($subject, $contextProvider->getFileProperty(), null);
                $this->locator->get(EntityManagerInterface::class)->persist($subject);
                $this->locator->get(EntityManagerInterface::class)->flush();
                $metadata['obsolete_file'] = $existingValue;
            }
        }
        $propertyAccessor->setValue($subject, $contextProvider->getFileProperty(), $value);

        return $newFilename;
    }

    public function detach($subject, ?File $file = null)
    {
        /** @var FileAdapterContextProviderInterface $contextProvider */
        $contextProvider = $this->getContextProvider();
        if (null === $file) {
            return;
        }

        $existingValue = $this->locator->get(PropertyAccessorInterface::class)->getValue($subject, $contextProvider->getFileProperty());
        if ($existingValue instanceof DoctrineCollection) {
            $value = $this->locator->get(PropertyAccessorInterface::class)->getValue($subject, $contextProvider->getFileProperty())->filter(
                static fn (ApiFile $apiFile) => false === mb_strpos($apiFile->getFilePath(), $file->getFilename())
            );
        } else {
            $value = null;
        }

        $this->locator->get(PropertyAccessorInterface::class)->setValue($subject, $contextProvider->getFileProperty(), $value);
    }
}

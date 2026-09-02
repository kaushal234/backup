<?php

declare(strict_types=1);

namespace App\Controller\Purchasing\SupplierRanking;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Event\FileUploadedEvent;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UploadController extends AbstractController
{
    private readonly PersistableFileManagerFactory $persistableFileManagerFactory;
    private readonly EventDispatcherInterface $eventDispatcher;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(PersistableFileManagerFactory $persistableFileManagerFactory, EventDispatcherInterface $eventDispatcher, IriConverterInterface $iriConverter)
    {
        $this->persistableFileManagerFactory = $persistableFileManagerFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->iriConverter = $iriConverter;
    }

    public function __invoke($data, string $method, string $class, Request $request): object
    {
        if (!method_exists($data, $method)) {
            throw new \InvalidArgumentException(\sprintf('The method %s does not exist for class %s', $method, $data::class));
        }

        $metadata = [];
        if (null !== $description = $request->request->get('description')) {
            $metadata['description'] = $description;
        }

        if (null !== $public = $request->request->get('public')) {
            $metadata['public'] = $public;
        }

        if (null !== $category = $request->request->get('category')) {
            $metadata['category'] = $this->iriConverter->getResourceFromIri($category);
        }

        if (null !== $expiredAt = $request->request->get('expiredAt')) {
            $metadata['expiredAt'] = new \DateTime($expiredAt);
        }

        $this->persistableFileManagerFactory->getManagerForClass($class)->attach($data, $request->files->get('file'), $metadata);

        $metadata = $request->request->all('metadata');
        $this->eventDispatcher->dispatch(new FileUploadedEvent($data, $metadata));

        return (($data->{$method}() instanceof Collection) && (false !== ($last = $data->{$method}()->last()))) ? $last : $data->{$method}();
    }
}

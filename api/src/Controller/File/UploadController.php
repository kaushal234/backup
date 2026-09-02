<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Event\FileUploadedEvent;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UploadController extends AbstractController
{
    public function __construct(
        private readonly PersistableFileManagerFactory $persistableFileManagerFactory,
        private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function __invoke($data, string $method, string $class, Request $request): object
    {
        if (!method_exists($data, $method)) {
            throw new \InvalidArgumentException(\sprintf('The method %s does not exist for class %s', $method, $data::class));
        }

        $metadata = [];
        $metadataKeys = ['description', 'public', 'width', 'height', 'type'];

        foreach ($metadataKeys as $key) {
            if (null !== ($value = $request->request->get($key))) {
                $metadata[$key] = $value;
            }
        }

        $this->persistableFileManagerFactory->getManagerForClass($class)->attach($data, $request->files->get('file'), $metadata);

        $metadata = $request->request->all('metadata');
        $this->eventDispatcher->dispatch(new FileUploadedEvent($data, $metadata));

        return (($data->{$method}() instanceof Collection) && (false !== ($last = $data->{$method}()->last()))) ? $last : $data->{$method}();
    }
}

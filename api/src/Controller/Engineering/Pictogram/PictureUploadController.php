<?php

declare(strict_types=1);

namespace App\Controller\Engineering\Pictogram;

use App\Entity\Engineering\Pictogram\Pictogram;
use App\Event\FileUploadedEvent;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PictureUploadController extends AbstractController
{
    private readonly PersistableFileManagerFactory $persistableFileManagerFactory;
    private readonly EventDispatcherInterface $eventDispatcher;

    public function __construct(PersistableFileManagerFactory $persistableFileManagerFactory, EventDispatcherInterface $eventDispatcher)
    {
        $this->persistableFileManagerFactory = $persistableFileManagerFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke($data, string $method, string $class, Request $request): object
    {
        if (!method_exists($data, $method)) {
            throw new \InvalidArgumentException(\sprintf('The method %s does not exist for class %s', $method, $data::class));
        }

        $metadata = ['main' => true];
        $metadataKeys = ['description', 'public', 'width', 'height'];

        foreach ($metadataKeys as $key) {
            if (null !== ($value = $request->request->get($key))) {
                $metadata[$key] = $value;
            }
        }

        $file = $request->files->get('file');

        // Control the format on the main picture to only allow web image.
        // ContextProvider allows more format for the other upload route of Pictogram.
        if (!\in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/svg', 'image/gif'], true)) {
            throw new \InvalidArgumentException('The file must be a jpeg, png, svg or gif.');
        }

        $this->persistableFileManagerFactory->getManagerForClass($class)->attach($data, $file, $metadata);

        $metadata = $request->request->all('metadata');
        $this->eventDispatcher->dispatch(new FileUploadedEvent($data, $metadata));

        return (($data->{$method}() instanceof Collection) && (false !== ($last = $data->{$method}()->last()))) ? $last : $data->{$method}();
    }
}

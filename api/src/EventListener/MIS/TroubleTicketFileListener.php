<?php

declare(strict_types=1);

namespace App\EventListener\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Acl;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\TroubleTicketFile;
use App\Entity\MIS\TroubleTicket\Type;
use App\Repository\AclRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TroubleTicketFileListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onRequest', EventPriorities::PRE_READ],
            ],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $class = $request->attributes->get('class');
        if (TroubleTicketFile::class !== $class || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $troubleTicket = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(TroubleTicket::class)->findOneBy(['id' => $request->attributes->get('id')]);
        if (null === $troubleTicket) {
            return;
        }

        if (Type::INCIDENT !== $troubleTicket->type->type || Type::SECURITY_HIGH_ATTENTION !== $troubleTicket->type->description) {
            return;
        }

        $user = $this->serviceLocator->get(Security::class)->getUser();
        /** @var AclRepository $aclRepository */
        $aclRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(Acl::class);
        if ($aclRepository->userHasRoles($user, ['GG_MIS'])) {
            return;
        }

        $file = $request->files->get('file');
        $zipFilePath = \sprintf('/tmp/%s.zip', $file->getFilename());
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilePath, \ZipArchive::CREATE)) {
            throw new \Exception('Impossible to open ZIP file');
        }
        $zip->addFile($file->getPathname(), $file->getClientOriginalName());
        $zip->setPassword('infected');
        $zip->setEncryptionName($file->getClientOriginalName(), \ZipArchive::EM_AES_256);
        $zip->close();

        $uploadedFile = new UploadedFile(
            $zipFilePath,
            'file.zip',
            'application/zip',
            null,
            true
        );

        $request->files->set('file', $uploadedFile);
    }

    public static function getSubscribedServices(): array
    {
        return [
            IriConverterInterface::class,
            EntityManagerInterface::class,
            Security::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\EventListener\Directory\People;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Acl;
use App\Entity\AI\AILog;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Entity\UserSetting;
use App\Repository\AclRepository;
use App\Repository\AI\AILogRepository;
use App\Repository\UserSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PeopleDisableListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    final public const string GROUP_AUTH_NAME = 'ACL_AUTH_INTRANET';

    public function __construct(
        private readonly ContainerInterface $serviceLocator,
        private readonly string $legacyUploadDir,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['postWrite', EventPriorities::POST_WRITE],
        ];
    }

    public function postWrite(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$user instanceof People || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var People $previousData */
        $previousData = $request->attributes->get('previous_data');

        if ($previousData->isDisabled() || !$user->isDisabled()) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        $this->removeAcl($entityManager, $user);
        $this->removeAILogs($entityManager, $user);
        $this->removeUserSettings($entityManager, $user);
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            Filesystem::class,
        ];
    }

    private function removeAcl(EntityManagerInterface $entityManager, People $user): void
    {
        /** @var AclRepository $aclRepo */
        $aclRepo = $entityManager->getRepository(Acl::class);
        /** @var EntityRepository<Group> $groupRepository */
        $groupRepository = $entityManager->getRepository(Group::class);

        $group = $groupRepository->findOneBy(['name' => self::GROUP_AUTH_NAME]);
        if (null !== $group) {
            $aclRepo->removeAclByGroup($user, $group);
            $entityManager->flush();
        }
    }

    private function removeAILogs(EntityManagerInterface $entityManager, People $user): void
    {
        /** @var AILogRepository $logRepository */
        $logRepository = $entityManager->getRepository(AILog::class);

        $filePaths = $logRepository->findFilePathsByPeople($user);

        $logRepository->deleteAllForPeople($user);

        if ([] !== $filePaths) {
            $filesystem = $this->serviceLocator->get(Filesystem::class);
            foreach ($filePaths as $filePath) {
                $filesystem->remove($this->legacyUploadDir.'/'.$filePath);
            }
        }
    }

    private function removeUserSettings(EntityManagerInterface $entityManager, People $user): void
    {
        /** @var UserSettingRepository $repository */
        $repository = $entityManager->getRepository(UserSetting::class);

        $repository->deleteAllForPeople($user);
    }
}

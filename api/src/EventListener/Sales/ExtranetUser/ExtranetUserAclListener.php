<?php

declare(strict_types=1);

namespace App\EventListener\Sales\ExtranetUser;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\ExtranetUserAcl;
use App\Manager\Sales\ExtranetUserManager;
use App\Notifier\Tasks\SequenceNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Manager\TaskCommentsManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ExtranetUserAclListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    /**
     * @var string
     */
    final public const EXTRANET_USER_DIFFERENT_CUSTOMER_SEQUENCE_TEMPLATE = 'sales.extranetuser.differentCustomerApproval';

    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onPreDelete(ViewEvent $event)
    {
        $extranetUserAcl = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$extranetUserAcl instanceof ExtranetUserAcl || Request::METHOD_DELETE !== $request->getMethod()) {
            return;
        }

        $crt = $extranetUserAcl->getCrt();
        if (1 === $crt->getAcls()->count()) {
            $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
            $entityManager->remove($crt);
        }
    }

    public function onCreate(ViewEvent $event)
    {
        $extranetUserAcl = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$extranetUserAcl instanceof ExtranetUserAcl || Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $extranetUser = $extranetUserAcl->getExtranetUser();
        if ($extranetUser->isDisabled() || null === ($customer = $extranetUserAcl->getCrt()->getCustomer()) || $customer === $extranetUser->getExtranetUserProfile()->customer) {
            return;
        }

        $sequenceManager = $this->serviceLocator->get(SequenceManager::class);
        if (!empty($sequenceManager->findOpenSequence(self::EXTRANET_USER_DIFFERENT_CUSTOMER_SEQUENCE_TEMPLATE, $extranetUser->getLegacyId()))) {
            return;
        }

        $extranetUserManager = $this->serviceLocator->get(ExtranetUserManager::class);
        $asm = $customer->getMainSalesRepresentative()->asm;
        $sequence = $extranetUserManager->createExtranetUserSequenceAndSetParameters($extranetUser, $asm->getBusinessUnit(), $asm, self::EXTRANET_USER_DIFFERENT_CUSTOMER_SEQUENCE_TEMPLATE, 104, \sprintf('Extranet User #%d</a> request access for another eCustomer', $extranetUser->getId()));

        try {
            $sequenceId = $sequenceManager->insert($sequence);
        } catch (\Exception $exception) {
            throw new \LogicException('Sequence not inserted. Reason: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $comment = \sprintf('The eContact of this TASK has been linked to a CRT on an ECUST for which you are the ASM (%s).
If you validate this sequence, this eContact will have access to the Extranet of your ECUST, to which he or she does not belong according to their profile.
Please confirm.', $customer->getName());

        $extranetUserProfile = $extranetUser->getExtranetUserProfile();
        $extranetUserProfile->sequenceIds = [...$extranetUserProfile->sequenceIds, $sequenceId];
        $extranetUser->setExtranetUserProfile($extranetUserProfile);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->persist($extranetUser);
        $entityManager->flush();

        $this->serviceLocator->get(TaskCommentsManager::class)->insertComment($sequence, $comment, $asm);
        $this->serviceLocator->get(SequenceNotifier::class)->sendEmail($sequence);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPreDelete', EventPriorities::PRE_WRITE],
                ['onCreate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            UrlGeneratorInterface::class,
            SequenceManager::class,
            TaskCommentsManager::class,
            SequenceNotifier::class,
            ExtranetUserManager::class,
        ];
    }
}

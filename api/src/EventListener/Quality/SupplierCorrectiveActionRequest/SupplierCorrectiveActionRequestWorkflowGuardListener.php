<?php

declare(strict_types=1);

namespace App\EventListener\Quality\SupplierCorrectiveActionRequest;

use App\Entity\Feature;
use App\Entity\Group;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Manager\Directory\PeopleManager;
use App\Repository\FeatureRepository;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SupplierCorrectiveActionRequestWorkflowGuardListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    final public const FEATURE_SCAR_STATUS_ADMIN = 'FEATURE_SCAR_STATUS_ADMIN';
    final public const FEATURE_SCAR_STATUS = 'FEATURE_SCAR_STATUS';

    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.supplier_corrective_action_request.guard.to_validation' => ['guardValidation'],
            'workflow.supplier_corrective_action_request.guard.to_closed' => ['guardClosed'],
            'workflow.supplier_corrective_action_request.guard.to_commercial_agreement' => ['guardCommercialAgreement'],
            'workflow.supplier_corrective_action_request.guard.to_vendor_to_fill_form' => ['guardVendorToFillForm'],
            'workflow.supplier_corrective_action_request.guard.to_tld_to_review_form' => ['guardTldToReviewForm'],
        ];
    }

    public function guardVendorToFillForm(GuardEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getSubject();
        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
            return;
        }

        if (SupplierCorrectiveActionRequest::CLOSED === $supplierCorrectiveActionRequest->getStatus()) {
            if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
                $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS_ADMIN, $event);
            }

            return;
        }

        if (\in_array($supplierCorrectiveActionRequest->getStatus(), [SupplierCorrectiveActionRequest::PENDING, SupplierCorrectiveActionRequest::VALIDATION], true)
            && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS) && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
            $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS, $event);
        }
    }

    public function guardTldToReviewForm(GuardEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getSubject();
        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
            return;
        }

        if (SupplierCorrectiveActionRequest::CLOSED === $supplierCorrectiveActionRequest->getStatus()) {
            if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
                $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS_ADMIN, $event);
            }

            return;
        }

        if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS) && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
            $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS, $event);
        }
    }

    public function guardValidation(GuardEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getSubject();
        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
            return;
        }

        if (null === $supplierCorrectiveActionRequest->issueOrigin || '' === $supplierCorrectiveActionRequest->issueOrigin
            || null === $supplierCorrectiveActionRequest->correctiveAction || '' === $supplierCorrectiveActionRequest->correctiveAction
            || null === $supplierCorrectiveActionRequest->preventiveAction || '' === $supplierCorrectiveActionRequest->preventiveAction) {
            $event->addTransitionBlocker(new TransitionBlocker('Root cause or corrective action or preventive action fields not set.', TransitionBlocker::UNKNOWN));

            return;
        }

        if (SupplierCorrectiveActionRequest::CLOSED === $supplierCorrectiveActionRequest->getStatus()) {
            if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
                $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS_ADMIN, $event);
            }

            return;
        }

        if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS)) {
            $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS, $event);
        }
    }

    public function guardCommercialAgreement(GuardEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getSubject();
        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
            return;
        }

        if (SupplierCorrectiveActionRequest::CLOSED === $supplierCorrectiveActionRequest->getStatus()) {
            if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
                $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS_ADMIN, $event);
            }

            return;
        }

        if (null === $supplierCorrectiveActionRequest->commercialAgreement || '' === $supplierCorrectiveActionRequest->commercialAgreement) {
            $event->addTransitionBlocker(new TransitionBlocker('Commercial agreement field not set.', TransitionBlocker::UNKNOWN));

            return;
        }

        if (\in_array($supplierCorrectiveActionRequest->iFactor, [SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1, SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_10], true)
            && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS) && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
            $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS, $event, 'for ifactor 1 and 10');

            return;
        }

        if (\in_array($supplierCorrectiveActionRequest->iFactor, [SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_100, SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1000], true)
            && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
            $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS_ADMIN, $event, 'for ifactor 100 and 1000');
        }
    }

    public function guardClosed(GuardEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getSubject();
        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
            return;
        }

        $fields = [
            'Root cause' => $supplierCorrectiveActionRequest->issueOrigin,
            'Corrective Action' => $supplierCorrectiveActionRequest->correctiveAction,
            'Preventive Action' => $supplierCorrectiveActionRequest->preventiveAction,
        ];

        if (\in_array($supplierCorrectiveActionRequest->getStatus(), [SupplierCorrectiveActionRequest::TLD_TO_REVIEW_FORM, SupplierCorrectiveActionRequest::VALIDATION], true)) {
            $fields['Commercial Agreement'] = $supplierCorrectiveActionRequest->commercialAgreement;
        }

        if (SupplierCorrectiveActionRequest::COMMERCIAL_AGREEMENT === $supplierCorrectiveActionRequest->getStatus()) {
            $fields = [
                'Verification Description' => $supplierCorrectiveActionRequest->verificationDescription,
                'Conclusion' => $supplierCorrectiveActionRequest->conclusion,
            ];
        }

        foreach ($fields as $key => $value) {
            if (null === $value || '' === $value) {
                $emptyFields[] = $key;
            }
        }

        if (!empty($emptyFields)) {
            $event->addTransitionBlocker(new TransitionBlocker(\sprintf('At least one of these fields (%s) is not set.', implode(', ', $emptyFields)), TransitionBlocker::UNKNOWN));

            return;
        }

        $security = $this->serviceLocator->get(Security::class);
        switch ($supplierCorrectiveActionRequest->iFactor) {
            case SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1:
            case SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_10:
                if (!$security->isGranted(self::FEATURE_SCAR_STATUS) && !$security->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
                    $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS, $event);
                }

                return;
            case SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_100:
                if (!PeopleManager::hasOneOfGroups($security->getUser(), ['ROLE_CEO', 'ROLE_COO'], $supplierCorrectiveActionRequest->factory)) {
                    $event->addTransitionBlocker(new TransitionBlocker('You do not have permissions to do this. Only CEO and COO can close IF 100 SCAR.', TransitionBlocker::UNKNOWN));
                }

                return;
            case SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1000:
                if (!PeopleManager::hasOneOfGroups($security->getUser(), ['ROLE_CEO', 'ROLE_RCOO'], $supplierCorrectiveActionRequest->factory)) {
                    $event->addTransitionBlocker(new TransitionBlocker('You do not have permissions to do this. Only CEO and RCOO can close IF 1000 SCAR.', TransitionBlocker::UNKNOWN));
                }

                return;
        }

        if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS) || !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_SCAR_STATUS_ADMIN)) {
            $this->generateGroupTransitionBlocker(self::FEATURE_SCAR_STATUS, $event);
        }
    }

    public function generateGroupTransitionBlocker(string $featureName, GuardEvent $event, string $message = '')
    {
        /** @var Feature $feature */
        $feature = $this->serviceLocator->get(FeatureRepository::class)->findOneBy(['name' => $featureName]);
        $authorizedGroup = implode(', ', array_map(static fn (Group $group) => $group->getName(), $feature->getGroups()->toArray()));
        $event->addTransitionBlocker(new TransitionBlocker(\sprintf('You do not have permissions to do this. Authorized group: %s %s', $authorizedGroup, $message), TransitionBlocker::UNKNOWN));
    }

    public static function getSubscribedServices(): array
    {
        return [
            FeatureRepository::class,
            Security::class,
        ];
    }
}

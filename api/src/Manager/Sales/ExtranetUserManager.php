<?php

declare(strict_types=1);

namespace App\Manager\Sales;

use App\Entity\Country;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Network;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\SalesArea;
use App\Entity\User;
use App\Manager\UserManager;
use App\Notifier\Directory\PeopleExtranetAccessNotifier;
use App\Notifier\Tasks\SequenceNotifier;
use App\Repository\Directory\PeopleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Manager\TaskCommentsManager;
use LegacyBundle\Model\Sequence;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtranetUserManager
{
    /**
     * @var string
     */
    final public const EXTRANET_USER_APPROVAL_SEQUENCE_TEMPLATE = 'sales.extranetuser.approval';
    /**
     * @var string
     */
    final public const MODULE = 'XU';

    private readonly UserManager $userManager;
    private readonly EntityManagerInterface $entityManager;
    private readonly SequenceManager $sequenceManager;
    private readonly ValidatorInterface $validator;
    private readonly TaskCommentsManager $taskCommentManager;
    private readonly UrlGeneratorInterface $router;
    private readonly SequenceNotifier $sequenceNotifier;
    private readonly PeopleExtranetAccessNotifier $peopleExtranetAccessNotifier;
    private readonly LoggerInterface $logger;

    public function __construct(UserManager $userManager, EntityManagerInterface $entityManager, SequenceManager $sequenceManager, ValidatorInterface $validator, TaskCommentsManager $taskCommentManager, UrlGeneratorInterface $router, SequenceNotifier $sequenceNotifier, PeopleExtranetAccessNotifier $peopleExtranetAccessNotifier, LoggerInterface $logger)
    {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->sequenceManager = $sequenceManager;
        $this->validator = $validator;
        $this->taskCommentManager = $taskCommentManager;
        $this->router = $router;
        $this->sequenceNotifier = $sequenceNotifier;
        $this->peopleExtranetAccessNotifier = $peopleExtranetAccessNotifier;
        $this->logger = $logger;
    }

    public function handleExtranetUserRequest(ExtranetUser $user)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $existingUsers = $userRepository->findByEmailOrUsername($user->getEmail());

        if ([] === $existingUsers) {
            $this->userManager->generatePassword($user);

            try {
                $this->entityManager->persist($user);
            } catch (\Exception $exception) {
                $this->logger->critical('Could not persist Extranet User. Reason : {reason}', ['reason' => $exception->getMessage()]);

                return;
            }

            $this->entityManager->flush();
            try {
                $this->generateSequence($user);
            } catch (\LogicException $exception) {
                $this->logger->critical($exception->getMessage());
            }

            return;
        }

        $existingDisabledExtranetUserAccount = null;
        foreach ($existingUsers as $existingUser) {
            if ($existingUser->isDisabled()) {
                if ($existingUser instanceof ExtranetUser) {
                    $existingDisabledExtranetUserAccount = $existingUser;
                }
                continue;
            }

            if ($existingUser instanceof ExtranetUser) {
                $this->userManager->resetPasswordConfirmation($existingUser);

                return;
            }

            if ($existingUser instanceof People) {
                $this->peopleExtranetAccessNotifier->sendEmail($existingUser);

                return;
            }
        }

        if (null !== $existingDisabledExtranetUserAccount) {
            try {
                $this->generateSequence($existingDisabledExtranetUserAccount);
            } catch (\LogicException $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
    }

    public function generateSequence(ExtranetUser $extranetUser)
    {
        $openSequence = $this->sequenceManager->findOpenSequence(self::EXTRANET_USER_APPROVAL_SEQUENCE_TEMPLATE, $extranetUser->getLegacyId());
        if (false !== $openSequence) {
            throw new \LogicException(\sprintf('Extranet access sequence %d is already open for this extranet user', $openSequence['id']));
        }

        $assignee = null;
        $businessUnit = null;

        /** @var Country|null $country */
        $country = $extranetUser->getExtranetUserProfile()->country;

        $salesAreas = $this->entityManager->getRepository(SalesArea::class)->findBy(['country' => $country]);

        if (null !== $country && [] !== $salesAreas) {
            foreach ($salesAreas as $salesArea) {
                if (null === $salesArea->getSso()->getNetwork()) {
                    continue;
                }

                if (Network::NETWORK_TLD !== $salesArea->getSso()->getNetwork()->getName()) {
                    continue;
                }

                if (null === ($businessUnit = $salesArea->getAsm()->getBusinessUnit())) {
                    continue;
                }

                $location = $salesArea->getSso();

                /** @var PeopleRepository $peopleRepository */
                $peopleRepository = $this->entityManager->getRepository(People::class);

                /** @var People[] assignees */
                $assignees = $peopleRepository->findGroupMembers('ROLE_SXU', $location);

                if ([] !== $assignees) {
                    /** @var People $assignee */
                    $assignee = $assignees[0];
                    break;
                }
            }
        }

        if (!$assignee && null !== $module = $this->entityManager->getRepository(Module::class)->findOneBy(['name' => self::MODULE])) {
            $assignee = $module->getOperationalOwner();
        }

        if (!$assignee instanceof People) {
            throw new \LogicException('No assignee found for Extranet User #'.$extranetUser->getId());
        }

        if (null === ($businessUnit ??= $assignee->getBusinessUnit())) {
            throw new \LogicException('No business unit found for'.$assignee->getFirstname().' '.$assignee->getLastname());
        }

        $sequence = $this->createExtranetUserSequenceAndSetParameters($extranetUser, $businessUnit, $assignee, self::EXTRANET_USER_APPROVAL_SEQUENCE_TEMPLATE, 56, \sprintf('Extranet User #%d</a> Request', $extranetUser->getId()));

        try {
            $sequenceId = $this->sequenceManager->insert($sequence);
            $extranetUser->getExtranetUserProfile()->sequenceIds = [...$extranetUser->getExtranetUserProfile()->sequenceIds, (string) $sequenceId];

            $this->entityManager->persist($extranetUser);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw new \LogicException('Sequence not inserted. Reason : '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $violations = $this->validator->validate($extranetUser);
        $comment = 'Please make sure to update the following before ANY action on this sequence: ';

        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $comment = \sprintf("%s\n - %s: %s",
                    $comment,
                    $violation->getInvalidValue(),
                    (string) $violation->getMessage());
            }
            $this->taskCommentManager->insertComment($sequence, $comment);
        }

        $this->sequenceNotifier->sendEmail($sequence);
    }

    public function createExtranetUserSequenceAndSetParameters(ExtranetUser $extranetUser, BusinessUnit $businessUnit, People $assignee, string $templateName, int $templateNumber, string $description): Sequence
    {
        /** @var Country|null $country */
        $country = $extranetUser->getExtranetUserProfile()->country;
        $countryName = null !== $country ? $country->getName() : '';

        $route = $this->router->generate('extranet_users', ['id' => $extranetUser->getId()], Router::ABSOLUTE_URL);

        $sequence = new Sequence();
        $sequence
            ->setTemplateName($templateName)
            ->setCloseParams([
                'assignor' => $assignee,
                'assignee' => $assignee,
                'seqTpl' => $templateNumber,
                'module' => 'SEQ',
                'lastname' => $extranetUser->getLastname(),
                'firstname' => $extranetUser->getFirstname(),
                'company_name' => $extranetUser->getExtranetUserProfile()->companyName,
                'division' => $extranetUser->getExtranetUserProfile()->division,
                'department' => $extranetUser->getExtranetUserProfile()->department,
                'title' => $extranetUser->getExtranetUserProfile()->jobTitle,
                'email' => $extranetUser->getUserIdentifier(),
                'address' => $extranetUser->getExtranetUserProfile()->legacyAddress,
                'country_temp' => $countryName,
            ])
            ->setParentId($extranetUser->getLegacyId())
            ->setDescription(
                \sprintf("<a href='$route'>%s:<br>
                                    Lastname: %s,<br>
                                    Firstname: %s,<br>
                                    Company: %s,<br>
                                    Job Title: %s,<br>
                                    Department: %s,<br>
                                    Email: %s,<br>
                                    Country: %s,<br>
                                    Address: %s<br>",
                    $description,
                    $extranetUser->getLastname(),
                    $extranetUser->getFirstname(),
                    $extranetUser->getExtranetUserProfile()->companyName,
                    $extranetUser->getExtranetUserProfile()->jobTitle,
                    $extranetUser->getExtranetUserProfile()->department,
                    $extranetUser->getUserIdentifier(),
                    $countryName,
                    $extranetUser->getExtranetUserProfile()->legacyAddress
                ))
            ->setLocation($businessUnit->getLocation())
            ->setAssignee($assignee)
            ->setAssignor($assignee)
        ;

        return $sequence;
    }

    public function disableContact(ExtranetUser $extranetUser, bool $archive = false): ExtranetUser
    {
        $extranetUser->setDisabled(true);

        if ($archive) {
            $extranetUser->setHidden(true);
            $extranetUser->getExtranetUserProfile()->archived = true;
        }

        $aclsToRemove = $archive ? $extranetUser->getExtranetUserAcls() : [];
        foreach ($aclsToRemove as $acl) {
            $this->entityManager->remove($acl);
        }

        return $extranetUser;
    }
}

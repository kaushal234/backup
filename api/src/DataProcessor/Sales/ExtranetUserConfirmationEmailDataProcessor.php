<?php

declare(strict_types=1);

namespace App\DataProcessor\Sales;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Sales\ExtranetUserConfirmationEmailInput;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Manager\UserManager;
use App\Notifier\Sales\ExtranetUser\ExtranetUserNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @template T
 */
final class ExtranetUserConfirmationEmailDataProcessor implements ProcessorInterface
{
    private readonly ValidatorInterface $validator;
    private readonly ExtranetUserNotifier $notifier;
    private readonly UserManager $userManager;
    private readonly EntityManagerInterface $entityManager;
    private readonly Security $security;

    public function __construct(ExtranetUserNotifier $notifier, ValidatorInterface $validator, UserManager $userManager, EntityManagerInterface $entityManager, Security $security)
    {
        $this->validator = $validator;
        $this->notifier = $notifier;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     *
     * @param ExtranetUserConfirmationEmailInput $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $repository = $this->entityManager->getRepository(ExtranetUser::class);
        /** @var ExtranetUser $extranetUser */
        $extranetUser = $repository->find($uriVariables['id']);

        $this->userManager->generatePassword($extranetUser);
        $this->entityManager->persist($extranetUser);
        $this->entityManager->flush();

        $this->validator->validate($data);

        $user = $this->security->getUser();
        if (!$user instanceof People) {
            return $extranetUser;
        }

        $emailContext = [
            'message' => $data->message,
            'password' => $extranetUser->getClearPassword(),
        ];

        foreach (['ccs' => $data->getCcs(), 'bccs' => $data->getBccs()] as $key => $value) {
            foreach ($value as $people) {
                /* @var People $people */
                $emailContext[$key][] = $people->getEmail();
            }
        }

        $this->notifier->sendConfirmationEmail($extranetUser, $user, $data->subject, $emailContext);

        return $extranetUser;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use App\Entity\User;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ExtranetUserUpdateController extends AbstractController
{
    private readonly Security $security;
    private readonly UserManager $userManager;
    private readonly EntityManagerInterface $entityManager;
    private readonly DecoderInterface $decoder;

    public function __construct(Security $security, UserManager $userManager, EntityManagerInterface $entityManager, DecoderInterface $decoder)
    {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->decoder = $decoder;
        $this->security = $security;
    }

    public function __invoke(ExtranetUser $data, Request $request)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (null !== $data->getClearPassword()) {
            $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);

            $this->entityManager->refresh($user);

            if (!\array_key_exists('token', $content) || $user->getToken() !== $content['token']) {
                throw new BadRequestHttpException('Token is not valid, your password has not been updated');
            }

            $this->userManager->encodePassword($data);

            $data->setToken('');
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use App\Entity\Directory\People;
use App\Manager\UserManager;
use App\Repository\Directory\PeopleRepository;
use App\Repository\UserConnectionRepository;
use App\Security\JWT\JWTEncoder;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PeopleSageAuthController extends AbstractController
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly JWTEncoderInterface $encoder,
        private readonly LoggerInterface $logger,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JWTEncoderInterface $jwtEncoder,
        private readonly UserConnectionRepository $userConnectionRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $sagepartsPayload = $this->encoder->decode($request->request->get('token'));
        } catch (JWTDecodeFailureException $exception) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $exception->getReason()) {
                throw $this->createAccessDeniedException('Expired token');
            }

            // Token is created by Sageparts so we can't verify the signature but an error is logged if something went wrong while decoding the token
            if (JWTDecodeFailureException::UNVERIFIED_TOKEN !== $exception->getReason()) {
                $this->logger->error('Something went wrong decoding the Sage token: {error}', [
                    'error' => $exception->getMessage(),
                    'exception' => $exception,
                ]);
            }

            $sagepartsPayload = $exception->getPayload();
        }

        /*
         * upn is the name on the key User Principal Name, in the token received by Sageparts Azure authenticator.
         * It contains the email of the user
         */
        if (!($sagepartsPayload['upn'] ?? null)) {
            throw new UnauthorizedHttpException('User not allowed');
        }

        /** @var People|null $people */
        $people = $this->peopleRepository->findOneBy([
            'username' => $sagepartsPayload['upn'],
            'disabled' => false,
            'hidden' => false,
        ]);

        if (!$people || false === mb_stripos((string) $sagepartsPayload['upn'], 'sageparts.com')) {
            throw new UnauthorizedHttpException('User not allowed');
        }

        $this->tokenStorage->setToken(new PreAuthenticatedToken($people, 'token'));

        /*
         * Now we create an API token for the user that will be valid 1 day
         */
        $apiPayload = [];
        $apiPayload[JWTEncoder::PAYLOAD_USER_KEY] = $people;
        $apiPayload[UserManager::JWT_PROPERTY_USERNAME] = $people->getUserIdentifier();
        $apiPayload['from'] = 'sso';
        $apiPayload[RegisteredClaims::EXPIRATION_TIME] = new \DateTimeImmutable('+1 day');

        $this->userConnectionRepository->addActivityLog($people, 'sso-sageparts');

        return new JsonResponse(
            [
                'token' => $this->jwtEncoder->encode($apiPayload),
                'username' => $people->getUserIdentifier(),
            ]
        );
    }
}

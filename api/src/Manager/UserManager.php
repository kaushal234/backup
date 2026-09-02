<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\AuthorizedApplication;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Sales\ExtranetUser;
use App\Entity\User;
use App\Notifier\User\UserPasswordNotifier;
use App\Repository\UserRepository;
use App\Request\Activity\CommentRequestManager;
use App\Security\JWT\JWTEncoder;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserManager
{
    /**
     * @var string
     */
    final public const LOGIN_PORTAL = 'portal';
    /**
     * @var string
     */
    final public const LINK_PORTAL = 'link';
    /**
     * @var string
     */
    final public const JWT_PROPERTY_USERNAME = 'username';
    /**
     * @var string
     */
    final public const JWT_PROPERTY_ORIGIN = 'origin';
    /**
     * @var string
     */
    private const PASSWORD_LOWERCASE_CHARS = 'abcdefghijklmnopqrstuvwxyz';
    /**
     * @var string
     */
    private const PASSWORD_UPPERCASE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    /**
     * @var string
     */
    private const PASSWORD_SPECIAL_CHARS = '_@$#&%!.';
    /**
     * @var string
     */
    private const PASSWORD_NUMBERS = '0123456789';

    private readonly UserPasswordHasherInterface $userPasswordHasher;
    private readonly JWTEncoderInterface $jwtEncoder;
    private readonly TokenStorageInterface $tokenStorage;
    private readonly UserRepository $userRepository;
    private readonly CommentRequestManager $commentManager;
    private readonly ManagerRegistry $registry;
    private readonly UserPasswordNotifier $notifier;
    private readonly ResetPasswordHelperInterface $resetPasswordHelper;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        TokenStorageInterface $tokenStorage,
        JWTEncoderInterface $jwtEncoder,
        UserRepository $userRepository,
        UserPasswordNotifier $notifier,
        CommentRequestManager $commentManager,
        ManagerRegistry $registry,
        ResetPasswordHelperInterface $resetPasswordHelper
    ) {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->jwtEncoder = $jwtEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
        $this->commentManager = $commentManager;
        $this->registry = $registry;
        $this->notifier = $notifier;
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    public function resetPasswordConfirmation(User $user): void
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        $this->notifier->sendConfirmation($user, $resetToken->getToken());
    }

    public function generatePassword(User $user): void
    {
        $user->setPassword($this->getRandomPassword());
        $this->encodePassword($user);
    }

    public function getRandomPassword(): string
    {
        $password = [];

        $requirements = [
            self::PASSWORD_LOWERCASE_CHARS.self::PASSWORD_UPPERCASE_CHARS => 4,
            self::PASSWORD_NUMBERS => 3,
            self::PASSWORD_SPECIAL_CHARS => 1,
            self::PASSWORD_LOWERCASE_CHARS => 4,
            self::PASSWORD_UPPERCASE_CHARS => 3,
        ];

        foreach ($requirements as $chars => $amount) {
            $charPositions = mb_strlen($chars) - 1;
            for ($i = 0; $i < $amount; ++$i) {
                $byte = (int) round(\ord(random_bytes(1)) / 255 * $charPositions);
                $password[] = $chars[$byte];
            }
        }

        shuffle($password);

        return implode('', $password);
    }

    public function encodePassword(User $user): void
    {
        if (empty($user->getClearPassword())) {
            return;
        }
        $user->setSalt(bin2hex(random_bytes(32)));
        $encodedPassword = $this->userPasswordHasher->hashPassword($user, $user->getClearPassword());
        $user->setEncodedPassword($encodedPassword);
    }

    public function generateToken(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $user->setToken($token);
    }

    public function resetToken(User $user): void
    {
        $user->setToken('');
    }

    public function generateSwitchToUserToken(User $user): string
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            throw new UnauthorizedHttpException('You have to be authenticated to switch to a user');
        }

        /** @var User $currentUser */
        $currentUser = $token->getUser();

        $payload = [self::JWT_PROPERTY_ORIGIN => $currentUser->getUsername()];
        $this->commentManager->insertComment($user, \sprintf('%s is impersonating %s', (string) $currentUser, (string) $user));

        $token->setUser($user);
        $this->tokenStorage->setToken($token);

        $payload[JWTEncoder::PAYLOAD_USER_KEY] = $user;

        return $this->jwtEncoder->encode($payload);
    }

    public static function getPortal(UserInterface $user): string
    {
        switch (true) {
            case $user instanceof ExtranetUser:
                return 'extranet';
            case $user instanceof People:
                return 'intranet';
            case $user instanceof VendorUser:
                return 'evendors';
        }
        throw new UnauthorizedHttpException('No portal has been found at authentication');
    }

    public function generateExitSwitching(array $payload): string
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            throw new UnauthorizedHttpException('You have to be authenticated to switch to a user');
        }
        if ([] === $payload) {
            throw new \RuntimeException('Unable to decode the original token');
        }
        if (empty($payload[self::JWT_PROPERTY_ORIGIN])) {
            throw new BadRequestHttpException('No previous user for switch exiting');
        }

        $payload[self::JWT_PROPERTY_USERNAME] = $payload[self::JWT_PROPERTY_ORIGIN];
        unset($payload[self::JWT_PROPERTY_ORIGIN]);

        /** @var User $impersonatedUser */
        $impersonatedUser = $token->getUser();
        $user = $this->registry->getRepository(People::class)->findByUsernameForLogin($payload[self::JWT_PROPERTY_USERNAME]);

        $this->commentManager->insertComment($impersonatedUser, \sprintf('%s stopped impersonating %s', (string) $user, (string) $impersonatedUser));

        $token->setUser($user);
        $this->tokenStorage->setToken($token);
        unset($payload['iat'], $payload['exp']);

        return $this->jwtEncoder->encode($payload);
    }

    public function logPassword(User $user)
    {
        $this->userRepository->logPassword($user);
    }

    public function getUserLoader(?string $portal): ?callable
    {
        switch ($portal) {
            case 'intranet':
            case self::LINK_PORTAL:
                return fn ($identifier) => $this->registry->getRepository(People::class)->findByUsernameForLogin($identifier);
            case 'extranet':
                return fn ($identifier) => $this->registry->getRepository(ExtranetUser::class)->findOneBy(['username' => $identifier]);
            case 'evendors':
                return fn ($identifier) => $this->registry->getRepository(VendorUser::class)->findByUsername($identifier);
            case 'external':
                return fn ($identifier) => $this->registry->getRepository(AuthorizedApplication::class)->findOneBy(['name' => $identifier]);
            default:
                return null;
        }
    }
}

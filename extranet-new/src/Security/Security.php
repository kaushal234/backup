<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecision;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

final class Security implements AuthorizationCheckerInterface
{
    public const LAST_USERNAME = SecurityRequestAttributes::LAST_USERNAME;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * Returns whether there's an authenticated user.
     */
    public function isFullyAuthenticated(): bool
    {
        return null !== $this->tokenStorage->getToken()?->getUser();
    }

    /**
     * Returns the current security token.
     */
    public function getToken(): ?TokenInterface
    {
        return $this->tokenStorage->getToken();
    }

    /**
     * Returns the current security token.
     */
    public function getAuthenticatedToken(): TokenInterface
    {
        $token = $this->getToken();
        if (null === $token) {
            $this->denyAccess();
        }

        return $token;
    }

    public function getAuthenticatedUser(): UserInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof UserInterface) {
            $this->denyAccess();
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(mixed $attribute, mixed $subject = null, ?AccessDecision $accessDecision = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @param non-empty-string $attribute
     * @param non-empty-string $message
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted(string $attribute, ?object $subject = null, string $message = 'Access Denied.'): void
    {
        if (!$this->isGranted($attribute, $subject)) {
            $this->denyAccess($message, [$attribute], $subject);
        }
    }

    /**
     * @param non-empty-string       $message
     * @param list<non-empty-string> $attributes
     *
     * @throws AccessDeniedException
     */
    public function denyAccess(string $message = 'Access Denied.', array $attributes = [], ?object $subject = null): never
    {
        $exception = new AccessDeniedException($message);
        $exception->setAttributes($attributes);
        $exception->setSubject($subject);

        throw $exception;
    }
}

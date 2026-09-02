<?php

declare(strict_types=1);

namespace App\Validator;

use ApiPlatform\Symfony\Validator\ValidationGroupsGeneratorInterface;
use App\Entity\Support\Manual;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manual uses this class to only authorize MOO of PUBS to edit a manual without an equipment record.
 */
final readonly class ManualGroupsGenerator implements ValidationGroupsGeneratorInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function __invoke($object): array
    {
        if ($this->authorizationChecker->isGranted('MANUAL_EDIT_VOTER', $object)) {
            return ['Default'];
        }

        return ['Default', Manual::USER_VALIDATION_GROUP];
    }
}

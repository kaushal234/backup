<?php

declare(strict_types=1);

namespace App\Validator;

use ApiPlatform\Symfony\Validator\ValidationGroupsGeneratorInterface;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FirstArticleQualificationGroupsGenerator implements ValidationGroupsGeneratorInterface
{
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param FirstArticleQualification $object
     *
     * @return string[]
     */
    public function __invoke($object): array
    {
        return $this->authorizationChecker->isGranted('FEATURE_FAQ_BUYER') ? ['Default', 'buyer'] : ['Default'];
    }
}

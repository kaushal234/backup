<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use App\Repository\Sales\ExtranetUserAclRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Contracts\Cache\CacheInterface;

class ExtranetUserRoleVoter extends RoleVoter
{
    private readonly CacheInterface $cache;
    private readonly ExtranetUserAclRepository $extranetUserAclRepository;

    public function __construct(CacheInterface $arrayCache, ExtranetUserAclRepository $extranetUserAclRepository)
    {
        parent::__construct();
        $this->cache = $arrayCache;
        $this->extranetUserAclRepository = $extranetUserAclRepository;
    }

    protected function extractRoles(TokenInterface $token): array
    {
        $user = $token->getUser();
        if (!$user instanceof ExtranetUser) {
            return [];
        }

        $extranetUserAclRepository = $this->extranetUserAclRepository;

        return $this->cache->get(\sprintf('ExtranetUser-%s', $user->getId()), static function () use ($user, $extranetUserAclRepository) {
            return array_map(
                static fn ($featureRow) => 'ROLE_'.$featureRow['role'],
                $extranetUserAclRepository->loadRolesByExtranetUser($user)
            );
        });
    }
}

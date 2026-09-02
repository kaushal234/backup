<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory\People;
use App\Repository\FeatureRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Contracts\Cache\CacheInterface;

class FeatureVoter extends RoleVoter
{
    private readonly CacheInterface $cache;
    private readonly FeatureRepository $featureRepository;

    public function __construct(CacheInterface $arrayCache, FeatureRepository $featureRepository)
    {
        parent::__construct('FEATURE_');
        $this->cache = $arrayCache;
        $this->featureRepository = $featureRepository;
    }

    protected function extractRoles(TokenInterface $token): array
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return [];
        }

        $featureRepository = $this->featureRepository;

        return $this->cache->get(\sprintf('People-%s', $user->getId()), static function () use ($user, $featureRepository) {
            $results = $featureRepository->loadFeaturesByPeople($user);

            $features = [];
            $memo = [];
            foreach ($results as $feature) {
                if (!\array_key_exists($feature['name'], $memo)) {
                    $features[] = $feature['name'];
                }
                if (null !== $feature['location_id']) {
                    $features[] = $feature['name'].'_'.$feature['location_id'];
                }
                $memo[$feature['name']] = true;
            }

            return $features;
        });
    }
}

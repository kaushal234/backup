<?php

declare(strict_types=1);

namespace ApiBundle\Security\Voter;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Security\Voter\AccessLightVoter;
use AppBundle\Security\Voter\AccessVoter;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ApiVoter implements VoterInterface
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes, ?Vote $vote = null): int
    {
        if ($token instanceof NullToken) {
            return self::ACCESS_ABSTAIN;
        }

        if (null !== $subject && (!\is_string($subject) || !(bool) Iri::type($subject))) {
            return self::ACCESS_ABSTAIN;
        }

        $filteredAttributes = [];
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Expression) {
                continue;
            }
            if (\is_string($attribute)) {
                if (preg_match('/^(ACL|ROLE|LOCATION)_/', $attribute)) {
                    continue;
                }

                if (null === $subject && 0 === mb_strpos($attribute, 'FEATURE_')) {
                    continue;
                }

                if (\in_array($attribute, [AccessLightVoter::ATTRIBUTE, AccessVoter::ATTRIBUTE], true)) {
                    continue;
                }
            }

            $filteredAttributes[] = $attribute;
        }

        if ([] === $filteredAttributes) {
            return self::ACCESS_ABSTAIN;
        }

        $query = ['attributes' => $filteredAttributes];
        if (null !== $subject) {
            $query['resource'] = $subject;
        }

        $vote = $this->client->get('/grants', ['query' => $query]);
        switch ($vote['grant']) {
            case 'GRANTED':
                return self::ACCESS_GRANTED;
            case 'DENIED':
                return self::ACCESS_DENIED;
        }

        throw new \UnexpectedValueException('Unexpected vote received from API');
    }
}

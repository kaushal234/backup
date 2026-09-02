<?php

declare(strict_types=1);

namespace App\Manager\Directory;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Directory\People;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamMemberManager
{
    public function __construct(
        private readonly Security $security,
        private readonly TeamMemberQueryManager $teamMemberQuery,
        private readonly TeamMemberFilterManager $teamMemberFilter,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function get(array $parameters)
    {
        $resolver = new OptionsResolver();
        $this->configureOption($resolver);
        $options = $resolver->resolve($parameters);
        $order = '';

        if ($options['order']) {
            $order = $this->teamMemberFilter->order($options['order']);
        }

        if ($options['supervisor']) {
            return $this->teamMemberQuery->getSupervisorHierarchy($options['user']);
        }

        if ($options['supervisor_position']) {
            if ($options['position']) {
                $supervisor = $this->teamMemberQuery->getSupervisorHierarchy($options['user'], $this->teamMemberFilter->positionFilter($options['supervisor_position']));

                if ([] === $supervisor) {
                    /** @var People $user */
                    $user = $this->security->getUser();

                    return [[
                        'id' => $user->getId(),
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                        'supervisor_id' => $user->getSupervisor()->getId(),
                        'disabled' => $user->isDisabled(),
                        'level' => 1,
                        'position_code' => $user->getPosition()->getCode(),
                    ]];
                }

                return $this->teamMemberQuery->getSubordinateHierarchy($supervisor[0]['id'], $this->teamMemberFilter->positionFilter($options['position']), $order);
            }

            return $this->teamMemberQuery->getSupervisorHierarchy($options['user'], $this->teamMemberFilter->positionFilter($options['supervisor_position']));
        }

        return $this->teamMemberQuery->getSubordinateHierarchy($options['user'], $this->teamMemberFilter->positionFilter($options['position']), $order);
    }

    public function transformToIris(array $peoples): array
    {
        foreach ($peoples as $key => $people) {
            $peoples[$key]['@id'] = $this->iriConverter->getIriFromResource(People::class, UrlGeneratorInterface::ABS_PATH, new Get(), ['uri_variables' => ['id' => $people['id']]]);
        }

        return $peoples;
    }

    public function configureOption(OptionsResolver $resolver)
    {
        /** @var People $user */
        $user = $this->security->getUser();

        $resolver->setDefaults([
            'user' => $user->getId(),
            'position' => [],
            'supervisor' => false,
            'supervisor_position' => [],
            'order' => null,
        ]);

        $resolver->setAllowedTypes('position', 'string[]');
        $resolver->setAllowedTypes('supervisor_position', 'string[]');

        $resolver->setNormalizer('supervisor', static function (Options $options, string $value): bool {
            return (bool) $value;
        });

        $resolver->setNormalizer('user', static function (Options $options, string $value): int {
            return (int) $value;
        });

        $resolver->setNormalizer('position', static function (Options $options, array $value) {
            return array_map(static function ($position) {return '\''.$position.'\''; }, $value);
        });

        $resolver->setNormalizer('supervisor_position', static function (Options $options, array $value) {
            return array_map(static function ($position) {return '\''.$position.'\''; }, $value);
        });
    }
}

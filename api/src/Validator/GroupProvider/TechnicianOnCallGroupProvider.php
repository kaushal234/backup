<?php

declare(strict_types=1);

namespace App\Validator\GroupProvider;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupProviderInterface;

class TechnicianOnCallGroupProvider implements GroupProviderInterface
{
    public function __construct(
        private readonly ?RequestStack $requestStack,
    ) {
    }

    public function getGroups(object $object): array|GroupSequence
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || 'technician_on_call_duplicate' !== $request->get('_route')) {
            return ['TechnicianOnCall', 'contact_require', 'contacts'];
        }

        return ['TechnicianOnCall', 'contacts'];
    }
}

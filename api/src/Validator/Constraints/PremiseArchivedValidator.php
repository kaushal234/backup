<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Directory\Premise;
use App\Repository\Directory\PeopleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PremiseArchivedValidator extends ConstraintValidator
{
    protected RequestStack $requestStack;
    protected PeopleRepository $peopleRepository;

    public function __construct(PeopleRepository $peopleRepository, RequestStack $requestStack)
    {
        $this->peopleRepository = $peopleRepository;
        $this->requestStack = $requestStack;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PremiseArchived || !$value instanceof Premise) {
            return;
        }

        if (!$value->archived) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $previous = $request->attributes->get('previous_data');
        if (!$previous instanceof Premise) {
            return;
        }

        if ($previous->archived) {
            return;
        }

        $count = $this->peopleRepository->countPremiseUsers($value);

        if ($count > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }
}

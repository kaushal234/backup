<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Department;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DepartmentController extends AbstractController
{
    private readonly PeopleRepository $peopleRepository;

    private readonly IriConverterInterface $iriConverter;

    public function __construct(PeopleRepository $peopleRepository, IriConverterInterface $iriConverter)
    {
        $this->peopleRepository = $peopleRepository;
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(Department $department, Request $request)
    {
        if (empty($request->getContent()) || 0 === $this->peopleRepository->count(['department' => $department])) {
            return $department;
        }

        $content = json_decode((string) $request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (\is_array($content) && \array_key_exists('replacement', $content)) {
            $replacement = $this->iriConverter->getResourceFromIri($content['replacement']);
        } else {
            throw new UnprocessableEntityHttpException('A property "replacement" was expected in the body of the request');
        }

        if (!$replacement instanceof Department) {
            throw new UnprocessableEntityHttpException(\sprintf('An instance of "Department" was expected. Got a "%s"', $department::class));
        }

        $this->peopleRepository->replacePeopleDepartment($department, $replacement);

        return $department;
    }
}

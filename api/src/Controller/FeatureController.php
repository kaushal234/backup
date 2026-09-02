<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Filter\Feature\FeatureParamsFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FeatureController extends AbstractController
{
    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(Request $request)
    {
        if (!$request->query->has(FeatureParamsFilter::FILTER_ATTRIBUTE_PROPERTY)) {
            throw new BadRequestHttpException('Missing filter attributes.');
        }

        $object = null;
        if ($request->query->has(FeatureParamsFilter::FILTER_RESOURCE_PROPERTY)) {
            $object = $this->iriConverter->getResourceFromIri($request->query->get('resource'));
        }

        $attributes = (array) ($request->query->all()[FeatureParamsFilter::FILTER_ATTRIBUTE_PROPERTY] ?? []);
        if (\count($attributes) > 1) {
            throw new BadRequestHttpException('Passing several attribute is not permitted, you should make several api calls instead.');
        }

        $grant = $this->isGranted($attributes[0], $object);

        return new JsonResponse(['grant' => $grant ? 'GRANTED' : 'DENIED']);
    }
}

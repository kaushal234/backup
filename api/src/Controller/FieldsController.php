<?php

declare(strict_types=1);

namespace App\Controller;

use App\Serializer\DenormalizedPropertiesExtractor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FieldsController extends AbstractController
{
    private readonly DenormalizedPropertiesExtractor $denormalizedPropertiesExtractor;

    public function __construct(DenormalizedPropertiesExtractor $denormalizedPropertiesExtractor)
    {
        $this->denormalizedPropertiesExtractor = $denormalizedPropertiesExtractor;
    }

    #[Route(path: '/fields', methods: ['GET'])]
    public function fields(Request $request)
    {
        $iri = $request->query->get('iri', $request->query->get('resource'));
        $method = $request->query->get('method');

        if (null === $iri || null === $method) {
            throw new BadRequestHttpException('iri and method parameters are mandatory.');
        }

        try {
            $denormalizedProperties = $this->denormalizedPropertiesExtractor->extract($iri, $method);
        } catch (\Exception $exception) {
            $denormalizedProperties = [];
        }

        return new JsonResponse($denormalizedProperties);
    }
}

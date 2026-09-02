<?php

declare(strict_types=1);

namespace AppBundle\Controller\Manufacturing;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/manufacturing/engineering', defaults: ['alvest_module' => 'ER'])]
class DrawingController
{
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    private readonly Client $client;

    public function __construct(Client $client, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
        $this->client = $client;
    }

    #[Route(path: '/drawing/{site}/{item}/{date}', name: 'manufacturing_engineering_drawing', methods: ['GET'])]
    public function download(int $site, string $item, \DateTime $date)
    {
        $date->setTime(23, 59, 59);
        $date = $date->format(\DateTimeInterface::ATOM);

        return $this->fileStreamedResponseFactory->create(
            \sprintf('ion/bill-of-materials/drawings/site=%d;project=;product=%s', $site, $item),
            ['query' => ['date' => $date]]
        );
    }

    #[Route(path: '/zip/{site}/{item}/{date}', name: 'manufacturing_engineering_zip', methods: ['GET'])]
    public function zipAllDrawings(int $site, string $item, \DateTime $date, Request $request): StreamedResponse
    {
        $formatsQueryParameter = '';
        foreach ($request->query->all('formats') as $format) {
            $formatsQueryParameter .= \sprintf('&formats[]=%s', $format);
        }

        $response = new StreamedResponse(function () use ($site, $item, $date, $request, $formatsQueryParameter) {
            $zip = $this->client->request(
                \sprintf(
                    'ion/bill_of_material_item_zip/site=%d;project=;product=%s?flat=%d%s',
                    $site,
                    $item,
                    $request->query->get('flat'),
                    $formatsQueryParameter
                ),
                null,
                null, Request::METHOD_GET, [
                    'headers' => [
                        'Content-Type' => 'application/zip',
                        'Accept' => 'application/zip',
                    ],
                    'query' => ['date' => $date->format(\DateTimeInterface::ATOM), 'depth' => 20],
                ]
            );

            echo $zip->getContent();
        });

        $response->headers->set('Content-Disposition', \sprintf('inline; filename=%d.zip', $item));
        $response->headers->set('Content-type', 'application/zip');

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\DataPersister\Service;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallPersister
{
    public const RESOURCE_URL = 'service/technician_on_calls';

    public function __construct(
        private readonly Client $client,
        private readonly ViolationMapper $violationMapper,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function save(FormInterface $form, ?array $payload = null): bool|array|ApiData
    {
        $payload = $payload ?? $form->getData();

        try {
            return $this->client->save(self::RESOURCE_URL, $payload);
        } catch (ClientException $exception) {
            if (Response::HTTP_FORBIDDEN === $exception->getCode()) {
                /** @var Session $session */
                $session = $this->requestStack->getSession();

                $session->getFlashBag()->add('error', $this->translator->trans('security.warning.access_denied', [], 'messages'));

                return false;
            }

            $this->violationMapper->mapToForm($exception, $form);

            return false;
        }
    }

    public function put(FormInterface $form, ?array $payload = null, ?string $url = self::RESOURCE_URL): bool|array|ApiData
    {
        $payload = $payload ?? $form->getData();

        try {
            return $this->client->put($url, ['json' => $payload]);
        } catch (ClientException $exception) {
            if (Response::HTTP_FORBIDDEN === $exception->getCode()) {
                /** @var Session $session */
                $session = $this->requestStack->getSession();

                $session->getFlashBag()->add('error', $this->translator->trans('security.warning.access_denied', [], 'messages'));

                return false;
            }

            $this->violationMapper->mapToForm($exception, $form);

            return false;
        }
    }

    public function changeStatus(FormInterface $form): bool|array|ApiData
    {
        $technicianOnCall = $form->getData()->toArray();
        $payload = [
            '@id' => $technicianOnCall['@id'],
            'status' => mb_strtoupper($form->getClickedButton()->getName()),
        ];

        if ($form->has('symptoms') || $form->has('rootCause') || $form->has('solution')) {
            $payload['symptoms'] = $form->get('symptoms')->getData();
            $payload['rootCause'] = $form->get('rootCause')->getData();
            $payload['solution'] = $form->get('solution')->getData();
        }

        return $this->put($form, $payload, \sprintf('%s/%d/status', self::RESOURCE_URL, $technicianOnCall['id']));
    }

    public function requestTechnician(FormInterface $form): bool|array|ApiData
    {
        $data = $form->getData();
        $payload = [
            'nestedCustomerServiceRecord' => $form->get('nestedTechnicianOnCallCustomerServiceRecord')->getData(),
        ];

        return $this->put($form, $payload, \sprintf('%s/%d/request-technician', self::RESOURCE_URL, $data['id']));
    }
}

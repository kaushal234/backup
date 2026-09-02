<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ViolationMapper
{
    public function __construct(
        private readonly PropertyAccessorInterface $accessor,
    ) {
    }

    /**
     * Try to read a client exception to map the errors to a form.
     *
     * @param array<string, string> $propertyMapping
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function mapToForm(ClientException $e, FormInterface $form, array $propertyMapping = []): void
    {
        $body = $e->getResponse()->toArray(false);
        if (empty($body)) {
            // The error is not in the Response, it can be anything (shouldn't be displayed to the final user)
            throw $e;
        }

        if (!isset($body['violations'])) {
            $messages = [];
            if (isset($body['hydra:title'])) {
                $messages[] = $body['hydra:title'];
            }

            if (isset($body['hydra:description'])) {
                $messages[] = $body['hydra:description'];
            }

            if ($messages) {
                $form->addError(new FormError(implode(': ', $messages)));
            }

            return;
        }

        foreach ($body['violations'] as $violation) {
            $propertyPath = $propertyMapping[$violation['propertyPath']] ?? $violation['propertyPath'];
            $target = '['.implode('][', preg_split('#\.|\[#', str_replace(']', '', $propertyPath))).']';

            if ($this->accessor->isReadable($form, $target) && null !== $target = $this->accessor->getValue($form, $target)) {
                // Map the error to the right form element
                $target->addError(new FormError($violation['message']));
            } else {
                // Map the error as global
                $form->addError(new FormError($propertyPath.': '.$violation['message']));
            }
        }
    }
}

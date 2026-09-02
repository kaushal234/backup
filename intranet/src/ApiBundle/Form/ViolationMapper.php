<?php

declare(strict_types=1);

namespace ApiBundle\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ViolationMapper
{
    /** @var PropertyAccessorInterface */
    protected $accessor;

    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * Try to read a client exception to map the errors to a form.
     */
    public function mapToForm(ClientException $e, FormInterface $form, array $propertyMapping = [])
    {
        $body = json_decode($e->getResponse()->getContent(false), true);
        if (null === $body) {
            // The error is not in JSON, it can be anything (shouldn't be displayed to the final user)
            throw $e;
        }

        $this->contentMapToForm($body, $form, $propertyMapping);
    }

    public function contentMapToForm(array $body, FormInterface $form, array $propertyMapping = [])
    {
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
            $target = '['.implode('][', preg_split('#\.|\[#', str_replace(']', '', (string) $propertyPath))).']';
            if ($this->accessor->isReadable($form, $target) && null !== $target = $this->accessor->getValue($form, $target)) {
                // Map the error to the right form element
                $target->addError(new FormError($violation['message']));
            } else {
                // Map the error as global
                $form->addError(new FormError($propertyPath.': '.$violation['message']));
            }
        }
    }

    /**
     * Maps an API error payload onto a specific item of a collection form.
     *
     * Intended for controllers that persist a collection item by item and need to
     * attach each item's API validation errors back onto the right sub-form.
     *
     * The API returns flat property paths with no collection index.
     * The caller is responsible for knowing which item a payload
     * belongs to and passing its index; this method then routes the violations onto
     * that item, where the flat path matches a direct child field.
     *
     * If no item exists at $index, the payload is mapped as a global error on the root.
     *
     * @param array         $body            decoded API error body (expects a "violations" key)
     * @param FormInterface $collectionForm  the collection form
     * @param int           $index           index of the item the errors belong to
     * @param array         $propertyMapping optional API-path => form-path overrides
     */
    public function contentMapToFormCollection(array $body, FormInterface $collectionForm, int $index, array $propertyMapping = []): void
    {
        if (!$collectionForm->has((string) $index)) {
            $this->contentMapToForm($body, $collectionForm->getRoot(), $propertyMapping);

            return;
        }

        $this->contentMapToForm($body, $collectionForm->get((string) $index), $propertyMapping);
    }

    public function getViolations(ClientException $e)
    {
        $body = json_decode($e->getResponse()->getContent(false), true);
        $violations = [];
        if (isset($body['violations'])) {
            foreach ($body['violations'] as $violation) {
                $violations[$violation['propertyPath']] = $violation['message'];
            }
        }

        return $violations;
    }
}

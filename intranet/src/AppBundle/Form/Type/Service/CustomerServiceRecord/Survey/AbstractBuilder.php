<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord\Survey;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;

abstract class AbstractBuilder implements BuilderInterface
{
    final public function addForm(FormInterface $form, array $data): void
    {
        $form
            ->add('answer', $this->getType(), $this->getOptions($data))
        ;

        if ($options = $this->commentOptions($data)) {
            $form
                ->add('comment', TextareaType::class, [
                    'label' => 'csr.questions.'.$data['questionSurveyCustomerServiceRecord']['name'].'_comment',
                    'label_attr' => ['style' => 'text-align:start'],
                ] + (\is_array($options) ? $options : []))
            ;
        }
    }

    public function transform(array $value): array
    {
        return $value;
    }

    public function reverse(array $value): array
    {
        $value['answer'] = (string) $value['answer'];

        return $value;
    }

    abstract protected function getType(): string;

    abstract protected function getOptions(array $data): array;

    abstract protected function commentOptions(array $data): bool|array;
}

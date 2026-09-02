<?php

declare(strict_types=1);

namespace App\Twig\TechnicianOnCall;

use App\DataTransferObject\TechnicianOnCall\AddTechnicianOnCallComment;
use App\Form\Type\TechnicianOnCall\CommentType;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormExtension extends AbstractExtension
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('create_toc_comment_add_form', $this->createCommentForm(...)),
        ];
    }

    public function createCommentForm(TechnicianOnCall $technicianOnCall): FormView
    {
        $url = $this->urlGenerator->generate('technician_on_calls:comment', ['id' => $technicianOnCall->id]);

        $builder = $this->factory->createBuilder(CommentType::class, new AddTechnicianOnCallComment());
        $builder->setAction($url);

        return $builder->getForm()->createView();
    }
}

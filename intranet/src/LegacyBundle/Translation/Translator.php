<?php

declare(strict_types=1);

namespace LegacyBundle\Translation;

use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Translator implements TranslatorInterface, LocaleAwareInterface
{
    /** @var BaseTranslator */
    private readonly TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return htmlentities($this->translator->trans($id, $parameters, $domain, $locale), \ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IntlExtension extends AbstractExtension
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('country', [$this, 'country'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Returns the localized country name from the provided code.
     */
    public function country(?string $code = null, $locale = null): string
    {
        try {
            $name = Countries::getName($code ?? '', $locale ?: $this->translator->getLocale());
        } catch (MissingResourceException $e) {
            return '';
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'app_intl';
    }
}

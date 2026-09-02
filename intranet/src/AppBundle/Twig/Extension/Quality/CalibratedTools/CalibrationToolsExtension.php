<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension\Quality\CalibratedTools;

use ApiBundle\Iri\Iri;
use AppBundle\Manager\Quality\CalibratedTools\Statuses\OutOfToleranceFormStatus;
use AppBundle\Manager\Quality\CalibratedTools\Statuses\ToolStatus;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CalibrationToolsExtension extends AbstractExtension
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('link_url', [$this, 'getLinkUrl']),
            new TwigFilter('link_content', [$this, 'getLinkContent']),
            new TwigFilter('status_label', [$this, 'getStatusLabel']),
            new TwigFilter('expires_in', [$this, 'expiresIn']),
            new TwigFilter('expires_in_days', [$this, 'expiresInDays']),
        ];
    }

    public function unescape($value): string
    {
        return html_entity_decode((string) $value);
    }

    public function getLinkUrl($reportItem, $item)
    {
        if (empty($reportItem['link'])) {
            return '';
        }
        $urlDefinition = $reportItem['link'];
        if (!\array_key_exists('url', $urlDefinition) || empty($urlDefinition['url'])) {
            return '';
        }
        $url = $urlDefinition['url'];
        if (!\array_key_exists('params', $urlDefinition) || !\is_array($urlDefinition['params'])) {
            return $url;
        }

        $paramKey = key($urlDefinition['params']);
        $paramValue = current($urlDefinition['params']);

        $replaceValue = null;

        $useIri = '@id' === $paramValue;

        if ($useIri) {
            $replaceValue = Iri::id($item[$paramKey]);
        } elseif (isset($item[$paramValue])) {
            $replaceValue = $item[$paramValue];
        } else {
            $replaceValue = $item[$paramKey][$paramValue];
        }

        return str_replace('/0', '/'.$replaceValue, (string) $url);
    }

    public function getStatusLabel($value): string
    {
        $status = mb_strtoupper((string) $value);
        $label = 'label-default';
        switch ($status) {
            case OutOfToleranceFormStatus::IN_PROGRESS:
            case ToolStatus::ACTIVE:
                $label = 'label-primary';
                break;
            case ToolStatus::CALIBRATION_DUE_SOON:
                $label = 'label-warning';
                break;
            case OutOfToleranceFormStatus::CLOSED:
            case ToolStatus::UNDER_CALIBRATION:
                $label = 'label-info';
                break;
            case ToolStatus::EXPIRED:
                $label = 'label-danger';
                break;
            case ToolStatus::OUT_OF_SERVICE:
                $label = 'label-primary';
                break;
            case ToolStatus::SCRAPPED:
                $label = 'label-default';
                break;
        }

        return \sprintf('label %s', $label);
    }

    public function getLinkContent($value, $linkDefinition)
    {
        if (!\array_key_exists('content', $linkDefinition) || empty($linkDefinition['content'])) {
            return '';
        }

        return $value[$linkDefinition['content']];
    }

    public function expiresIn($value)
    {
        $date = new \DateTime();
        $date->add(\DateInterval::createFromDateString(\sprintf('%s day', $value ?? 0)));
        $currentDate = new \DateTime();

        $expiresIn = $currentDate->diff($date);

        if (1 === $expiresIn->invert) {
            return 'expired';
        }

        $expiresFormat = '';

        if ($expiresIn->y > 0) {
            $expiresFormat .= $this->translator->trans('calibration_tool.expiresIn.years', ['%nb%' => $expiresIn->y, '%count$' => $expiresIn->y], 'calibration_tool');
        }

        if ($expiresIn->m > 0) {
            $expiresFormat .= ' '.$this->translator->trans('calibration_tool.expiresIn.months', ['%nb%' => $expiresIn->m, '%count$' => $expiresIn->m], 'calibration_tool');
        }

        if ($expiresIn->d > 0) {
            $expiresFormat .= ' '.$this->translator->trans('calibration_tool.expiresIn.days', ['%nb%' => $expiresIn->d, '%count$' => $expiresIn->y], 'calibration_tool');
        }

        return $expiresFormat;
    }

    public function expiresInDays($value)
    {
        if (null === $value) {
            return $value;
        }

        return $this->translator->trans('calibration_tool.expiresIn.days_only', ['%nb%' => $value], 'calibration_tool');
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'calibration_tools';
    }
}

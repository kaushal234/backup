<?php

declare(strict_types=1);

namespace LegacyBundle\Factory\Sequence\HumanResources;

use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use LegacyBundle\Factory\Sequence\UserSequenceTrait;
use LegacyBundle\Model\Sequence;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AnnualRevisionSequenceFactory
{
    use UserSequenceTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly RouterInterface $router,
    ) {
    }

    public function createAnnualRevisionSequence(People $people, string $template, People $assignee): Sequence
    {
        $sequence = $this->constructSequence($template, $people, $assignee, 60);
        $locale = 'zh' === $people->getLocale() ? 'zh-CN' : ($people->getLocale() ?? 'en');

        $missingPropertyTranslation = $this->translator->trans('sequence.annual_revision.missing_property', [], 'emails', $locale);
        $url = $this->router->generate('account_acl', ['id' => $people->getId()]);

        $propertyDescription = '';
        foreach (['photo', 'alternateEmail'] as $property) {
            $propertyLine = $this->translator->trans(\sprintf('sequence.annual_revision.fields.%s', $property), [], 'emails', $locale);
            if (null === $this->propertyAccessor->getValue($people, $property)) {
                $propertyLine = \sprintf('%s (<span style="color: red">%s</span>)', $propertyLine, $missingPropertyTranslation);
            }

            $propertyDescription = \sprintf('%s
            %s', $propertyDescription, $propertyLine);
        }

        $phones = [];
        if ($people->getPhones()->count() > 0) {
            foreach ($people->getPhones() as $phone) {
                if (!\in_array($phone->getType(), [Phone::TYPE_MOBILE, Phone::TYPE_PHONE], true)) {
                    continue;
                }

                $phones[] = $phone->getType();
            }
        }

        $phonesLines = '';
        foreach ([Phone::TYPE_PHONE, Phone::TYPE_MOBILE] as $phoneType) {
            $phonesLines = \sprintf('%s
            %s', $phonesLines, $this->translator->trans(\sprintf('sequence.annual_revision.fields.%s', $phoneType), [], 'emails', $locale));
            if (\in_array($phoneType, $phones, true)) {
                $phonesLines = \sprintf('%s (<span style="color: red">%s</span>)
                ', $phonesLines, $missingPropertyTranslation);
            }
        }

        $businessUnitPremiseLine = $this->translator->trans('sequence.annual_revision.fields.businessUnit', [], 'emails', $locale);
        if (null === $people->getPremise()) {
            $businessUnitPremiseLine = \sprintf('%s (<span style="color: red">%s</span>)', $businessUnitPremiseLine, $missingPropertyTranslation);
        }

        $propertyDescription = \sprintf('%s
        %s
        %s', $propertyDescription, $phonesLines, $businessUnitPremiseLine);

        $description = \sprintf('%s
        %s
        %s
        %s', $this->translator->trans('sequence.annual_revision.header', ['%fullname%' => $people->getDisplayName()], 'emails', $locale),
            $propertyDescription,
            $this->translator->trans('sequence.annual_revision.second_point', ['%url%' => $url], 'emails', $locale),
            $this->translator->trans('sequence.annual_revision.footer', [], 'emails', $locale)
        );

        $sequence->setDescription($description);

        return $sequence;
    }
}

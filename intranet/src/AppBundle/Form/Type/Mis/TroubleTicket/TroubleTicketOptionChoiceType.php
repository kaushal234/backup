<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Option (mis/types) dropdown for the TTS add form.
 *
 * The visible label is ONLY the translated description (no "Incident - " prefix).
 * Each <option> carries data-* attributes so the page can, with no React:
 *   - data-category : Incident / Request   -> filter the list by the "Type" select
 *   - data-indice   : IF1 / IF 10 ...       -> available for severity-based logic
 *   - data-notify   : "1" on the option that must show the "who will be notified" modal
 *   - data-purchase-warning : "1" on the IT-purchase-request option
 *
 * NOTE: the two special options below mirror the React form, which keys off the
 * numeric type id (11 = "Major impact on business activity", 10 = purchase request).
 * Adjust SPECIAL_NOTIFY_ID / SPECIAL_PURCHASE_ID if those ids ever change.
 */
class TroubleTicketOptionChoiceType extends AbstractType
{
    private const SPECIAL_NOTIFY_ID = 11;
    private const SPECIAL_PURCHASE_ID = 10;

    public function __construct(
        private readonly DataProvider $dataProvider,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $meta = [];

        $resolver->setDefaults([
            'key' => '@id',
            'choice_translation_domain' => false,
            'choices' => function (Options $options) use (&$meta) {
                $collection = $this->dataProvider->findAll('mis/types', [], ['displayedOrder' => 'ASC']);

                $choices = [];
                foreach ($collection as $item) {
                    $label = $this->translator->trans(
                        'trouble_ticket.form.option_value.'.$item['description'],
                        [],
                        'trouble_ticket',
                    );
                    $value = $item[$options['key']];
                    $id = (int) mb_substr((string) mb_strrchr((string) $item['@id'], '/'), 1);

                    $choices[$label] = $value;
                    $meta[$value] = [
                        'category' => $item['type'],         // Incident / Request
                        'indice' => $item['indiceFactor'],   // IF1 / IF 10 / IF 100 / IF 1000
                        'notify' => self::SPECIAL_NOTIFY_ID === $id,
                        'purchase' => self::SPECIAL_PURCHASE_ID === $id,
                    ];
                }

                return ['' => ''] + $choices;
            },
            'choice_attr' => static function (Options $options) use (&$meta) {
                return static function ($choiceValue) use (&$meta) {
                    if (!isset($meta[$choiceValue])) {
                        return [];
                    }

                    $attr = [
                        'data-category' => $meta[$choiceValue]['category'],
                        'data-indice' => $meta[$choiceValue]['indice'],
                    ];

                    if ($meta[$choiceValue]['notify']) {
                        $attr['data-notify'] = '1';
                    }
                    if ($meta[$choiceValue]['purchase']) {
                        $attr['data-purchase-warning'] = '1';
                    }

                    return $attr;
                };
            },
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

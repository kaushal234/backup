<?php

declare(strict_types=1);
namespace Legacy\QuickForm\Manufacturing\Engineering;

class BenchmarkSelectorQuickForm
{
    public static function getForm(int $erp, string $pn, string $date): \HTML_QuickForm
    {
        $form = new \HTML_QuickForm('frmByNum', 'get');
        $form->addElement('hidden', 'm[0]', 'bom');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'pur');
        $form->addElement('hidden', 'erp', $erp);
        $form->addElement('hidden', 'pn', $pn);
        $form->addElement('hidden', 'date', $date);

        $factoryList = \tldLocation::getFactoryList("smartyOptions");

        $form->addElement('header', 'title', 'Select factories');
        $form->addElement('select', 'main_erp', 'Main factory', ["" => "", 540 => "TLD EUR"] + $factoryList);

        unset($factoryList[$erp]);
        $erps =& $form->addElement('advmultiselect', 'other_erps', null,
            $factoryList,
            ['size' => 6, 'class' => 'pool', 'style' => 'width:380px;']
        );
        $erps->setLabel(['Models Affected', 'Type->Model', 'Affected']);
        $erps->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $erps->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);$form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('main_erp', 'Main ERP is required', 'required');
        $form->setDefaults(['main_erp' => $erp, 'other_erps' => []]);

        return $form;
    }
}
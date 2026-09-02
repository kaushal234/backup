<?php

namespace Legacy\Controller\Manufacturing\Engineering;

use Legacy\QuickForm\Manufacturing\Engineering\BenchmarkSelectorQuickForm;
use Shared\Factory\Manufacturing\JobShop\JobShopBillOfMaterialBenchmarkTableFactory;
use Shared\Formatter\Manufacturing\JobShop\JobShopBillOfMaterialBenchmarkXlsFormatter;
use Shared\Provider\Manufacturing\JobShop\JobShopBillOfMaterialBenchmarkProvider;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;

class BillOfMaterialController
{
    public function __construct(
        public \Smarty $smarty
    ) {
    }

    public function purchasingBenchmark(int $erp, string $pn, \DateTime $date): ?string
    {
        $request = Request::createFromGlobals();
        $form = BenchmarkSelectorQuickForm::getForm($request->query->all()['erp'], $request->query->all()['pn'], $request->query->all()['date']);
        $body = $form->toHTML();

        if (!$form->isSubmitted() || !$form->validate()) {
            return $body;
        }

        $vars = \tldUtils::cleanupFormInput($form->exportValues());

        $otherERPs = $vars['other_erps'] ?? [];
        sort($otherERPs);

        try {
            $jobShopBillOfMaterialBenchmark = new JobShopBillOfMaterialBenchmarkProvider();
            $jsbom = $jobShopBillOfMaterialBenchmark->getItem($erp, $pn, $date, $otherERPs);
            $tableFactory = new JobShopBillOfMaterialBenchmarkTableFactory();
            $table = $tableFactory->createFromJobShopBillOfMaterialBenchmark($jsbom, $erp);
        } catch (ClientException $exception) {
            global $DEFAULT_ERROR;
            $DEFAULT_ERROR[] = "ERROR: BOM not found.";
            return null;
        } catch (\RuntimeException $runtimeException) {
            global $DEFAULT_ERROR;
            $DEFAULT_ERROR[] = $runtimeException->getMessage();
            return null;
        }

        $mParameter = $request->query->all('m');
        if (array_key_exists(3, $mParameter) && 'xls' === $mParameter[3]) {
            $jsbomFormatter = new JobShopBillOfMaterialBenchmarkXlsFormatter();
            $report = $jsbomFormatter->excel($table, $erp, \tldLocation::getNameFromErp($erp, ...$otherERPs));
            $report->out();
            exit;
        }
        $this->smarty->assign("width", "1028");
        $this->smarty->assign("product", $jsbom->product);
        $this->smarty->assign("site", $jsbom->site);
        $this->smarty->assign("bom", $table);
        $this->smarty->assign("mainErp", $erp);
        $this->smarty->assign("otherERPs", $otherERPs);
        $this->smarty->assign("erpNames", \tldLocation::getNameFromErp($erp, ...$otherERPs));
        $this->smarty->assign("date", $date->format('Y-m-d'));

        return $body .= $this->smarty->fetch(__DIR__.'/../../../../templates/Manufacturing/Engineering/view.bom.benchmark.tpl');
    }
}
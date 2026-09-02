<?php
require_once('HTML/QuickForm/advmultiselect.php');
include_once("graphs.common.inc.php");
include_once("sales_service.inc.php");

const CRAB_CSC = 'PDI-CSC';
const CRAB_SOL = 'PDI-SOL';

$colors = [
    'TLD MTL' => 'red',
    'TLD SHA' => 'orange',
    'TLD SHE' => 'green',
    'TLD STL' => 'blue',
    'TLD WIN' => 'purple',
    'TLD WUX' => 'pink',
];

$PATH .= "/kpi_pdi_crab";
$DEFAULT_TITLE .= "\\PDI CRAB KPI";
$DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="$php_self?m[0]=kpi_pdi_crab">Home</a>
EOF;

$ssoList = array_filter(
    tldLocation::getSalesOrgList("smartyOptionsIDLocation"),
    function ($name) {
        return !in_array($name, ['TLD DTV', 'TLD AME SCM'], true);
    }
);

$form = new HTML_QuickForm('pdiCrabsBySSO', 'post');
$form->addElement('hidden', 'm[0]', 'kpi_pdi_crab');
$form->addElement('header', 'title', "PDI CRAB KPI by");
$form->addElement('select', 'sso', 'SSO', ["ALL" => "ALL SSO"] + $ssoList, ["onChange" => "javascript:this.form.submit();"]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$form->setDefaults(['sso' => 'ALL']);

$body = $form->toHTML();

if ($form->validate() || !$form->isSubmitted()) {
    $vars = tldUtils::cleanupFormInput($form->exportValues());

    $where = $vars['sso'] === 'ALL' ? "" : "AND loc_sso.id = " . $vars['sso'];

    $query = <<<SQL
    SELECT
      COUNT(*) AS nb_crabs,
      locations.location            AS buid_fullname,
      crabs.opno                    AS code_fullname,
      DATE_FORMAT(crabs.dt,'%Y-%m') AS crab_date
    FROM crabs
      LEFT JOIN locations ON locations.id = crabs.buid
      LEFT JOIN service ON service.id = crabs.erid
      LEFT JOIN sor_units ON sor_units.id = service.sor_uid
      LEFT JOIN sor_lines ON sor_units.parent_id = sor_lines.id
      LEFT JOIN sor ON sor_lines.parent_id = sor.id
      LEFT JOIN locations as loc_sso ON sor.sso = loc_sso.id
    WHERE crabs.opno IN ('PDI-CSC', 'PDI-SOL')
    AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(crabs.dt,'%Y%m')) BETWEEN 0 AND 11
    $where
      GROUP BY DATE_FORMAT(crabs.dt,'%Y-%m'), locations.location, crabs.opno
      ORDER BY crabs.dt ASC
SQL;

    $rows = tldUtils::getSqlToAssocArray($query);

    /**
     * Get last 12 months
     */
    $start = (new DateTime('1 year ago'))->modify('+1 month');
    $end = (new DateTime())->modify('+1 month');
    $interval = new DateInterval('P1M');
    $period = new DatePeriod($start, $interval, $end);
    $months = [];

    foreach ($period as $dt) {
        $months[$dt->format('Y-m')] = 0;
    }

    $factories = array_filter(
        tldLocation::byConstraints(['disable' => 0, 'factory' => 'Y', 'hidden' => 0]),
        function ($factory) {
            return $factory['location'] !== 'TLD DTV';
        }
    );

    $createSerie = function ($location, $crabType, $show = true) use ($months, $colors) {
        $serie = [
            'name' => $location,
            'data' => $months,
            'stack' => $crabType,
            'color' => $colors[$location],
        ];

        if (false === $show) {
            $serie['showInLegend'] = false;
        }

        return $serie;
    };

    $crabs = array_reduce($factories, function ($memo, $factory) use ($createSerie) {
        $memo[$factory['location']] = [
            CRAB_CSC => $createSerie($factory['location'], CRAB_CSC),
            CRAB_SOL => $createSerie($factory['location'], CRAB_SOL, false),
        ];
        return $memo;
    }, []);

    /**
     * Process results from db and populate the data array
     */
    foreach ($rows as $row) {
        if (!array_key_exists($row['buid_fullname'], $crabs)) {
            continue;
        }
        $crabDate = (new DateTime($row['crab_date']))->format('Y-m');
        if (!array_key_exists($crabDate, $months)) {
            continue;
        }
        $crabs[$row['buid_fullname']][$row['code_fullname']]['data'][$crabDate] = (int)$row['nb_crabs'];
    }

    /**
     * Convert to JSON
     */
    $json = [];
    foreach ($crabs as &$crab) {
        $crab[CRAB_CSC]['data'] = array_values($crab[CRAB_CSC]['data']);
        $crab[CRAB_SOL]['data'] = array_values($crab[CRAB_SOL]['data']);
        $json = array_merge($json, array_values($crab));
    }

    $smarty->assign('json', json_encode($json));
    $smarty->assign('months', json_encode(array_keys($months)));

    $body .= $smarty->fetch("$PATH/kpi_pdi_crab.tpl");
}


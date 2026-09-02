<?php

class tldKpiDefinitionPopup
{
    private $title;
    private $content;
    private $link;

    public function __construct(tldKpiDefinition $definition)
    {
        $this->title = self::textConversionHack(str_replace("'", "\'", $definition->getTitle()));
        $this->content = $definition->getContent();
        $this->link = self::textConversionHack('Definition: '.$definition->getTitle());
    }

    private static function textConversionHack($text)
    {
        return htmlentities($text, ENT_QUOTES | ENT_HTML401, 'UTF-8');
    }

    public function fetch()
    {
        $popup = new tldOverlib(
            $this->content,
            [
                'CAPTION' => $this->title,
                'WIDTH' => 500,
                'linkName' => $this->link,
            ]
        );
        return $popup->fetch();
    }
}

class tldKpiDefinition
{
    private $title;
    private $content;
    private $groupTarget;

    public function __construct($identifier, $lang = 'en')
    {
        $definition = tldKpiDefinitionCollection::getByIdentifier($identifier);
        if (!isset($definition[$lang])) {
            $lang = 'en';
        }

        $this->title = $definition[$lang]['title'];
        $this->content = $definition[$lang]['definition'];
        if (isset($definition['GroupTarget']) && !empty($definition['GroupTarget'])) {
            $this->content .= "<b>Group Target:</b> {$definition['GroupTarget']}";
            $this->groupTarget = $definition['GroupTarget'];
        }
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function hasGroupTarget()
    {
        return !empty($this->groupTarget);
    }

    public function getGroupTarget()
    {
        return $this->groupTarget;
    }
}

class tldKpiDefinitionCollection
{
    public static function getByIdentifier($identifier)
    {
        // manipulate identifier
        $identifier = explode('_', $identifier);
        // get all
        $list = self::getList();
        // return following identifier
        return $list[$identifier[0]][$identifier[1]];
    }

    public static function getList()
    {
        return [
            'delivery' => [
                'gt28' => [
                    'en' => [
                        'title' => "End of Month GT in last 3 days of the month",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all the units First green tagged during one month, this is the percentage of the units that have been First green tagged after the 28th day of the month.</p>
                            <p><b>How it is generated?</b><br>It is generated automatically out of the QA/GT module every month.</p>
                        ",
                    ],
                    'fr' => [
                        'title' => "End of Month GT in last 3 days of the month",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all the units First green tagged during one month, this is the percentage of the units that have been First green tagged after the 28th day of the month.</p>
                            <p><b>How it is generated?</b><br>It is generated automatically out of the QA/GT module every month.</p>
                        ",
                    ],
                    'GroupTarget' => 10,
                ],

                'gt20' => [
                    'en' => [
                        'title' => "End of Month GT in last 10 days of the month",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all the units First green tagged during one month, this is the percentage of the units that have been First green tagged after the 20th day of the month.</p>
                            <p><b>How it is generated?</b><br>It is generated automatically out of the QA/GT module every month.</p>
                        ",
                    ],
                    'fr' => [
                        'title' => "End of Month GT in last 10 days of the month",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all the units First green tagged during one month, this is the percentage of the units that have been First green tagged after the 20th day of the month.</p>
                            <p><b>How it is generated?</b><br>It is generated automatically out of the QA/GT module every month.</p>
                        ",
                    ],
                    'GroupTarget' => 33,
                ],

                'otdpFactory' => [
                    'en' => [
                        'title' => "OTDP Factory",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all units delivered this month in this factory, this is the percentage of units First Green Tagged at max 4 days after the factory promised date</p>
	                        <p><b>How it is generated?</b><br>Generated directly out of the ODP online every month</p>
                        ",
                    ],
                    'fr' => [
                        'title' => "GT réalisés à l'heure par rapport à l'engagement usine",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all units delivered this month in this factory, this is the percentage of units First Green Tagged at max 4 days after the factory promised date</p>
	                        <p><b>How it is generated?</b><br>Generated directly out of the ODP online every month</p>
                        ",
                    ],
                    'GroupTarget' => 95,
                ],

                'otdpFactoryAVGLate' => [
                    'en' => [
                        'title' => "OTDP Factory AVG days late",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all units delivered this month in this factory, you make the sum of days late for only the units late and divide it by the number of units First GT during the month</p>
	                        <p>How it is generated?</b><br>Generated directly out of the ODP online every month</p>
                        ",
                    ],
                    'fr' => [
                        'title' => "Nombre de jour moyen en retard par rapport à l'engagement usine",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>On all units delivered this month in this factory, you make the sum of days late for only the units late and divide it by the number of units First GT during the month</p>
	                        <p><b>How it is generated?</b><br>Generated directly out of the ODP online every month</p>
                        ",
                    ],
                    'GroupTarget' => 2,
                ],

            ],
            'greentag' => [
                'GTcount' => [
                    'en' => [
                        'title' => 'GT count per day ',
                        'definition' => "
                            <p><b>What it does measure exactly?</b>
                            <br>- Estimated GT (Qty)
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units with an estimated 1st GT date in the month (info from ER) including late units and excluding units already 1st GT or shipped in previous months
                            <br>- Factory Promised Customer Date (Qty)
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units  promised to be 1st GT including late units, and excluding units already 1st GT or shipped in previous months
                            <br>- Released (Qty)
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units released for YT or GT based on question OP#990 answered in PIO
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] PIO units released for YT or GT
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Based on the date of the last answer 'Y' found in question(s) linked to PIO-OP#990

                            <br>- 1st GT (Qty) 
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units with a 1st GT date in the month
                            <br><br><i>NB : Models can only be selected on <b>T units</b> button selection</i>
                        ",
                    ],
                    'fr' => [
                        'title' => 'GT count per day ',
                        'definition' => "
                            <p><b>What it does measure exactly?</b>
                            <br>- Estimated GT (Qty)
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units with an estimated 1st GT date in the month (info from ER) including late units and excluding units already 1st GT or shipped in previous months
                            <br>- Factory Promised Customer Date (Qty)
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units  promised to be 1st GT including late units, and excluding units already 1st GT or shipped in previous months
                            <br>- Released (Qty)
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units released for YT or GT based on question OP#990 answered in PIO
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] PIO units released for YT or GT
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Based on the date of the last answer 'Y' found in question(s) linked to PIO-OP#990

                            <br>- 1st GT (Qty) 
                            <br />&nbsp;&nbsp;&nbsp;&nbsp;Number of [T and/or P] units with a 1st GT date in the month
                            <br><br><i>NB : Models can only be selected on <b>T units</b> button selection</i>
                        ",
                    ],
                    'GroupTarget' => '',
                ],
                'TPcount' => [
                    'en' => [
                        'title' => "GT TP sum per day ",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>- Estimated GT date (CUR) = Value in selected currency of units estimated to be GT at date based on ER and SOL information
                            <br>- Promised TP (CUR) = Value in selected currency of units promised to be GT at date, including late units, based on ODP and ER
                            <br>- Released for GT or YT (CUR) = Value in selected currency of T units released for YT or GT based on question #990 answered on PIO
                            <br>- 1st GT (CUR) = Negitiated TP of the units 1st GT in the month
                        ",
                    ],
                    'fr' => [
                        'title' => "GT TP sum per day ",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>- Estimated TP (CUR) = Value in selected currency of units estimated to be GT at date based on ER and SOL information
                            <br>- Promised TP (CUR) = Value in selected currency of units promised to be GT at date, including late units, based on ODP and ER
                            <br>- Released TP (CUR) = Value in selected currency of T units released for YT or GT based on question #990 answered on PIO
                            <br>- First TP (CUR) = Negitiated TP of the units 1st GT in the month
                        ",
                    ],
                    'GroupTarget' => '',
                ],

            ],
            'engineering' => [
                'EAPClosedRepartitionByModel' => [
                    'en' => [
                        'title' => 'EAP CLOSED Repartition by model',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP closed during that month for a given model</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "Repartition de la fermeture des EAP par modele",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP closed during that month for a given model</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                ],
                'EAPClosedRepartitionByFamily' => [
                    'en' => [
                        'title' => 'EAP CLOSED Repartition by family',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP closed during that month for a given family</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "Repartition de la fermeture des EAP par famille",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP closed during that month for a given family</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                ],
                'EAPNotifiedRepartitionByType' => [
                    'en' => [
                        'title' => 'EAP NOTIFIED Repartition by type',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP notified during that month for a given type</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "Repartition de la notification des EAP par type",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP notified during that month for a given type</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                ],
                'OTE' => [
                    'en' => [
                        'title' => 'On Time Engineering',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the percentage of gate closure on time (+15days)</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "On Time Engineering",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the percentage of gate closure on time (+15days)</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                    'GroupTarget' => 70,
                ],
                'OnBudgetProgramDelivery' => [
                    'en' => [
                        'title' => 'On Budget Program Delivery',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the MEAP budget deviation</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "On Budget Program Delivery",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the MEAP budget deviation</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                ],
                'EAPQtyRemainingPerMonth' => [
                    'en' => [
                        'title' => 'EAP Qty Remaining Open by equipment type per month',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP remaining during that month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => 'EAP Qty Remaining Open by equipment type per month',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP remaining during that month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                ],
                'EAPQtyRemainingPerMonthByCategory' => [
                    'en' => [
                        'title' => 'EAP Qty Remaining Open by category per month',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP remaining during that month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => 'EAP Qty Remaining Open by category per month',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP remaining during that month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                ],
                'EAPMonthlyFinishRate' => [
                    'en' => [
                        'title' => 'Monthly EAP finish rate (%)',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the percentage of EAP closed during that month. (Qty Closed) / (Qty Closed + Qty remaining) * 100</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => 'Monthly EAP finish rate (%)',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the percentage of EAP closed during that month. (Qty Closed) / (Qty Closed + Qty remaining) * 100</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                ],
                'EAPQtyNotifiedPerMonth' => [
                    'en' => [
                        'title' => 'EAP Qty NOTIFIED per month',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP notified during that month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => 'EAP Qty NOTIFIED per month',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAP notified during that month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                ],
                'EAPage' => [
                    'en' => [
                        'title' => 'EAP age',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>The age is calculated for the opened EAP starting from the creation date</p>
		                    <b>How it is generated?</b><ul><li>An EAP with NOTIFICATION or CLOSED is considered as out of the scope of the calculation</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => 'EAP age',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>The age is calculated for the opened EAP starting from the creation date</p>
		                    <b>How it is generated?</b><ul><li>An EAP with NOTIFICATION or CLOSED is considered as out of the scope of the calculation</li></ul>
                        ",
                    ],
                ],
                'CBOMOnTime' => [
                    'en' => [
                        'title' => 'CBOM on TIME [%]',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br> Rate of ERs on-time with CBOM last update date prior to Promised CBOM date</p>
                        ",
                    ],
                    'fr' => [
                        'title' => 'CBOM on TIME [%]',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br> Rate of ERs on-time with CBOM last update date prior to Promised CBOM date</p>
                        ",
                    ],
                    'GroupTarget' => 85,
                ],
                'CBOMDelayAverage' => [
                    'en' => [
                        'title' => 'CBOM delay average [days]',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br> Sum-up delays in days / the number of ERs in delay (CBOM last update date after the Promised CBOM date )</p>
                        ",
                    ],
                    'fr' => [
                        'title' => 'CBOM delay average [days]',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br> Sum-up delays in days / the number of ERs in delay (CBOM last update date after the Promised CBOM date )</p>
                        ",
                    ],
                    'GroupTarget' => 5,
                ],
                'EAPNotifiedIn72Hrs' => [
                    'en' => [
                        'title' => 'EAP % NOTIFIED In 72 Hours for Previous 12 Months',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the percent of EAPs NOTIFIED in 72 hours from time opened.
                            Takes: (SUM of EAPs NOTIFIED / SUM of EAPs OPENED) * 100</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "EAP % NOTIFIED In 72 Hours for Previous 12 Months",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the percent of EAPs NOTIFIED in 72 hours from time opened.
                            Takes: (SUM of EAPs NOTIFIED / SUM of EAPs OPENED) * 100</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                    'GroupTarget' => 15,
                ],
                'EAPAverageNumberOfDaysToClose' => [
                    'en' => [
                        'title' => 'EAP Average Number of Days To Close',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the Average Number Of Days To Close An EAP.
                            Takes: Sum of days to close the EAP for all the EAP closed this month / number of EAP closed this month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "EAP Average Number of Days To Close",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the Average Number Of Days To Close An EAP.
                            Takes: Sum of days to close the EAP for all the EAP closed this month / number of EAP closed this month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                    'GroupTarget' => 90,
                ],
                'EAPAverageNumberOfDaysOpen' => [
                    'en' => [
                        'title' => 'EAP Average Number of Days Open',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the Average Number Of Days An EAP is Open.
        Takes: Sum of opened days of all EAP at the given month [Looking at full EAP backlog] / number of EAP still opened at the given month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>
                        ",
                    ],
                    'fr' => [
                        'title' => "EAP Average Number of Days Open",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the Average Number Of Days An EAP is Open.
        Takes: Sum of opened days of all EAP at the given month [Looking at full EAP backlog] / number of EAP still opened at the given month</p>
		                    <b>How it is generated?</b><ul><li>Automatically generated from MEAP module</li></ul>
                        ",
                    ],
                ],
                'EAPQtyOpenPerMonth' => [
                    'en' => [
                        'title' => 'EAP Qty open per month',
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Measures the number of EAPs opened and still open at the end of each month.</p>
                            <p><b>How it is generated?</b><br>Automatically generated from EAP module, based on EAP open date and closure date.</p>
                        ",
                    ],
                    'fr' => [
                        'title' => 'EAP ouverts par mois',
                        'definition' => "
                            <p><b>Que mesure exactement ce graphe ?</b><br>Mesure le nombre d'EAP ouverts et toujours ouverts à la fin de chaque mois.</p>
                            <p><b>Comment est-il généré ?</b><br>Généré automatiquement à partir du module EAP, en fonction des dates d'ouverture et de fermeture.</p>
                        ",
                    ],
                ],
            ],
            'inventory' => [


            ],

            'production' => [
                'industrialnetsales' => [
                    'en' => [
                        'title' => "Net Sales BU (k local currency) / Productive hours",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>NNet Sales BU (k local currency) / Productive hours</p>
	                        <p>How it is generated?</b><br>Industrial Net Sales = Total sales of the month for the considered BU expressed in k local currency / Productive hours of the considered month.</p>
                        ",
                    ],
                    'GroupTarget' => '',
                ],
                'industrialnetsalessurface' => [
                    'en' => [
                        'title' => "Net Sales BU (k local currency) / Surface dedicated to manufacturing",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Net Sales BU (k local currency) / Surface dedicated to manufacturing</p>
	                        <p>How it is generated?</b><br> Industrial Net Sales expressed in k local currency per workshop sqm = Total sales of the month for the considered BU(Manufacturing\KPI\KPI admin)/ workshop surface.</p>
                        ",
                    ],
                    'GroupTarget' => '',
                ],
                'whse&SFE' => [
                    'en' => [
                        'title' => "Number of warehouse employees (WH KEEPER + GL/2) / Number of shopfloor operators (SFE + GL/2) (in %)",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Number of warehouse employees (WH KEEPER + GL/2) / Number of shopfloor operators (SFE + GL/2) (in %)</p>
	                        <p>How it is generated?</b><br>Warehouse Direct employees number: number of employees with warehouse keeper function + TLD Material Control and Purchasing Group Leader function at 0,5 FTE (full time employee) 
	                        <br>Shopfloor Operators number: number of TLD employees with SHOP FLOOR EMPLOYEE function + TLD Production Group Leader function accounted at 0.5 FTE.</p>
                        ",
                    ],
                    'GroupTarget' => '',
                ],
            ],

            'support' => [
                'WCByType' => [
                    'en' => [
                        'title' => "WC by Type in the month",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Number of warranty claims by type</p>
	                        <p>How it is generated?</b><br></p>
                        ",
                    ],
                    'GroupTarget' => '',
                ],
                'WCByModel' => [
                    'en' => [
                        'title' => "WC by Model in the month",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Number of warranty claims by model</p>
	                        <p>How it is generated?</b><br></p>
                        ",
                    ],
                    'GroupTarget' => '',
                ],
                'WCByYear' => [
                    'en' => [
                        'title' => "WC in past 5 years",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Number of warranty claims in gaven year</p>
	                        <p>How it is generated?</b><br></p>
                        ",
                    ],
                    'GroupTarget' => '',
                ],
            ],

            'quality' => [

                'perfectCommissioning' => [
                    'en' => [
                        'title' => "CSR commissioning noted 5/5/5 by month of the GT of the machine",
                        'definition' => "
                            <p><b>What it does measure exactly?</b><br>Number of CSR commissioning with a 5/5/5 rating (4/5/5 or 5/4/5 does not work !) over the total number of CSR commissioning during a calendar month for a said factory. Ratio in %</p>
	                        <p>How it is generated?</b><br>Generated directly out of the CSR Commissioning survey</p>
                        ",
                    ],
                    'GroupTarget' => 95,
                ],

                'avgWC' => [
                    'en' => [
                        'title' => "WC - Average number of WC per Machine, shipped < 2 years",
                        'definition' => "
                        <p><b>What it does measure exactly?</b><br>Measures the average number of WC per Machine per Year.
		(Number of WC not 'SALES CONCESSION' or 'REJECTED') / (SUM of shipped Machines)</p>
		<b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>
		<b>Targets ER Types</b><table border=1>
								   <tr><td>Conventional Aircraft Tractors	</td><td> 0.5</td></tr>
								   <tr><td>Towbarless Aircraft Tractors		</td><td> 1.5</td></tr>
								   <tr><td>Catering Trucks					</td><td> 2</td></tr>
								   <tr><td>Maintenance Platforms			</td><td> 0.5</td></tr>
								   <tr><td>Trailers and Dollies				</td><td> 0.1</td></tr>
								   <tr><td>Loaders							</td><td> 1.5</td></tr>
								   <tr><td>Baggage Tractors					</td><td> 0.5</td></tr>
								   <tr><td>Belt Loaders						</td><td> 0.4</td></tr>
								   <tr><td>Passenger Steps					</td><td> 0.5</td></tr>
								   <tr><td>Transporters						</td><td> 1.5</td></tr>
								   <tr><td>Air Conditioners					</td><td> 0.4</td></tr>
								   <tr><td>Air Starters						</td><td> 0.25</td></tr>
								   <tr><td>Ground Power Units				</td><td> 0.25</td></tr>
								   <tr><td>Lav and Water Trucks				</td><td> 0.5</td></tr>
								   <tr><td>Military Loaders					</td><td> 1.5</td></tr>
								   <tr><td>Military Coolers					</td><td> 0.25</td></tr>
								   <tr><td>Miscellaneous				    </td><td> 0.5</td></tr>
								   <tr><td>Power Conversion					</td><td> 0.75</td></tr>
								   <tr><td>Distribution Systems				</td><td> 0.5</td></tr>
								</table>
                        ",
                    ],
                    'GroupTarget' => 0.75,
                ],

            ],

            'vendors' => [


            ],
        ];
    }

}


// LEGACY Setup of the definition description for Help Page and Graph definition popups

$help = [

// VENDOR OTDP ---->

    "OTDP Vendors reliability Target" => 95,

    "OTDP Vendors reliability Definition" => "<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of line items received within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

    "OTDP Vendors reliability by Parts Definition" => "<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of a specified part received within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

    "OTDP Vendors reliability by Buyer Definition" => "<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of line items received by the buyer, within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

    "OTDP Vendors reliability by Vendor Definition" => "<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of line items received for a specified vendor, within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

    "Days of Payables Out Definition" => "<p><b>What it does measure exactly?</b><br>It measures the Account Payables in days of sales (i.e. the time we take to pay our suppliers in average)</p>
    <b>How it is generated?</b><ul><li>Trade Accounts Payable divided by ((Sales of past 3 months) / 90)</li></ul>
    <p><i>Trade Accounts Payable and Sales are entered in the KPI Admin</i></p>",
    "Days of Payables Out Group Target" => 60,

    "VWC Resolved Rate Definition" => "<p><b>What it does measure exactly?</b><br>It measures the VWC Closed as Resolved Percentage</p>
    <b>How it is generated?</b><ul><li>Calculated as the ratio of \"CLOSED_RESOLVED\" vs \"total number of closed\" (%)</li></ul>",
    "VWC Resolved Rate Group Target" => "NA",

    "VWC Closed Rate Definition" => "<p><b>What it does measure exactly?</b><br>It measures the VWC Closed to open</p>
    <b>How it is generated?</b><ul><li>Calculated as the ratio of \"total number closed\" vs \"total number of open\" (%)</li></ul>",
    "VWC Closed Rate Group Target" => "NA",

    "VWC Average Resolved Time Definition" => "<p><b>What it does measure exactly?</b><br>It averages the amount of days opened for VWC Closed as Resolved</p>",
    "VWC Average Resolved Time Group Target" => "NA",

    "Evendor system usage(Percentage)Definition" => "<p><b>What it does measure exactly?</b><br>It shows activity of active eVendor accounts and their weight in total figure of vendors  for each ERP# disregarding Shadow connections</p>
	<b>How it is generated?</b><ul><li> Per ERP# [Number of Vendors (through 1 or N eVendor valid contacts i.e. enable) who logged-in at least once during the month] / [total number of unique Vendors in ERP # ]</li></ul>",
    "Evendor system usage(Percentage)Definition Group Target" => "NA",

    "Evendor system usage(Percentage) Definition" => "<p><b>What it does measure exactly?</b><br>It shows activity of active eVendor accounts for each ERP#  disregarding Shadow connections</p>
	<b>How it is generated?</b><ul><li> Per ERP# [Number of Vendors (through 1 or N eVendor valid contacts i.e. enable) who logged-in at least once during the month] / [total number of Vendors linked to eVendor system (through 1 or N eVendor valid contacts)]</li></ul>",
    "Evendor system usage(Percentage) Definition Group Target" => "NA",
// PURCHASING ---->

    "Vendor PO Confirm Definition" => "<p><b>What it does measure exactly?</b><br>It measures the percentage of POL confirmed dates from vendors</p>
    <b>How it is generated?</b><ul><li>Percent calculated from POL from Baan to confirmed date submitted from eVendor (date by PO opened date)</li></ul>",
    "Vendor PO Confirm Group Target" => 98,

// <------

    "Cycle Count Quantity  Definition" => "<p><b>What it does measure exactly?</b><br>Measures the percentage of line items cycle counted during the month on which a variance has been found</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the number of items they counted during the month</li>
								<li>the number of items on which a variance was found</li></ul>
								The system calculates and display the ratio between these two numbers in percentage (2 digits)</ul>",
    "Cycle Count Quantity Group Target" => 2,

    "Cycle Count Value Definition" => "<p><b>What it does measure exactly?</b><br>Measures the percentage on inventory valuation variance found during the cycle count of the month</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the $ value of items they counted during the month</li>
								<li>the sum of the value of the variances found</li></ul>
								The system calculates and display the ratio between these two numbers in percentage (2 digits)</ul>",
    "Cycle Count Value Group Target" => 0.5,

    "Inventory Value Definition" => "<p><b>What it does measure exactly?</b><br>Measures the inventory value in days of Sales </p>
	<b>How it is generated?</b><ul><li>One number to enter monthly by BU accounting people</li>
									<li>equal to <<i>Net Inventory Value</i> divided by (Total Sales of past 3 months) / 90</li></ul>",
    "Inventory Value Group Target" => 45,

    "Inventory KPI Definition" => "<p><b>What it does measure exactly?</b><br>Inventory data in past 12 months</p>
	<b>How it is generated?</b><ul><li>Finish googd value</li>
									<li>equal to <<i>Raw materiall value</i>> minus <<i>Inventory value</i>> minus <<i>WIP value</i>> </li></ul>",
    "Inventory Target" => "NA",

    "Work in Progress Value Definition" => "<p><b>What it does measure exactly?</b><br>Measures the WIP value in days of Sales </p>
	<b>How it is generated?</b><ul><li>One number to enter monthly by BU accounting people</li>
									<li>equal to <i>Net WIP Inventory Value</i> divided by (Total Sales of past 3 months) / 90</li></ul>",
    "Work in Progress Value Group Target" => 30,

    "Factory Standard Efficiency Definition" => "<p><b>What it does measure exactly?</b><br>Measures the ratio between Standard Hours allocated on units and Actual hours allocated on units.</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the standard hours allocated to units shipped in the month</li>
								<li>the actual hours spent on units shipped in the month</li></ul>
								The system calculates and display the ratio between these two numbers in percentage format</ul>",
    "Factory Standard Efficiency Group Target" => 95,

    "Factory Productive Hour Ratio Definition" => "<p><b>What it does measure exactly?</b><br>Measures the ratio between productive hours a allocated on work orders during the month and potential hours of the factory during the same month.</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the productive hours allocated on work orders during the month
								<li>the potential hours of the factory during the same month</ul>
								The system calculates and display the ratio between these two number in percentage</ul>",
    "Factory Productive Hour Ratio Group Target" => 85,

    "Factory Productivity Definition" => "<p><b>What it does measure exactly?</b><br>Measures the real productivity of the factory </p>
	<b>How it is generated?</b><ul><li>Direct result of FSE x PHR and must be displayed in percentage</li></ul>",
    "Factory Productivity Group Target" => 80,

    "ITR Definition" => "<p><b>What it does measure exactly?</b><br> Inventory Turnover Ratio = Net Sales (average 3 months) / Inventory at Cost (raw materials) </p>",
    "ITR Target" => 'NA',

// QUALITY KPI ---->

    "Internal Customer Satisfaction Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measure the SSO satisfaction for this factory</p>
        <b>How it is generated?</b><ul><li>Input by the PSM of one number (0 to 5, 2 digits) for each SSO, so three inputs by factory</li></ul>",
    "Internal Customer Satisfaction Group Target" => 4.5,

    "Warranty Claim Counts Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the number of WC for this factory during the month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Warranty module</li></ul>",
    "Warranty Claim Counts Group Target" => 'NA',

    "AVGWC" =>
        "<p><b>What it does measure exactly?</b><br>Measures the average number of WC per Machine per Year.
		(Number of WC not 'SALES CONCESSION' or 'REJECTED') / (SUM of shipped Machines)</p>
		<b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>
		<b>Targets ER Types</b><table border=1>
								   <tr><td>Conventional Aircraft Tractors	</td><td> 0.5</td></tr>
								   <tr><td>Towbarless Aircraft Tractors		</td><td> 1.5</td></tr>
								   <tr><td>Catering Trucks					</td><td> 2</td></tr>
								   <tr><td>Maintenance Platforms			</td><td> 0.5</td></tr>
								   <tr><td>Trailers and Dollies				</td><td> 0.1</td></tr>
								   <tr><td>Loaders							</td><td> 1.5</td></tr>
								   <tr><td>Baggage Tractors					</td><td> 0.5</td></tr>
								   <tr><td>Belt Loaders						</td><td> 0.4</td></tr>
								   <tr><td>Passenger Steps					</td><td> 0.5</td></tr>
								   <tr><td>Transporters						</td><td> 1.5</td></tr>
								   <tr><td>Air Conditioners					</td><td> 0.4</td></tr>
								   <tr><td>Air Starters						</td><td> 0.25</td></tr>
								   <tr><td>Ground Power Units				</td><td> 0.25</td></tr>
								   <tr><td>Lav and Water Trucks				</td><td> 0.5</td></tr>
								   <tr><td>Military Loaders					</td><td> 1.5</td></tr>
								   <tr><td>Military Coolers					</td><td> 0.25</td></tr>
								</table>",
    "AVGWC target" => '0.75',

    "MTBF" =>
        "<p><b>What it does measure exactly?</b><br>Measures (SUM of NB days between [last day of month] and ER shipped date) / 
	   (Number of WC not 'SALES CONCESSION' or 'REJECTED') for all ER that have been shipped 24 months before the last day of the period</p>
        <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>
		<b>Targets ER Types</b><table border=1>
								   <tr><td>Conventional Aircraft Tractors	</td><td> 750</td></tr>
								   <tr><td>Towbarless Aircraft Tractors		</td><td> 250</td></tr>
								   <tr><td>Catering Trucks					</td><td> 200</td></tr>
								   <tr><td>Maintenance Platforms			</td><td> 750</td></tr>
								   <tr><td>Trailers and Dollies				</td><td> 3500</td></tr>
								   <tr><td>Loaders							</td><td> 250</td></tr>
								   <tr><td>Baggage Tractors					</td><td> 750</td></tr>
								   <tr><td>Belt Loaders						</td><td> 1000</td></tr>
								   <tr><td>Passenger Steps					</td><td> 750</td></tr>
								   <tr><td>Transporters						</td><td> 250</td></tr>
								   <tr><td>Air Conditioners					</td><td> 1000</td></tr>
								   <tr><td>Air Starters						</td><td> 1500</td></tr>
								   <tr><td>Ground Power Units				</td><td> 1500</td></tr>
								   <tr><td>Lav and Water Trucks				</td><td> 750</td></tr>
								   <tr><td>Military Loaders					</td><td> 250</td></tr>
								   <tr><td>Military Coolers					</td><td> 1500</td></tr>
								</table>",
    "MTBF target" => "365",
    /*
        "AVGTMY"=>
            "<p><b>What it does measure exactly?</b><br>Measures the Average of Time of WC per Machine per Year.
            Takes: (Number of WC not 'SALES CONCESSION' or 'REJECTED') / (SUM of Time between [last day of month] and ER shipped date) / The Number of ER
            for all ER that have been shipped 24 months before the last day of the period</p>
            <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>",
        "AVGTMY target"=>"NA",
    */
    "ATFF" =>
        "<p><b>What it does measure exactly?</b><br>Measures (SUM of day between first WC and ER ship date) / (NB ER with at least one WC)
        for all ER that have been shipped 24 months before the last day of the period and ER have at least a WC</p>
        <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>",
    "ATFF target" => "None",

    "MNOWC3" =>
        "<p><b>What it does measure exactly?</b><br>Measures (SUM machines WHEN first WC date - ship date > 90 days) / (SUM of shipped machines)
        for all ER that have been shipped 24 months before the last day of the period</p>
        <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>
		<b>Targets ER Types</b><table border=1>
								   <tr><td>Conventional Aircraft Tractors	</td><td> 85</td></tr>
								   <tr><td>Towbarless Aircraft Tractors		</td><td> 75</td></tr>
								   <tr><td>Catering Trucks					</td><td >75</td></tr>
								   <tr><td>Maintenance Platforms			</td><td> 85</td></tr>
								   <tr><td>Trailers and Dollies				</td><td> 98</td></tr>
								   <tr><td>Loaders							</td><td> 75</td></tr>
								   <tr><td>Baggage Tractors					</td><td> 95</td></tr>
								   <tr><td>Belt Loaders						</td><td> 90</td></tr>
								   <tr><td>Passenger Steps					</td><td> 95</td></tr>
								   <tr><td>Transporters						</td><td> 90</td></tr>
								   <tr><td>Air Conditioners					</td><td> 85</td></tr>
								   <tr><td>Air Starters						</td><td> 90</td></tr>
								   <tr><td>Ground Power Units				</td><td> 95</td></tr>
								   <tr><td>Lav and Water Trucks				</td><td> 85</td></tr>
								   <tr><td>Military Loaders					</td><td> 75</td></tr>
								   <tr><td>Military Coolers					</td><td> 95</td></tr>
								</table>",
    "MNOWC3 target" => "85",

    "PDC Focus Weight Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measure the FW of the factory during that month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Support module</li></ul>",
    "PDC Focus Weight Group Target" => 'NA',

    "PDC in Progress Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the number of PDC in progress</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Support module</li></ul>",
    "PDC in Progress Group Target" => 35,

    "PDC Opened During the Month Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the number of PDC opened during that month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Support module</li></ul>",
    "PDC Opened During the Month Group Target" => 'NA',

    "CRAB Average Per Type For All GT Units Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the Average of CRABS on GT Units per Type.
        Takes: (SUM of CRABs by type (per unit First GT date of given month) / (SUM of units First GT date of given month)</p>
        <b>How it is generated?</b><ul><li>Automatically generated by CRAB and ER module</li></ul>",
    "CRAB Average Per Type For All GT Units Group Target" => "NA",

    "VWC Supplier Recovery Cost Past 12 Months" =>
        "<p><b>What it does measure exactly?</b><br>Sum of the supplier recovery cost.</p>
        <b>How it is generated?</b><ul><li>Automatically generated by VWC module</li></ul>",
    "VWC Supplier Recovery Cost Group Target" => "NA",

// ENGINEERING KPI ---->

    "EAP Qty per month for Previous 12 Months Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the number of EAP remaining during that month</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li>
		<li>SUM of EAP, not REJECTED, still remaining for the specified month</li></ul>",
    "EAP Qty per month Group Target" => "200",

    "EAP Qty CLOSED per month for Previous 12 Months Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the number of PDC closed during that month</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>",
    "EAP Qty CLOSED per month Group Target" => "100",

    "EAP Qty CLOSED per month by Type for Previous 12 Months Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the number of EAP closed during that month by model type</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>",

    "EAP In Progress Late Report" => "<p><b>What it does measure exactly?</b><br>It provides the quantity of EAP under In Progress status by Model Type, according to the duration since EAP has been submit</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li><p>
		<li>Sum of EAP, in PENDING, sort by duration </li></ul>",

    "EAP % CLOSED In 72 Hours for Previous 12 Months Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the percent of EAPs CLOSED in 72 hours from time opened.
        Takes: (SUM of EAPs CLOSED / SUM of EAPs OPENED) * 100</p>
        <b>How it is generated?</b><ul><li>Automatically generated by EAP module</li></ul>",
    "EAP % CLOSED In 72 Hours Group Target" => "15",

    "EAP Average Number Of Days To Close for Previous 12 Months Definition" =>
        "<p><b>What it does measure exactly?</b><br>Measures the Average Number Of Days To Close An EAP.
        Takes: Sum of days to close the EAP for all the EAP closed this month / number of EAP closed this month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by EAP module</li></ul>",
    "EAP Average Number Of Days To Close Group Target" => "90",

    "DMS Monthly average days in revision past 12 month" => "<p><b>For all exisiting DMS of the considered BU In Revision, for the month considered, make an average of the days accumulated in between 
		</b><br> 
		<ul><li>If last status in Revision (last day of the considered month minus last revision date)</li>
		<li>for all other status: nothing is measured the purpose of the KPI</li></ul>",
    "Monthly average days in revision past 12 month target" => "NA",

    "DMS Monthly expired past 12 month" => "<p><b>Increase count by 1 if DMS :</b><br>
		<ul><li>Has reached expired status during that period (and expired is still end of that month status) or</li>
			<li>The last known status is expired (ie not Active, Revision, approval, Archive) even if reached during previous period</li></ul>",
    "Monthly expired past 12 month target" => "NA",
];

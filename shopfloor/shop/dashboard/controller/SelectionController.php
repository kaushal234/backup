<?php

class SelectionController
{
    /**
     * @var Smarty
     */
    public  $render;

    /**
     * @var string
     */
    private $view;
    /**
     * @var tldUser
     */
    private $user;

    /**
     * @param $smarty
     * @param tldUser $user
     */
    public function __construct($smarty,tldUser $user)
    {
        $this->render = $smarty;
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function checkAccess(){
        if($this->user->itsDetails === null){
            $this->render->assign('error', _('You are not connected to the PIO'));
            return false;
        }
        if(!$this->isUserAllowed([
            'pi_GL',
            'pi_ECQ',
            'role_PS',
            'role_MPE',
            'role_PM',
            'role_CMO',
            'role_CEO',
            'role_COO',
            'role_PLANNER',
        ])){
            $this->render->assign('error', _('You cannot access to this dashboard'));
            return false;
        }
        return true;
    }

    /**
     * @param array $rights
     * @return array|bool|int|string
     */
    public function isUserAllowed(array $rights){
        return $this->user->isInGroup($rights);
    }


    public function makeTheSelection(){
        if(!$this->checkAccess()){
            $this->view = __DIR__."/../view/messages.tpl";
            return;
        }

        //check the form
        $queryFamilyMatrix =<<<SQL
SELECT pifm.id, pifm.family, pifm.factory
FROM pi_family_matrix pifm
LEFT JOIN pi_family AS pif ON pif.family=pifm.family
WHERE pif.status != 'Deleted' 
ORDER BY pifm.family
SQL;
        $familyMatrix = TldUtils::getSqlToAssocArray($queryFamilyMatrix);
        $factories = array_unique(array_column($familyMatrix,'factory'));

        $this->render->assign('factories', $factories);
        $this->render->assign('familyMatrix',  $familyMatrix);
        $this->view = __DIR__."/../view/dashboard.view.tpl";
    }

    public function displayDashboardList(){
        $this->view = __DIR__."/../view/dashboardList.tpl";
    }

    public function getReportResults(array $formValues){
        $erp = tldLocation::getERPByLocation($formValues['factory']);

        $dbSrc = ($erp != '502' && $erp != '403')?["src" => "baan"]:["src" => "baantest"];

        //get the questions
        $formValues['families'] = str_replace(',',"','",$formValues['families'] );
        $query =<<<SQL
        SELECT service.sn, service.dgt_rev,  t_prno, service.t_pdno,family.family, t_opno, count(ans.id) AS nbAnswers,count(qst.id) AS nbQuestions FROM service AS service
        LEFT JOIN pi_unit_family AS family ON service.sn = family.unit
        LEFT JOIN pi_questions_unit AS qst ON service.sn = qst.unit
        LEFT JOIN pi_answers AS ans ON ans.parent_id = qst.id
        WHERE family.family IN ('{$formValues['families']}')
        AND service.man_location = '{$formValues['factory']}'
        AND (service.dgt_com ='0000-00-00' AND service.dyt ='0000-00-00')
        AND qst.active='Y'
        AND (ans.active='Y' or ans.active IS NULL)
        AND t_opno != 999 
SQL;
        if(isset($formValues['erFrom'])){
            $formValues['erFrom'] = substr($formValues['erFrom'],1);
            $query .=<<<SQL
            AND service.id >= {$formValues['erFrom']} 
SQL;
        }

        if(isset($formValues['erTo'])){
            $formValues['erTo'] = substr($formValues['erTo'],1);
            $query .=<<<SQL
            AND service.id <= {$formValues['erTo']} 
SQL;
        }

        if(isset($formValues['dateGT'])){
            $query .=<<<SQL
            AND service.dgt_rev <= '{$formValues['dateGT']}' 
SQL;
        }
//        $formValues['operations'] =",005-499,500-599,,,";
        $operationConstraints =[];
        $queryOperations ='';
        $formValues['operations'] = explode(',',$formValues['operations'] );
            foreach ($formValues['operations'] as $op) {
                if ($op !== '') {
                    $op = explode('-', $op);
                    $operationConstraints[] = <<<SQL
                  (t_opno >= {$op[0]} AND t_opno <= {$op[1]})
SQL;
                }

            }
            if(!empty($operationConstraints)){
                $queryOperations = ' AND (';
                $queryOperations.= implode(' OR ',$operationConstraints ).')';
                $query.= $queryOperations;
            }

            $query.=<<<SQL
        GROUP BY service.id, t_prno, service.t_pdno,t_opno;
SQL;
        $unitsRoutingAdvancements = tldUtils::getSqlToAssocArray($query);
        if(empty($unitsRoutingAdvancements)){
            return json_encode([]);
        }
        $projectsData = [];
        $projectFamilyLink = [];
        $operationFamilyLink = [];
        $oldProjectNumber = null;
        foreach ($unitsRoutingAdvancements as $unitRouting){
            $family = trim($unitRouting['family']);
            $projectNumber = trim($unitRouting['t_prno']);
            if(!array_key_exists($family,$projectsData)){
                $operationFamilyLink[$family] = [];
                $projectsData[$family] = [
                    'projects' => [],
                    'operations' => [],
                ];
            }
            if(!array_key_exists($projectNumber,$projectsData[$family]['projects'])){
                $projectsData[$family]['projects'][$projectNumber] = [
                    'serialNumber' => $unitRouting['sn'],
                    'estimatedGreenTag' => $unitRouting['dgt_rev'],
                    'operations' => [
                        'SUM' => [
                            'nbQuestions'=> 0,
                            'nbAnswers' => 0,
                            'percentage' => 0,
                            'clocking'=> 0,
                            'standardHours'=> 0,
                        ],
                    ]
                ];
                $projectFamilyLink[$projectNumber] = $family;
                if($oldProjectNumber !== null) {
                   $projectsData[$projectFamilyLink[$oldProjectNumber]]['projects'][$oldProjectNumber]['operations']["SUM"]['percentage'] = round(($projectsData[$projectFamilyLink[$oldProjectNumber]]['projects'][$oldProjectNumber]['operations']["SUM"]['nbAnswers'] / $projectsData[$projectFamilyLink[$oldProjectNumber]]['projects'][$oldProjectNumber]['operations']["SUM"]['nbQuestions']) * 100);
                }
                $oldProjectNumber = $projectNumber;
            }
            if(!array_key_exists((int)$unitRouting['t_opno'], $operationFamilyLink)){
                $operationFamilyLink[$family][(int)$unitRouting['t_opno']] =  [
                    'opno'=>$unitRouting['t_opno'],
                    'description'=>'',
                ];
            }

            $projectsData[$family]['projects'][$projectNumber]['operations'][$unitRouting['t_opno']] = [
              //  'taskId'=> null,
                'percentage'=>round(($unitRouting['nbAnswers']/$unitRouting['nbQuestions'])*100),
                'nbQuestions' => $unitRouting['nbQuestions'],
                'nbAnswers' => $unitRouting['nbAnswers'],
                'standardHours' => null,
                'clocking' => null,
            ];
            $projectsData[$family]['projects'][$projectNumber]['operations']["SUM"]['nbQuestions'] += $unitRouting['nbQuestions'];
            $projectsData[$family]['projects'][$projectNumber]['operations']["SUM"]['nbAnswers'] += $unitRouting['nbAnswers'];
        }

        if($oldProjectNumber !== null) {
            $projectsData[$family]['projects'][$oldProjectNumber]['operations']["SUM"]['percentage'] = round(($projectsData[$family]['projects'][$oldProjectNumber]['operations']["SUM"]['nbAnswers'] / $projectsData[$family]['projects'][$oldProjectNumber]['operations']["SUM"]['nbQuestions']) * 100);
        }

        foreach ($operationFamilyLink as $family =>&$operations) {
            ksort($operations);
            $operations['SUM']=['opno' => 'SUM',
                'description'=> 'SUM'];
            $projectsData[$family]['operations'] = $operations;
        }

        return json_encode($projectsData);

    }

    public function render()
    {
        return $this->render->fetch($this->view);
    }

}

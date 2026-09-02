<?php

declare(strict_types=1);

namespace App\Pi\Utils;

class QuestionManager
{
    /**
     * @return array|Question[]
     */
    public static function getQuestionsToAdd(array $unit, array $opno, $compPiErp): array
    {
        if (empty($opno)) {
            return [];
        }

        if (null === $unit['family']) {
            $unit['family'] = '';
        }

        $compNameList = [
            '220' => 'pow',
            '250' => 'aer',
            '400' => 'win',
            '410' => 'wim',
            '420' => 'she',
            '430' => 'wol',
            '500' => 'mtl',
            '510' => 'sor',
            '520' => 'stl',
            '570' => 'leb',
            '640' => 'sha',
            '660' => 'wux',
            '820' => 'mai',
        ];
        if (null === $compName = ($compNameList[$compPiErp] ?? null)) {
            return [];
        }
        $allPN = (null !== $unit['cbom']) ? implode("','", $unit['cbom']) : '';
        $operations = implode(',', $opno);
        $query = <<<SQL
SELECT
            qst.id,
            qst.parent_id ,
            t_opno ,
            qstpn.t_item ,
            model ,
            owner,
            mtl ,
            sha ,
            she ,
            sor ,
            stl ,
            win ,
            wim ,
            wux ,
            leb ,
            aer ,
            pow ,
            mai ,
            wol ,
            subject_en
            ,subject_fr
            ,subject_zh ,
            desc_en ,
            desc_fr ,
            desc_zh,
            position,
            help_en,
            help_fr,
            help_zh ,
            attachment_en ,
            attachment_fr ,
            attachment_zh ,
            answer_type ,
            answer_unit ,
            component_sn,
            match_list ,
            answer_max ,
            answer_min ,
            non_conformity ,
            created_on ,
            entered_by ,
            updated_on ,
            updated_by,
            create_mode ,
            gt1 ,
            gt3,
            active
FROM pi_questions AS qst
LEFT JOIN pi_questions_pn_xref AS qstpn ON qst.id= qstpn.question_id
WHERE t_opno IN($operations)
AND model = '{$unit['family']}'
AND (qstpn.t_item IN (' ','$allPN')
OR qstpn.t_item IS NULL)
AND active='Y'
AND $compName != 0
AND (dt_validity = '0000-00-00' OR dt_validity <'{$unit['productionStartDate']}')
AND  CONCAT_WS('~',qst.id,IFNULL(qstpn.t_item,'')) NOT IN
(SELECT CONCAT_WS('~',parent_id,t_item) FROM pi_questions_unit
WHERE t_opno IN ($operations)
AND model='{$unit['family']}'
AND comp=$compPiErp
AND unit='{$unit['sn']}');
SQL;

        $queryNbUnit = <<<SQL
                            SELECT COUNT(DISTINCT(unit)) AS nbUnit FROM pi_questions_unit
                            WHERE model='{$unit['family']}'
                            AND comp =$compPiErp;
SQL;
        $nbUnit = \tldUtils::getSqlRowToAssocArray($queryNbUnit);
        $questions = [];
        foreach (\tldUtils::getSqlToAssocArray($query) as $qst) {
            // check the factor of insertion
            if ($qst[$compName] > 1 && $nbUnit['nbUnit'] % $qst[$compName] !== 0) {
                // questions to inset but not to show
                $qst['active'] = 'N';
            }
            $questions[] = new Question($qst);
        }

        return $questions;
    }

    public static function insertQuestionUnit(array $questions, array $unit, $compPiErp)
    {
        $query = 'INSERT INTO pi_questions_unit (
        parent_id ,
        unit ,
        comp ,
        t_cprj ,
        t_pdno ,
        t_opno ,
        t_item ,
        model ,
        owner,
        mtl ,
        sha ,
        she ,
        sor ,
        stl ,
        win ,
        wim ,
        wux ,
        leb ,
        aer ,
        pow ,
        mai ,
        wol,
        subject_en
        ,subject_fr
        ,subject_zh ,
        desc_en ,
        desc_fr,
        desc_zh,
        position,
        help_en,
        help_fr,
        help_zh ,
        attachment_en ,
        attachment_fr ,
        attachment_zh ,
        answer_type ,
        answer_unit ,
        component_sn,
        match_list ,
        answer_max ,
        answer_min ,
        non_conformity ,
        created_on ,
        entered_by ,
        updated_on ,
        updated_by,
        create_mode ,
        gt1 ,
        gt3,
        active )
        VALUES ';

        $values = [];
        $unitData = [
            'unit' => $unit['sn'],
            'comp' => $compPiErp,
            'cprj' => $unit['t_prno'],
            'pdno' => $unit['t_pdno'],
        ];

        $datetime = (new \DateTime())->format('Y-m-d H:m:i');
        for ($i = 1, $length = \count($questions); $i <= $length; ++$i) {
            $questionArray = $questions[$i - 1]->toArray();
            $questionArray['createdOn'] = $datetime;

            // remove useless variable
            unset($questionArray['parentId'], $questionArray['dtValidity'], $questionArray['dtExpiration']);

            array_splice($questionArray, 1, 0, $unitData);
            $values[] = "('".implode("','", $questionArray)."')";

            if (0 === $i % 100 && $i !== $length) {
                \tldUtils::sqlInsert($query.implode(',', $values));
                $values = [];
            }
        }
        if (!empty($values)) {
            \tldUtils::sqlInsert($query.implode(',', $values));
        }
    }
}

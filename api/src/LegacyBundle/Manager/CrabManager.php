<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Directory\People;
use App\Entity\Quality\Crab;
use Doctrine\DBAL\Connection;

class CrabManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
    ) {
    }

    public function answerQuestion(Crab $crab, People $user)
    {
        $this->legacyConnection->executeQuery(
            "UPDATE pi_answers SET
            answer='YES',
            d_entered_by=':user',
            d_entered_on=NOW()
            WHERE parent_id=:piQuestionId
            AND id=:answerId",
            [
                'user' => $user->getLegacyId(),
                'piQuestionId' => $crab->piQuestionId,
                'answerId' => $this->getPiAnswer($crab),
            ]
        );
    }

    public function derogateQuestion(Crab $crab, People $user)
    {
        $this->legacyConnection->executeQuery(
            'UPDATE pi_answers SET
            derogation=:derogationId,
            d_entered_by=:user,
            d_entered_on=NOW()
            WHERE parent_id=:piQuestionId
            AND id=:answerId',
            [
                'derogationId' => $crab->derogation->getId(),
                'user' => $user->getLegacyId(),
                'piQuestionId' => $crab->piQuestionId,
                'answerId' => $this->getPiAnswer($crab),
            ]
        );
    }

    public function getPiQuestionType(Crab $crab)
    {
        $piQuestionType = $this->legacyConnection->fetchAssociative(
            'SELECT owner
            FROM pi_questions_unit
            WHERE id=:piQuestionId',
            [
                'piQuestionId' => $crab->piQuestionId,
            ]
        );

        return false === $piQuestionType ? null : $piQuestionType['owner'];
    }

    public function getPiQuestionDetails(Crab $crab)
    {
        $piQuestionDetails = $this->legacyConnection->fetchAssociative(
            'SELECT subject_en, desc_en
            FROM pi_questions_unit
            WHERE id=:piQuestionId',
            [
                'piQuestionId' => $crab->piQuestionId,
            ]
        );

        return false === $piQuestionDetails ? [] : $piQuestionDetails;
    }

    public function getPiQuestionParentId(Crab $crab): ?int
    {
        $piQuestionParentId = $this->legacyConnection->fetchAssociative(
            'SELECT parent_id
            FROM pi_questions_unit
            WHERE id=:piQuestionId
            LIMIT 1',
            [
                'piQuestionId' => $crab->piQuestionId,
            ]
        );

        return $piQuestionParentId['parent_id'] ?? null;
    }

    private function getPiAnswer(Crab $crab)
    {
        $piAnswer = $this->legacyConnection->fetchAssociative(
            'SELECT id FROM pi_answers WHERE parent_id=:id ORDER BY id DESC LIMIT 1',
            ['id' => $crab->piQuestionId]
        );

        if ($piAnswer) {
            return $piAnswer['id'];
        }
    }
}

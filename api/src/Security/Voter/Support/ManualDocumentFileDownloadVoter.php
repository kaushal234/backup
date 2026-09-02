<?php

declare(strict_types=1);

namespace App\Security\Voter\Support;

use App\Dto\Support\EquipmentRecordPublic\ManualDocumentPublic;
use App\Entity\Support\ManualDocument;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ManualDocumentFileDownloadVoter extends AbstractVoter
{
    public const string ATTRIBUTE = 'DOWNLOAD_MANUAL_DOCUMENT_FILE_VOTER';

    protected function supports(string $attribute, $subject): bool
    {
        return self::ATTRIBUTE === $attribute;
    }

    /**
     * @param ManualDocument|null $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$subject instanceof ManualDocument) {
            return false;
        }

        $security = $this->getSecurity();

        if ($security->isGranted('EQUIPMENT_ACCESS_VOTER', $subject->manual->equipmentRecord)) {
            return true;
        }

        if (!$security->isGranted('AUTHORIZED_APPLICATION_FEATURE_EXTRANET_PUBLIC')) {
            return false;
        }

        $category = $subject->category;
        if (null === $category) {
            return false;
        }

        return \in_array($category->name, ManualDocumentPublic::PUBLIC_CATEGORY_NAMES, true);
    }
}

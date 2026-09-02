<?php

declare(strict_types=1);

namespace App\Security\Voter\Activity;

use ApiPlatform\Metadata\IriConverterInterface;
use App\ApiPlatform\UniqueResourceMetadataCollectionFactory;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\TechnicianOnCall;
use App\Repository\Sales\ExtranetUserAclRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class CommentVoter extends AbstractVoter
{
    private const ROLE_TOC = 'role_TOC';

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[UniqueResourceMetadataCollectionFactory::class, IriConverterInterface::class, ExtranetUserAclRepository::class]];
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 'COMMENT_WRITE_VOTER' === $attribute && $subject instanceof Comment;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if ($user instanceof People && $this->getSecurity()->isGranted('FEATURE_COMMENT_WRITE')) {
            return true;
        }

        try {
            $target = $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($subject->getResource());
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestException(\sprintf('Item not found for %s', $subject->getResource()));
        }
        $extraProperties = $this->serviceLocator->get(UniqueResourceMetadataCollectionFactory::class)->getExtraProperties($target::class);

        return
            (null !== ($extraProperties[Comment::VENDOR_USER_COMMENTABLE] ?? null) && $user instanceof VendorUser && $this->getSecurity()->isGranted('BUSINESS_PARTNER_VOTER', $target))
            || (null !== ($extraProperties[Comment::EXTRANET_USER_COMMENTABLE] ?? null) && $target instanceof TechnicianOnCall && $user instanceof ExtranetUser && $this->serviceLocator->get(ExtranetUserAclRepository::class)->userHasGroup($user, self::ROLE_TOC) && $this->getSecurity()->isGranted('EQUIPMENT_ACCESS_VOTER', $target->equipmentRecord))
        ;
    }
}

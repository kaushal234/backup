<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\RecipientsFinder;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

#[FeatureDoc(path: 'toc-mail-builders.md')]
abstract class AbstractTocEmailBuilder implements TechnicianOnCallEmailBuilderInterface
{
    protected NormalizerInterface $normalizer;
    private TranslatorInterface $translator;
    private RecipientsFinder $recipientsFinder;
    private Security $security;
    private CustomerRelationshipTeamRepository $customerRelationshipTeamRepository;

    #[Required]
    public function setDependencies(
        TranslatorInterface $translator,
        RecipientsFinder $recipientsFinder,
        Security $security,
        CustomerRelationshipTeamRepository $customerRelationshipTeamRepository,
        NormalizerInterface $normalizer,
    ): void {
        $this->translator = $translator;
        $this->recipientsFinder = $recipientsFinder;
        $this->security = $security;
        $this->customerRelationshipTeamRepository = $customerRelationshipTeamRepository;
        $this->normalizer = $normalizer;
    }

    abstract public function supports(TechnicianOnCallMailSubject $subject): bool;

    public function build(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $technicianOnCall,
        array $context = [],
    ): TemplatedEmail {
        $currentUser = $this->security->getUser();
        $tos = $this->recipientsFinder->findTos($subject, $technicianOnCall, $context);
        $ccs = $this->recipientsFinder->findCcs($subject, $technicianOnCall, $context);

        return (new TemplatedEmail())
            ->from($currentUser instanceof People ? $currentUser->getEmail() : 'noreply@tld-gse.com')
            ->to(...$tos)
            ->cc(...$ccs)
            ->subject($this->getEmailSubjectTranslation($subject, $technicianOnCall))
            ->htmlTemplate(\sprintf('Emails/Service/TechnicianOnCall/%s.html.twig', $subject->value))
            ->context($this->buildContext($subject, $technicianOnCall, $context));
    }

    protected function buildContext(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $technicianOnCall,
        array $context,
    ): array {
        if ($subject->isExternal()) {
            $crt = $this->customerRelationshipTeamRepository->findOneBy([
                'customer' => $technicianOnCall->customer,
                'serviceLocation' => $technicianOnCall->salesOrganisationService,
                'deletedAt' => null,
            ], ['id' => 'ASC']);

            $context['crt'] = $this->normalizer->normalize($crt, null, ['groups' => ['customer_relationship_team', 'people_public', 'location_public']]);
        }

        return $context;
    }

    protected function getEmailSubjectTranslation(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): string
    {
        $translated = $this->translator->trans($this->getSubjectTranslationKey($subject, $technicianOnCall), [
            '%id%' => $technicianOnCall->getId(),
            '%productName%' => $technicianOnCall->equipmentRecord?->getProduct()?->getName() ?? '---',
            ...$this->getAdditionalTranslationParameters($subject, $technicianOnCall),
        ], 'emails');

        if ($this->shouldAppendFactoryFlagNotice($subject, $technicianOnCall)) {
            $translated .= ' - '.$this->translator->trans('toc.subject.factory_support_needed_suffix', [], 'emails');
        }

        return $translated;
    }

    abstract protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array;

    protected function getSubjectTranslationKey(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): string
    {
        return \sprintf('toc.subject.%s', $subject->value);
    }

    private function shouldAppendFactoryFlagNotice(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): bool
    {
        if (!$technicianOnCall->factoryFlag) {
            return false;
        }
        // This email already states the flag condition explicitly — avoid double messaging.
        if (TechnicianOnCallMailSubject::TOC_FACTORY_FLAG === $subject) {
            return false;
        }

        // Only internal-facing emails need this — not customer-facing emails.
        return !$subject->isExternal();
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\Controller\Communication;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/communication', defaults: ['alvest_module' => 'Communication'])]
class CommunicationController extends AbstractController
{
    #[Route(path: '/{entity}-boiler-plates', name: 'communication_boiler_plates', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function boilerPlate(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/boiler_plates.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-colour-palette', name: 'communication_colour_palette', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function colourPalette(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/colour_palette.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-business-cards', name: 'communication_business_cards', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function businessCards(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/business_cards.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-social-media', name: 'communication_social_media', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function socialMedia(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/social_media.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-logos', name: 'communication_logos', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|agsa|tracteasy'], methods: ['GET'])]
    public function logos(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/logos.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-wallpapers', name: 'communication_wallpapers', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function wallpapers(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/wallpapers.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-group-values', name: 'communication_group_values', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    #[Template('communication/group_values.html.twig')]
    public function groupValues(string $entity)
    {
        return ['entity' => $entity];
    }

    #[Route(path: '/{entity}-brand-guidelines', name: 'communication_brand_guidelines', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function brandGuidelines(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/brand_guidelines.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-email-signatures', name: 'communication_email_signatures', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function emailSignature(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/email_signatures.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-powerpoint-templates', name: 'communication_powerpoint_template', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function powerpointPresentation(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/powerpoint_templates.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-corporate-brochure', name: 'communication_corporate_brochure', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function corporateBrochure(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/corporate_brochure.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-data-sheets', name: 'communication_data_sheets', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function dataSheets(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/data_sheets.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-fact-sheets', name: 'communication_fact_sheets', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function factSheets(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/fact_sheets.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-posters', name: 'communication_posters', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function posters(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/posters.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-songs', name: 'communication_songs', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function songs(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/songs.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-letterheads', name: 'communication_letterheads', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function letterheads(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/letterheads.html.twig', $entity), ['entity' => $entity]);
    }

    #[Route(path: '/{entity}-linkedin-banners', name: 'communication_linkedin_banners', requirements: ['entity' => 'tld|alvest|aero-specialties|aes|sas|sage-parts|page|xops|page-gse|aaes|tld-wollard|anderson-airmotive|tracteasy'], methods: ['GET'])]
    public function linkedInBanners(string $entity)
    {
        return $this->render(\sprintf('/communication/%s/linkedin_banners.html.twig', $entity), ['entity' => $entity]);
    }
}

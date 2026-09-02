<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(defaults: ['alvest_module' => 'USER', 'breadcrumb_label' => 'menu.mentoring.title', 'moduleDomain' => 'directory_mentoring'])]
class MentoringController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, CsvStreamedResponseFactory::class]);
    }

    #[Route(path: '/directory/mentoring', name: 'directory_mentoring_home', methods: 'GET')]
    #[Template('directory/mentoring/list.html.twig')]
    #[IsGranted('FEATURE_PEOPLE_MENTORING_VIEW')]
    public function list()
    {
        $mentees = $this->container->get(Client::class)->findBy(
            'people',
            [
                'exists' => ['mentor' => true],
                'hidden' => 0,
                'disabled' => 0,
            ],
            ['lastname' => 'asc']);

        $mentorEmails = [];

        foreach ($mentees as $mentee) {
            if (\is_array($mentee) && isset($mentee['mentor']) && \is_array($mentee['mentor'])) {
                $mentorEmails[] = $mentee['mentor']['email'] ?? null;
            }
        }

        $mentorEmails = array_filter($mentorEmails);

        return [
            'mentees' => $mentees,
            'mentors' => array_unique($mentorEmails),
        ];
    }

    #[Route(path: '/directory/mentoring/download', name: 'directory_mentoring_download', methods: 'GET')]
    #[IsGranted('FEATURE_DOWNLOAD_DIRECTORY')]
    public function downloadDirectory()
    {
        $parameters = [
            'exists' => ['mentor' => true],
            'hidden' => 0,
            'disabled' => 0,
            'normalization_groups_override' => ['expose_legacy', 'people:export'],
        ];

        return $this->container->get(CsvStreamedResponseFactory::class)->create('people/download_directory', $parameters, 'mentors.csv');
    }
}

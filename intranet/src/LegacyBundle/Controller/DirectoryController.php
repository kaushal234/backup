<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Migration HACK.
 *
 * We catch the legacy url and forward to our controller only for migrated pages.
 * Otherwise, throw NotFoundException to fallback on the legacy.
 */
class DirectoryController extends AbstractController
{
    private array $translations = [
        'regions' => [
            'controller' => 'Region',
            'resource' => 'regions',
        ],
        'locations' => [
            'controller' => 'Location',
            'resource' => 'locations',
        ],
        'bu' => [
            'controller' => 'BusinessUnit',
            'resource' => 'business_units',
        ],
        'departments' => [
            'controller' => 'Department',
            'resource' => 'departments',
        ],
        'functions' => [
            'controller' => 'Position',
            'resource' => 'positions',
        ],
        'people' => [
            'controller' => 'People',
            'resource' => 'people',
        ],
    ];

    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/directory/index.php', name: 'legacy_directory_index', defaults: ['alvest_module' => 'DIR'])]
    public function router(Request $request)
    {
        if ($request->request->has('m')) {
            throw $this->createNotFoundException();
        }

        /** @var array $m */
        $m = $request->query->all()['m'] ?? [];

        /*
         * DIRECTORY SECTION
         */
        if ([] === $m) {
            return $this->redirectToRoute('directory_index');
        }

        if ('people' === $m[0]) {
            $request->attributes->set('alvest_module', 'USER');
        }

        /*
         * PHOTO SECTION
         */
        if (
            (isset($m[0]) && 'outPhoto' === $m[0] && $request->query->has('id'))
            || ($m === ['people', 'view', 'photo', 'out'] && $request->query->has('id'))
        ) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            if (!empty($people['photo'])) {
                return $this->redirectToRoute('upload_access', ['filePath' => $people['photo']['filePath'], 'width' => $request->query->get('width')]);
            }

            return $this->redirectToRoute('directory_people_show', ['id' => Iri::id($people)]);
        }

        if (\array_slice($m, 0, 3) === ['people', 'view', 'photo'] && isset($m[3]) && \in_array($m[3], ['delete', 'edit'], true) && $request->query->has('id')) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('directory_people_edit', ['id' => Iri::id($people)]);
        }

        if (\array_slice($m, 0, 3) === ['people', 'view', 'log'] && $request->query->has('id')) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('directory_people_show', ['id' => Iri::id($people)]);
        }

        if ($m === ['jobs']) {
            return $this->redirectToRoute('human_resources_job_home');
        }

        /*
         * PEOPLE SECTION
         */
        if ($m === ['people', 'form', 'byNumber']) {
            return $this->redirectToRoute('directory_people_search_id');
        }

        if ($m === ['people', 'view', 'subordinates'] && $request->query->has('id')) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('directory_people_team_members', ['id' => Iri::id($people)]);
        }

        if ($m === ['people', 'view', 'groups']) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('directory_people_acls', ['id' => Iri::id($people)]);
        }

        if ($m === ['people', 'view', 'groups', 'add']) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('directory_people_add_acl', ['id' => Iri::id($people)]);
        }

        if ($m === ['people', 'view', 'groups', 'import']) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('directory_people_import_acl', ['id' => Iri::id($people)]);
        }

        if (
            ($m === ['people', 'listing', 'search'])
            || ($m === ['people', 'form', 'search'])
        ) {
            return $this->redirectToRoute('directory_people_search');
        }

        if ($m === ['people', 'view', 'outVcard'] || $m === ['entry', 'view', 'outVcard']) {
            try {
                $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('id')]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('directory_people_vcard', ['id' => Iri::id($people)]);
        }

        if ($m === ['people', 'download']) {
            return $this->redirectToRoute('directory_people_download');
        }

        /*
         * TRANSLATION SECTION
         */
        if (!empty($m[0]) && \array_key_exists($m[0], $this->translations)) {
            $class = 'AppBundle\\Controller\\Directory\\'.$this->translations[$m[0]]['controller'].'Controller';
            if (class_exists($class)) {
                if (empty($m[1])) {
                    if (method_exists($class, 'list')) {
                        return $this->forward("$class::list");
                    }
                } else {
                    if ('form' === $m[1] && 'add' === $m[2]) {
                        $m[1] = 'add';
                        unset($m[2]);
                    }

                    switch ($m[1]) {
                        case 'add':
                            if (empty($m[2]) && method_exists($class, 'add')) {
                                return $this->forward("$class::add");
                            }
                            break;
                        case 'view':
                            try {
                                $entity = $this->client->findOneBy(
                                    $this->translations[$m[0]]['resource'],
                                    ['legacyId' => $request->query->get('id')]
                                );
                            } catch (\RangeException $e) {
                                throw new LegacyResourceNotFoundException();
                            }

                            if (empty($m[3])) {
                                if (empty($m[2])) {
                                    $m[2] = 'show';
                                }

                                switch ($m[2]) {
                                    case 'show':
                                        if (method_exists($class, 'show')) {
                                            return $this->forward(
                                                "$class::show",
                                                [
                                                    'id' => Iri::id($entity),
                                                ]
                                            );
                                        }
                                        break;
                                    case 'edit':
                                        if (method_exists($class, 'edit')) {
                                            return $this->forward(
                                                "$class::edit",
                                                [
                                                    'id' => Iri::id($entity),
                                                ]
                                            );
                                        }
                                        break;
                                    case 'duplicate':
                                        if (method_exists($class, 'duplicate')) {
                                            return $this->forward(
                                                "$class::duplicate",
                                                [
                                                    'id' => Iri::id($entity),
                                                ]
                                            );
                                        }
                                        break;
                                    case 'delete':
                                        if (method_exists($class, 'delete')) {
                                            return $this->forward(
                                                "$class::delete",
                                                [
                                                    'id' => Iri::id($entity),
                                                ]
                                            );
                                        }
                                        break;
                                }
                            }
                            break;
                    }
                }
            }
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/directory/{name}/{resourceName}_admin.php', name: 'legacy_directory_admin_index', methods: 'GET|POST')]
    public function adminRouter(Request $request, $resourceName): Response
    {
        $mode = $request->query->get('mode');

        if (\array_key_exists($resourceName, $this->translations)) {
            $class = 'AppBundle\\Controller\Directory\\'.$this->translations[$resourceName]['controller'].'Controller';
            if (class_exists($class)) {
                try {
                    $entity = $this->client->findOneBy(
                        $this->translations[$resourceName]['resource'],
                        ['legacyId' => $request->query->get('id')]
                    );
                } catch (\RangeException $e) {
                    throw new LegacyResourceNotFoundException();
                }

                switch ($mode) {
                    case 'record_view':
                        if (method_exists($class, 'show')) {
                            return $this->forward(
                                "$class::show",
                                [
                                    'id' => Iri::id($entity),
                                ]
                            );
                        }
                        break;
                    case 'form_edit':
                        if (method_exists($class, 'edit')) {
                            return $this->forward(
                                "$class::edit",
                                [
                                    'id' => Iri::id($entity),
                                ]
                            );
                        }
                        break;
                }
            }
        }

        throw $this->createNotFoundException();
    }
}

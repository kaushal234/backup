<?php

declare(strict_types=1);

namespace AppBundle\Manager;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use ApiBundle\Security\Core\Authentication\JwtToken;
use ApiBundle\Security\Core\Authentication\UserProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class UserManager
{
    public function __construct(
        private Client $client,
        private UserProvider $userProvider,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array $people
     *
     * @return array
     */
    public function addPeople($people)
    {
        unset($people['@id'], $people['photo'], $people['legacyId'], $people['hidden'], $people['disabled']);

        $this->checkPassword($people);

        return $this->client->save('people', $people);
    }

    /**
     * Update a people.
     *
     * @param array $people
     *
     * @return array
     */
    public function updatePeople($people)
    {
        // Remove phones id during the time to figure what's wrong with double write
        // so they can be considered as new. They are still logged properly
        if (!empty($people['phones'])) {
            $phones = [];
            foreach ($people['phones'] as $phone) {
                $p = $phone;
                unset($p['@id']);
                $phones[] = $p;
            }
            $people['phones'] = $phones;
        }
        if (!isset($people['@id']) || !isset($people['@type']) || 'People' !== $people['@type']) {
            throw new \LogicException('Given array is not a valid People');
        }

        $this->checkPassword($people);

        return $this->client->save('people', $people);
    }

    /**
     * @param array $people
     *
     * @return array
     */
    public function updateAcls($people, array $acls)
    {
        $peopleId = Iri::id($people['@id']);

        return $this->client->request('users', $peopleId, 'import_acl', 'POST', [
            'json' => $acls,
        ]);
    }

    /**
     * @param array $people
     */
    public function setPhoto($people, UploadedFile $photo)
    {
        $peopleId = Iri::id($people['@id']);

        $this->client->request('people', $peopleId, 'photo', 'POST', [
            'multipart' => [
                [
                    'name' => 'photo',
                    'contents' => fopen($photo->getPathname(), 'r'),
                ],
            ],
        ]);
    }

    public function reloadUser(string $username, string $plainPassword): void
    {
        $response = $this->client->post('token', [
            'form_params' => [
                'username' => $username,
                'password' => $plainPassword,
                'portal' => 'intranet',
            ],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ]);

        $newTokenString = $response['token'] ?? null;

        if (!$newTokenString) {
            throw new \RuntimeException('Unable to retrieve a new JWT token after changing password.');
        }

        $newUser = $this->userProvider->loadUserByIdentifier($newTokenString);

        $jwtToken = new JwtToken($newUser, 'main', $newUser->getRoles());

        $this->tokenStorage->setToken($jwtToken);

        $session = $this->requestStack->getSession();
        $session->set('_security_main', serialize($jwtToken));
    }

    /**
     * If the password is empty, remove it from the fields list.
     *
     * @param array $people
     */
    private function checkPassword($people)
    {
        if (empty($people['password'])) {
            unset($people['password']);
        }
    }
}

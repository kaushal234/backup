<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Acl;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Entity\Group;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Sales\ExtranetUserFavorite;
use App\Entity\Sales\ExtranetUserGroup;
use App\Entity\Sales\ExtranetUserProfile;
use App\Entity\User;
use App\Repository\AclRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\GenderHelper;
use LegacyBundle\Command\Helper\PhoneHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(name: 'legacy:import:sales:extranet_users')]
class ImportSalesExtranetUsersCommand extends Command
{
    private array $logs = [];

    private readonly Connection $legacyConnection;
    private readonly GenderHelper $genderHelper;
    private readonly EntityManagerInterface $em;
    private readonly ValidatorInterface $validator;
    private readonly SynchronizationVoter $synchronizationVoter;
    private readonly ActivityLogVoter $activityLogVoter;
    private readonly SanitationHelper $sanitationHelper;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly PhoneHelper $phoneHelper;
    private readonly PasswordHasherFactoryInterface $passwordHasherFactory;

    public function __construct(
        Connection $legacyConnection,
        GenderHelper $genderHelper,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SynchronizationVoter $synchronizationVoter,
        ActivityLogVoter $activityLogVoter,
        SanitationHelper $sanitationHelper,
        EntityCacheHelperFactory $cacheFactory,
        PhoneHelper $phoneHelper,
        PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
        parent::__construct();
        $this
            ->setDescription('Import TLD legacy extranet users, extranet user roles and extanet user favorites')
            ->addOption('logs', 'l', InputArgument::OPTIONAL, 'If passed as an option, will write logs to a csv file', false);

        $this->legacyConnection = $legacyConnection;
        $this->genderHelper = $genderHelper;
        $this->em = $em;
        $this->validator = $validator;
        $this->synchronizationVoter = $synchronizationVoter;
        $this->activityLogVoter = $activityLogVoter;
        $this->sanitationHelper = $sanitationHelper;
        $this->cacheFactory = $cacheFactory;
        $this->phoneHelper = $phoneHelper;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extranetUserGroupCache = $this->cacheFactory->createEntityCache(ExtranetUserGroup::class, 'name');
        $erpCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');
        $countryCache = $this->cacheFactory->createEntityCache(Country::class, 'legacyId');
        $crtCache = $this->cacheFactory->createEntityCache(CustomerRelationshipTeam::class, 'legacyId');
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');
        $groupCache = $this->cacheFactory->createEntityCache(Group::class, 'name');

        /** @var LegacyPasswordHasherInterface $passwordHasher */
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);
        /** @var AclRepository $aclRepo */
        $aclRepo = $this->em->getRepository(Acl::class);

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->em->getRepository(People::class);
        $qb = $peopleRepository->createQueryBuilder('p');
        $qb
            ->select('LOWER(p.username) AS username');

        $emails = array_reduce($qb->getQuery()->getScalarResult(), static fn ($memo, $data) => array_merge($memo, array_values($data)), []);

        $this->synchronizationVoter->disable();
        $this->activityLogVoter->disable();
        // Import extranet user profile
        $sql = <<<'SQL'
            SELECT eu.*, c.id AS customer_id, country.id AS country_id
            FROM extranet_users eu
              LEFT JOIN customers c
                ON eu.customer_name = c.customer_name
              LEFT JOIN countries country
                ON eu.country = country.name
            ORDER BY eu.last DESC
            SQL;
        $results = $this->legacyConnection->fetchAllAssociative($sql);

        $extranetUsersRoles = $this->getExtranetUserAcls();
        $extranetUsersFavorites = $this->getExtranetUserFavorites();

        $pb = new ProgressBar($output, \count($results));

        $extranetUserEmails = [];
        $extranetUserUsernames = [];
        $extranetUserAclsDictionary = [];
        $i = 0;
        foreach ($results as $data) {
            $pb->advance();

            ++$i;
            if ($i > 1 && 0 === $i % 1_000) {
                $this->em->flush();
            }

            $data['userid'] ??= '';
            $data['email'] ??= '';

            $userid = str_replace(["'", '?', '>', '<', ':', '\\', ' '], '', mb_strtolower(mb_trim((string) $data['userid'])));
            $email = str_replace(["'", '?', '>', '<', ':', '\\', ' '], '', mb_strtolower(mb_trim((string) $data['email'])));

            $emailConstraint = new Email(['mode' => 'strict']);

            $useridErrors = $this->validator->validate($userid, $emailConstraint);
            $emailErrors = $this->validator->validate($email, $emailConstraint);

            if ('' === $userid || $useridErrors->count() > 0 || '&' === $userid[0]) {
                if (0 === $emailErrors->count()) {
                    $userid = $email;
                } else {
                    $this->archiveXU($data['id']);
                    $this->addLog('No email nor username', $data['id'] ?? '', $data['lastname'] ?? '', $data['firstname'] ?? '', $data['customer_name'] ?? '', $data['erp'] ?? '');
                    continue;
                }
            }

            if (('' === $email) || ($emailErrors->count() > 0)) {
                $email = $userid;
            }

            if (\in_array($email, $extranetUserEmails, true) || \in_array($email, $extranetUserUsernames, true) || \in_array($userid, $extranetUserUsernames, true)) {
                $this->archiveXU($data['id']);
                $this->addLog('Email or username already exist for other Extranet User', $data['id'] ?? '', $data['lastname'] ?? '', $data['firstname'] ?? '', $data['customer_name'] ?? '', $data['erp'] ?? '');
                continue;
            }

            $extranetUserEmails[] = $email;
            $extranetUserUsernames[] = $userid;

            if (\in_array($userid, $emails, true) || \in_array($email, $emails, true)) {
                $qb = $peopleRepository->createQueryBuilder('u');
                $qb
                    ->where('LOWER(u.username) = :username')
                    ->setParameter('username', \in_array($userid, $emails, true) ? $userid : $email);

                /** @var People $people */
                $people = $qb->getQuery()->getSingleResult();

                if (null === $people->getBusinessUnit()) {
                    $this->addLog('User already existing but without BU', $data['id'] ?? '', $data['lastname'] ?? '', $data['firstname'] ?? '', $data['customer_name'] ?? '', $data['erp'] ?? '');
                    continue;
                }

                $peopleLocation = $people->getBusinessUnit()->getLocation();

                if (!$aclRepo->aclExists($people, 'ACL_XU_SHADOW', $peopleLocation)) {
                    /** @var Group $group */
                    $group = $groupCache->fetch('ACL_XU_SHADOW');
                    $acl = (new Acl())
                        ->setGroup($group)
                        ->setUser($people)
                        ->setLocation($peopleLocation);

                    $this->synchronizationVoter->enable();
                    $this->activityLogVoter->enable();

                    $this->em->persist($acl);

                    $this->synchronizationVoter->disable();
                    $this->activityLogVoter->disable();
                }

                continue;
            }

            $extranetUserProfile = (new ExtranetUserProfile())->setLegacyId((int) $data['id']);
            $extranetUserProfile->erpLocation = $erpCache->fetch($data['erp']);
            $extranetUserProfile->department = $data['department'];
            $extranetUserProfile->counter = (int) $data['counter'];
            $extranetUserProfile->division = $data['division'];
            $extranetUserProfile->type = $data['type'];
            $extranetUserProfile->jobTitle = $data['title'];
            $extranetUserProfile->language = $data['lang'];
            $extranetUserProfile->legacyAddress = null === $data['address'] ? '' : $data['address'];
            $extranetUserProfile->legacyShippingAddress = null === $data['shipping_address'] ? '' : $data['shipping_address'];
            $extranetUserProfile->country = $countryCache->fetch($data['country_id']);
            $extranetUserProfile->customerCarrierName = $data['cust_carrier_name'];
            $extranetUserProfile->companyName = $data['company_name'];
            $extranetUserProfile->note = $data['note'];
            $extranetUserProfile->shippingAccountNumber = $data['ship_acct_num'];
            $extranetUserProfile->requestorNumber = $data['requestor_num'];
            $extranetUserProfile->employeeNumber = $data['employe_num'];
            $extranetUserProfile->archived = '1' === $data['archived'];
            $extranetUserProfile->customer = $customerCache->fetch($data['customer_id']);
            $extranetUserProfile->sequenceIds = '0' === $data['seqid'] ? [$data['seqid']] : null;

            $regions = [];
            if (null !== ($country = $extranetUserProfile->country)) {
                $regions[] = $country->getIsoCode2();
            }

            $location = $extranetUserProfile->erpLocation;
            if ($location && $location->getAddress()->getCountry()) {
                $regions[] = $location->getAddress()->getCountry();
            }

            $this->em->persist($extranetUserProfile);

            $extranetUser = new ExtranetUser();
            $extranetUser
                ->setDisabled('N' === $data['enable'])
                ->setExtranetUserProfile($extranetUserProfile)
                ->setLegacyId((int) $data['id'])
                ->setUsername($userid)
                ->setLastname(null === $data['lastname'] ? '' : $this->sanitationHelper->parse($data['lastname']))
                ->setFirstname(null === $data['firstname'] ? '' : $this->sanitationHelper->parse($data['firstname']))
                ->setEmail($email)
                ->setHidden('1' === $data['hidden'])
                ->setSalt(bin2hex(random_bytes(32)))
                ->setLastLogin(null === $data['last'] ? null : new \DateTime($data['last']))
                ->setGender($this->genderHelper->getGenderFromProperty($data['salutation']))
            ;

            foreach (['phone' => Phone::TYPE_RECEPTION, 'direct_phone' => Phone::TYPE_PHONE, 'home_phone' => Phone::TYPE_HOME, 'mobile' => Phone::TYPE_MOBILE, 'fax' => Phone::TYPE_FAX] as $column => $type) {
                if (null === $data[$column]) {
                    continue;
                }
                $phone = $this->phoneHelper->parsePhone($data[$column], array_unique($regions));
                if (!$phone) {
                    continue;
                }

                $extranetUser->addPhone((new Phone())->setType($type)->setNumber($phone));
            }

            $extranetUser->setEncodedPassword($passwordHasher->hash($data['password'], $extranetUser->getSalt()));
            $this->em->persist($extranetUser);

            $xuRoles = $extranetUsersRoles[$data['id']] ?? [];
            foreach ($xuRoles as $xuRole) {
                if (null === $crtCache->fetch($xuRole['crt_id'])) {
                    continue;
                }

                /** @var CustomerRelationshipTeam $crt */
                $crt = $crtCache->fetch($xuRole['crt_id']);

                /** @var ExtranetUserGroup $group */
                $group = $extranetUserGroupCache->fetch($xuRole['role']);

                $uniqueKey = \sprintf('%s-%s-%s', $extranetUser->getLegacyId(), $crt->getId(), $group->getId());

                if (\in_array($uniqueKey, $extranetUserAclsDictionary, true)) {
                    continue;
                }

                $extranetUserAcl = new ExtranetUserAcl();
                $extranetUserAcl
                    ->setLegacyId((int) $xuRole['id'])
                    ->setCDel($xuRole['cdel'])
                    ->setCrt($crt)
                    ->setExtranetUserGroup($group)
                    ->setExtranetUser($extranetUser);
                $this->em->persist($extranetUserAcl);

                $extranetUserAclsDictionary[] = $uniqueKey;
            }

            $xuFavorites = $extranetUsersFavorites[$data['id']] ?? [];
            foreach ($xuFavorites as $xuFavorite) {
                $extranetUserFavorite = new ExtranetUserFavorite();
                $extranetUserFavorite
                    ->setLegacyId((int) $xuFavorite['id'])
                    ->setExtranetUser($extranetUser)
                    ->setAeroUsername($xuFavorite['fav_user'])
                    ->setPartNumber($xuFavorite['fav_pn'])
                    ->setDescription($xuFavorite['fav_dsca']);
                $this->em->persist($extranetUserFavorite);
            }
        }

        $this->em->flush();
        $pb->finish();

        if (false !== $input->getOption('logs')) {
            $this->printLogs();
        }

        $this->synchronizationVoter->enable();
        $this->activityLogVoter->enable();

        return 0;
    }

    private function getExtranetUserAcls()
    {
        // Import Extranet users roles
        $sql = <<<'SQL'
            SELECT id, parent_id, crt_id, cdel, role
            FROM extranet_users_roles
            SQL;
        $results = $this->legacyConnection->fetchAllAssociative($sql);

        $roles = [];

        foreach ($results as $role) {
            if (!\array_key_exists($role['parent_id'], $roles)) {
                $roles[$role['parent_id']] = [];
            }

            $roles[$role['parent_id']][] = $role;
        }

        return $roles;
    }

    private function getExtranetUserFavorites()
    {
        // Import Extranet Users Favorites
        $sql = <<<'SQL'
            SELECT id, parent_id, fav_user, fav_pn, fav_dsca
            FROM extranet_users_fav
            SQL;
        $results = $this->legacyConnection->fetchAllAssociative($sql);

        $favorites = [];

        foreach ($results as $favorite) {
            if (!\array_key_exists($favorite['parent_id'], $favorites)) {
                $favorites[$favorite['parent_id']] = [];
            }

            $favorites[$favorite['parent_id']][] = $favorite;
        }

        return $favorites;
    }

    private function addLog(
        string $reason,
        string $id,
        string $lastname,
        string $firstname,
        string $customer,
        string $location
    ) {
        $this->logs[] = implode(',', [$reason, $id, $lastname, $firstname, $customer, $location]);
        sort($this->logs);
    }

    private function printLogs()
    {
        $logs = implode("\n", $this->logs);

        $logs = "Reason,ID,Lastname,Firstname,Customer\n".$logs;

        file_put_contents('xulogs.csv', $logs);
    }

    private function archiveXU($id)
    {
        $sql = <<<'SQL'
            UPDATE extranet_users SET hidden=1, archived='1', enable='N', note='NOT IMPORTED WITH THE SYMFONY MIGRATION' WHERE id = :id
            SQL;
        $this->legacyConnection->executeStatement($sql, ['id' => $id]);
    }
}

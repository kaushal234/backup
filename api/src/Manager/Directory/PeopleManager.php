<?php

declare(strict_types=1);

namespace App\Manager\Directory;

use App\Entity\Acl;
use App\Entity\Directory\Location;
use App\Entity\Directory\Network;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Notifier\Tasks\LegacyTaskNotifier;
use App\Repository\Directory\PeopleRepository;
use Doctrine\Common\Collections\Collection;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;

class PeopleManager
{
    /** @var string */
    public const USER_JOINED = 'user_joined';

    /** @var string */
    public const USER_UPDATED = 'user_updated';

    /** @var string */
    public const USER_SUSPENDED = 'user_suspended';

    private const MAX_EMAIL_GENERATION_ATTEMPTS = 50;

    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly TaskManager $taskManager,
        private readonly LegacyTaskNotifier $notifier
    ) {
    }

    public function hasPublicPicture(People $people): bool
    {
        if ($people->isHidden()) {
            return false;
        }

        if (\in_array($people->getUserIdentifier(), ['acbregeron@tld-group.com', 'sylvie.zemmes@tld-group.com', 'mark.garlasco@tld-america.com'], true)) {
            return true;
        }

        if (null !== $position = $people->getPosition()) {
            $label = null;
            if (null !== $level = $position->getLevel()) {
                $label = $level->getLabel();
            }
            $code = $position->getCode();

            if (\in_array($label, ['ALVEST STEERING COMMITTEE', 'EXECUTIVES'], true) || ('MANAGERS' === $label) || 'LGS' === $code) {
                return true;
            }

            if (\in_array($code, ['SPM', 'CSM'], true)
                && null !== ($businessUnit = $people->getBusinessUnit())
                && null !== ($network = $businessUnit->getLocation()->getNetwork())
                && Network::NETWORK_TLD === $network->getName()
            ) {
                return true;
            }
        }

        /** @var Collection $acls */
        $acls = $people->getAcls();

        $acls = $acls->filter(static function (Acl $acl): bool {
            if (null !== $acl->getLocation() && (null === ($network = $acl->getLocation()->getNetwork()) || Network::NETWORK_TLD !== $network->getName())) {
                return false;
            }

            return \in_array($acl->getGroup()->getName(), ['ROLE_ASM', 'GG_SALES_AGENTS'], true);
        });

        return !$acls->isEmpty();
    }

    public function generateUsernameAndEmail(People $user)
    {
        $email = $this->generateEmailAddress($user);
        $user->setUsername($email);
        $user->setEmail($email);
    }

    public static function hasGroup(People $people, string $groupName, ?Location $location = null): bool
    {
        return !$people->getAcls()->filter(
            static fn (Acl $acl) => $groupName === $acl->getGroup()->getName() && (null === $location || $acl->getLocation() === $location))->isEmpty();
    }

    public static function hasOneOfGroups(People $people, array $groups, ?Location $location = null): bool
    {
        return !$people->getAcls()->filter(static fn (Acl $acl) => null !== $acl->getGroup() && \in_array($acl->getGroup()->getName(), $groups, true) && (null === $location || $acl->getLocation() === $location))->isEmpty();
    }

    public static function isSalesRepresentativeOfCustomer(People $people, Customer $customer): bool
    {
        if (null !== ($mainSalesRepresentative = $customer->getMainSalesRepresentative()) && $mainSalesRepresentative->asm->getId() === $people->getId()) {
            return true;
        }

        foreach ($customer->getSecondarySalesRepresentatives() as $secondarySalesRepresentative) {
            if ($secondarySalesRepresentative->asm->getId() === $people->getId()) {
                return true;
            }
        }

        return false;
    }

    public static function isAsmOfCustomerOrParent(People $people, Customer $customer): bool
    {
        if (self::isSalesRepresentativeOfCustomer($people, $customer)) {
            return true;
        }

        $i = 1;

        while (null !== ($customer = $customer->getParentCustomer()) && $i <= 10) {
            if (self::isSalesRepresentativeOfCustomer($people, $customer)) {
                return true;
            }
            ++$i;
        }

        return false;
    }

    public function createTaskForMISUserAndNotify(People $people): void
    {
        $task = (new Task())
            ->setAssignee($people)
            ->setAssignor($people)
            ->setParentId($people->getLegacyId())
            ->setModule('USER')
            ->setLocation($people->getBusinessUnit()?->getLocation())
            ->setDescription('This yearly task requires you, as an MIS team member to :

                    1.Read :
                         - DMS7028, ALVEST Information Security Policy
                         - DMS5017, ALVEST Information Security Management System
                         - DMS6222, MIS Missions and Activities
                         - DMS5221, ALVEST Information System Security Policy
                    2.Understand the content of the 4 above listed DMSs
                    3.Identify your area of responsibility (in particular when person in charge are mentioned within the above DMSs).
                    4.Understand that non-conforming with the policies and principles described in those above DMSs can put the company in severe troubles.


By closing this task you will confirm having met the 4 above requirements.')
        ;

        try {
            $this->taskManager->insert($task);
        } catch (\Exception $exception) {
            throw new \LogicException('Task not created. Reason : '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->notifier->sendEmail($task);
    }

    private function generateEmailAddress(People $user)
    {
        $firstname = str_replace(' ', '-', mb_trim($user->getFirstname() ?? ''));
        if (null !== $user->getBusinessUnit() && 'SAGE PARTS' === $user->getBusinessUnit()->getName()) {
            $firstname = mb_substr($firstname, 0, 1);
        }
        $lastname = str_replace(' ', '', $user->getLastname() ?? '');
        $username = mb_strtolower($firstname.'.'.$lastname);
        $search = explode(',', 'ç,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u');
        $replace = explode(',', 'c,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u');
        $username = str_replace($search, $replace, $username);

        $domain = '@tld-gse.com';

        $businessUnit = $user->getBusinessUnit();
        if (null !== $businessUnit && !empty($businessUnit->getDomain())) {
            $domain = $businessUnit->getDomain();
        }

        return $this->getValidEmail($username, $domain);
    }

    private function getValidEmail(string $username, string $domain): string
    {
        $emailAddress = $username.$domain;
        $counter = 1;

        while (0 !== \count($this->peopleRepository->findBy(['email' => $emailAddress]))) {
            if ($counter > self::MAX_EMAIL_GENERATION_ATTEMPTS) {
                throw new \RuntimeException(\sprintf('Failed to generate a unique email address after %d attempts. Last attempted email: %s', self::MAX_EMAIL_GENERATION_ATTEMPTS, $emailAddress));
            }

            $emailAddress = $username.$counter.$domain;
            ++$counter;
        }

        return $emailAddress;
    }
}

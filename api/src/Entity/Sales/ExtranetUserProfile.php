<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Address;
use App\Entity\AddressWithCountry;
use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\UserProfileTrait;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ArrayToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('NOT_EXISTING')"),
        new Get(security: "is_granted('ACCESS_PEOPLE') or is_granted('FEATURE_EXTRANET_USER_EDIT', object)"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['user', 'extranet_user', 'extranet_user_detail', 'user_profile', 'user_profile:detail', 'address', 'people_public', 'location_public', 'expose_legacy', 'country_list', 'airport_list']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"
)]
#[App\Loggable(owner: 'extranetUser', ownerRelation: 'extranetUserProfile', showIri: true)]
#[\AllowDynamicProperties]
#[Legacy\Synchronize(table: 'extranet_users', forceUpdate: true)]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['user_profile_language']])]
class ExtranetUserProfile implements LegacyIdInterface
{
    use LegacyIdentifierTrait;
    use UserProfileTrait;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(choices: ['PUNCHOUT', 'CART', ''])]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    #[Legacy\Column(column: 'type')]
    public ?string $type = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    #[Groups(['user_profile:detail', 'user_profile:write'])]
    #[Legacy\Column(column: 'division')]
    public ?string $division = null;

    #[ORM\Embedded(class: '\App\Entity\AddressWithCountry')]
    #[Assert\Valid]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    #[Legacy\Column(column: 'shipping_address', options: ['embeddedFields' => ['shippingAddress.street1', 'shippingAddress.street2', 'shippingAddress.town', 'shippingAddress.postalCode', 'shippingAddress.city', 'shippingAddress.state', 'shippingAddress.country']])]
    public ?AddressWithCountry $shippingAddress = null;

    #[ORM\Column(type: 'text', length: 65000)]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    public string $legacyShippingAddress = '';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(name: 'customer', nullable: true)]
    #[Groups(['user_profile', 'extranet_user_full_write', 'extranet_user_profile_campaign'])]
    #[Transferable(manager: 'manager.customer')]
    #[Legacy\Column(column: 'customer_name', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public ?Customer $customer = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    #[Legacy\Column(column: 'cust_carrier_name')]
    public ?string $customerCarrierName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['user_profile:detail', 'user_profile:write'])]
    #[Legacy\Column(column: 'ship_acct_num')]
    public ?string $shippingAccountNumber = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['user_profile:detail', 'user_profile:write'])]
    #[Legacy\Column(column: 'requestor_num')]
    public ?string $requestorNumber = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['user_profile:detail', 'user_profile:write'])]
    #[Legacy\Column(column: 'employe_num')]
    public ?string $employeeNumber = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(name: 'erp_location', nullable: true)]
    #[Groups(['user_profile', 'extranet_user_full_write'])]
    #[ValidLocation(sso: true)]
    #[Legacy\Column(column: 'erp', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?Location $erpLocation = null;

    #[Assert\All([new Assert\Type(type: 'string')])]
    #[ORM\Column(type: 'simple_array', length: 255, nullable: true)]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    #[Legacy\Column(column: 'seqid', transformer: ArrayToString::class)]
    public array $sequenceIds = [];

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    #[Legacy\Column(column: 'archived')]
    public bool $archived = false;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Sales\ExtranetUser', mappedBy: 'extranetUserProfile')]
    public ?ExtranetUser $extranetUser = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    public ?Airport $airport = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['user_profile:write', 'user_profile:detail', 'extranet_user_is_verified_campaign'])]
    public bool $isVerified = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['user_profile:write', 'user_profile:detail'])]
    public bool $isGiftAccepted = true;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['user_profile:write', 'user_profile:detail'])]
    public ?string $giftRefusedReason = null;

    public function __construct()
    {
        $this->address = new Address();
        $this->shippingAddress = new AddressWithCountry();
    }

    /**
     * check https://github.com/symfony/symfony/issues/35660
     * and https://github.com/symfony/symfony/issues/35574.
     */
    public function __serialize()
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserProfile;
use App\Serializer\ContextBuilder\ExtranetUserContextBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class ExtranetUserContextBuilderTest extends TestCase
{
    use ProphecyTrait;

    /** @dataProvider initialContextProvider */
    public function testContextVariesBasedOnContext($request, $initialContext, $normalization, $expected)
    {
        $serializerContextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        if ($normalization) {
            $securityProphecy->getUser()->shouldNotBeCalled();
        } else {
            $securityProphecy->getUser()->willReturn(new People());
        }

        $serializerContextBuilderProphecy->createFromRequest($request, $normalization, null)->shouldBeCalledTimes(1)->willReturn($initialContext);

        $builder = new ExtranetUserContextBuilder($serializerContextBuilderProphecy->reveal(), $securityProphecy->reveal());

        $context = $builder->createFromRequest($request, $normalization);

        self::assertSame($expected, $context['groups'] ?? []);
    }

    public function initialContextProvider()
    {
        yield 'normalize extranet user without groups' => [new Request(), ['resource_class' => ExtranetUser::class], true, []];
        yield 'normalize extranet user with groups and empty data' => [new Request(), ['resource_class' => ExtranetUser::class, 'groups' => ['extranet_user']], true, ['extranet_user', 'extranet_user_fetch_eager']];
        $request = new Request();
        $request->attributes->set('data', 'Foo');
        yield 'normalize extranet user with groups and data' => [$request, ['resource_class' => ExtranetUser::class, 'groups' => ['extranet_user']], true, ['extranet_user']];
        yield 'denormalize extranet user' => [new Request(), ['resource_class' => ExtranetUser::class], false, ['extranet_user_full_write']];
        yield 'denormalize extranet user profile' => [new Request(), ['resource_class' => ExtranetUserProfile::class], false, ['extranet_user_full_write']];
    }
}

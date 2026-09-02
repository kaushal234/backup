<?php

declare(strict_types=1);

namespace Tests\AppBundle\Manager;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Manager\Sales\OrderManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class OrderManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testWithoutQuote()
    {
        $this->expectException(\Exception::class);
        $request = new Request();

        $clientProphecy = $this->prophesize(Client::class);
        $manager = new OrderManager($clientProphecy->reveal());
        $manager->buildOrder($request);
    }

    public function testOrderBuilderWithFailingClientRequests()
    {
        $headers = [
            'eqno' => 'foo',
            'bu' => '540',
            'cu_nama' => 'MANULOC',
            'cu_orno' => '',
            'cu_new' => '',
            'asm' => '2279',
        ];

        $sessionProphecy = $this->prophesize(Session::class);
        $sessionProphecy->has('_legacy_equote_data')->willReturn(true);
        $sessionProphecy->get('_legacy_equote_data')->willReturn($headers);
        $request = new Request();
        $request->setSession($sessionProphecy->reveal());
        $clientProphecy = $this->prophesize(Client::class);
        $clientProphecy->findOneBy(Argument::type('string'), Argument::type('array'))->willThrow(\RangeException::class)->shouldBeCalledTimes(3);

        $manager = new OrderManager($clientProphecy->reveal());

        $expected = [
            'equoteId' => 'foo',
            'newCustomer' => false,
            'customerPurchaseOrders' => [],
        ];

        self::assertSame($expected, $manager->buildOrder($request));
    }

    public function testOrderBuilderForLegacy()
    {
        $headers = [
            'eqno' => 'foo',
            'bu' => '540',
            'cu_nama' => 'air jordan',
            'cu_orno' => '1984',
            'cu_new' => 'Y',
            'asm' => '2279',
            'agnt_nama' => 'air pes',
        ];

        $sessionProphecy = $this->prophesize(Session::class);
        $sessionProphecy->has('_legacy_equote_data')->willReturn(true);
        $sessionProphecy->get('_legacy_equote_data')->willReturn($headers);
        $request = new Request();
        $request->setSession($sessionProphecy->reveal());
        $clientProphecy = $this->prophesize(Client::class);
        $clientProphecy->findOneBy('locations', ['erp' => 540, 'capability.sso' => true])->willReturn(new ApiData(['@id' => '/locations/1']));
        $clientProphecy->findOneBy('people', ['legacyId' => 2279, 'hidden' => 0, 'disabled' => 0, 'normalization_groups_override' => ['people_list']])->willReturn(new ApiData(['@id' => '/people/1']));
        $clientProphecy->findOneBy('sales/customers', ['q' => 'air jordan', 'normalization_groups_override' => ['customer_list']])->willReturn(new ApiData(['@id' => '/sales/customers/1']));
        $clientProphecy->findOneBy('sales/customers', ['q' => 'air pes', 'normalization_groups_override' => ['customer_list']])->willReturn(new ApiData(['@id' => '/sales/customers/2']));

        $manager = new OrderManager($clientProphecy->reveal());

        $expected = [
            'equoteId' => 'foo',
            'newCustomer' => true,
            'customerPurchaseOrders' => ['1984'],
            'sso' => ['@id' => '/locations/1'],
            'asm' => ['@id' => '/people/1'],
            'endUser' => ['@id' => '/sales/customers/1'],
            'salesAgent' => ['@id' => '/sales/customers/2'],
        ];

        self::assertSame($expected, $manager->buildOrder($request));
    }

    public function testOrderBuilder()
    {
        $xml = <<<'EOF'
        <ShowResponse>
            <DataArea>
                <Quotation>
                    <QuotationHeader>
                        <Equote>foo</Equote>
                        <SSO>540</SSO>
                        <CUNO>air jordan</CUNO>
                        <ASM>2279</ASM>
                        <Buyer>BYR777</Buyer>
                        <EndUser>EU888</EndUser>
                    </QuotationHeader>
                </Quotation>
            </DataArea>
        </ShowResponse>
EOF;
        $headers = ['xml' => $xml];

        $request = Request::create('/');
        $request->attributes->set('quoteNumber', 'foo');

        $clientProphecy = $this->prophesize(Client::class);
        $clientProphecy->findOneBy('sales/quotes', ['quoteNumber' => 'foo'])->willReturn(new ApiData($headers));
        $clientProphecy->findOneBy('locations', ['erp' => 540, 'capability.sso' => true])->willReturn(new ApiData(['@id' => '/locations/1']));
        $clientProphecy->findOneBy('people', ['id' => 2279, 'hidden' => 0, 'disabled' => 0, 'normalization_groups_override' => ['people_list']])->willReturn(new ApiData(['@id' => '/people/1']));
        $clientProphecy->findOneBy('sales/customers', ['inforLnBusinessPartnerCodes' => 'BYR777'])->willReturn(new ApiData(['@id' => '/sales/customers/777']));
        $clientProphecy->findOneBy('sales/customers', ['inforLnBusinessPartnerCodes' => 'EU888'])->willReturn(new ApiData(['@id' => '/sales/customers/888']));

        $manager = new OrderManager($clientProphecy->reveal());

        $expected = [
            'equoteId' => 'foo',
            'inforLnBusinessPartnerCode' => 'air jordan',
            'contact' => null,
            'baanOrderNumbers' => [],
            'customerPurchaseOrders' => [],
            'newCustomer' => null,
            'salesAgent' => null,
            'juridicalLocation' => null,
            'note' => null,
            'buyer' => ['@id' => '/sales/customers/777'],
            'endUser' => ['@id' => '/sales/customers/888'],
            'asm' => ['@id' => '/people/1'],
            'sso' => ['@id' => '/locations/1'],
        ];

        self::assertSame($expected, $manager->buildOrder($request));
    }
}

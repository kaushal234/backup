<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\IndexController;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\ClientInterface;
use App\Sdk\Downloader;
use App\Sdk\Resource\BusinessUnit;
use App\Sdk\Resource\Department;
use App\Sdk\Resource\EvendorsNews;
use App\Sdk\Resource\Region;
use App\Sdk\Resource\Representative;
use App\Sdk\Resource\Supplier;
use App\Sdk\Resource\SupplierRanking\Criteria;
use App\Sdk\Resource\SupplierRanking\Notation;
use App\Sdk\Resource\SupplierRanking\SupplierRanking;
use App\Security\Security;
use App\Security\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

/**
 * @group unit
 */
final class IndexControllerTest extends TestCase
{
    private Environment&MockObject $twig;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private TokenStorageInterface&MockObject $tokenStorage;
    private IndexController $controller;
    private QueryBusInterface $bus;
    private ClientInterface&MockObject $client;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->bus = $this->createMock(QueryBusInterface::class);
        $responder = new Responder($this->twig, $this->urlGenerator, $this->createMock(SerializerInterface::class), new RequestStack());
        $downloader = new Downloader(
            $this->createMock(ClientInterface::class),
            $responder,
            ''
        );
        $security = new Security($this->createMock(AuthorizationCheckerInterface::class), $this->tokenStorage);

        $this->controller = new IndexController($security, $responder, $this->bus, $downloader);
    }

    public function testNonAuthenticated(): void
    {
        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn(null);
        $this->urlGenerator->expects($this->once())->method('generate')->with('security:login', [])->willReturn('/login');

        $response = ($this->controller)();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }

    public function testAnonymouslyAuthenticated(): void
    {
        $token = new NullToken();

        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn($token);
        $this->urlGenerator->expects($this->once())->method('generate')->with('security:login', [])->willReturn('/login');

        $response = ($this->controller)();

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }

    public function testFullyAuthenticated(): void
    {
        $user = new User\User(1, 'Saif Eddin', 'Gmati', 'saif@les-tilleuls.coop', '123456789', address: new User\Address(
            firstLine: 'Foo',
            secondLine: 'Bar',
            city: 'Nabeul',
            state: 'Nabeul',
            country: 'Tunisia',
            zipCode: '8000'
        ));
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        $this->tokenStorage->expects($this->exactly(2))->method('getToken')->willReturn($token);
        $this->urlGenerator->expects($this->never())->method('generate');
        $this->bus->expects($this->exactly(3))->method('dispatch')->willReturnOnConsecutiveCalls(new Representative(
            iri: '/people/1',
            id: 1,
            firstname: 'firstname',
            lastname: 'lastname',
            email: 'firstname.lastname@mail.com',
            businessUnit: new BusinessUnit(
                iri: '/business-unit/1',
                name: 'BU',
                region: new Region(
                    iri: '/region/1',
                    name: 'TLD',
                )
            ),
            department: new Department(iri: '/department/1', name: 'MIS'),
            jobTitle: 'jobTitle',
            phones: [],
            address: new User\Address(firstLine: '', secondLine: '', city: '', state: '', country: '', zipCode: ''),
            photo: null,
        ), [new SupplierRanking(
            iri: '/purchasing/supplier_ranking/supplier_rankings/1',
            id: 1,
            supplier: new Supplier(
                iri: '/purchasing/supplier/1',
                name: 'Test supplier name',
                code: 'AAAAA'
            ),
            notations: [
                new Notation(
                    iri: '/purchasing/supplier_ranking/notations/1',
                    criteria: new Criteria(
                        iri: '/purchasing/supplier_ranking/criterias/1',
                        id: 1,
                        name: 'Criteria name',
                        public: true
                    ),
                    notation: 5
                ),
            ]
        )], [new EvendorsNews(
            iri: '/evendors_news/1',
            id: 1,
            content: 'news content',
            publishedAt: '2022-11-17T13:58:00-05:00',
            files: [],
        )]);
        $this->twig->expects($this->once())->method('render')->willReturnCallback(static function (string $template, array $context) use ($user): string {
            self::assertSame('index.html.twig', $template);
            self::assertArrayHasKey('user', $context);
            self::assertSame($user, $context['user']);
            self::assertArrayHasKey('supplierRankingCharts', $context);
            self::assertSame('Test supplier name', $context['supplierRankingCharts'][0]['supplierRanking']->supplier->name);

            return 'body';
        });

        $response = ($this->controller)();

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('body', $response->getContent());
    }
}

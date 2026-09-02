<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Form\Type\Security\PasswordForgotType;
use AppBundle\Form\Type\Security\PasswordResetType;
use AppBundle\Form\Type\Security\SsoLoginType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Security Controller.
 */
class SecurityController extends AbstractController
{
    private readonly Client $client;
    private readonly AuthenticationUtils $authenticationUtils;
    private readonly TranslatorInterface $translator;
    private readonly ViolationMapper $violationMapper;

    public function __construct(Client $client, AuthenticationUtils $authenticationUtils, TranslatorInterface $translator, ViolationMapper $violationMapper)
    {
        $this->client = $client;
        $this->authenticationUtils = $authenticationUtils;
        $this->translator = $translator;
        $this->violationMapper = $violationMapper;
    }

    #[Route(path: '/login', name: 'login', methods: 'GET|POST')]
    #[Template('security/login.html.twig')]
    public function login()
    {
        return [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ];
    }

    #[Route(path: '/login_check', name: 'login_check', methods: 'POST')]
    public function loginCheck(): never
    {
        throw new \Exception('Please configure your firewall.');
    }

    #[Route(path: '/logout', name: 'logout', methods: 'GET')]
    public function logout(): never
    {
        throw new \Exception('Please configure your firewall.');
    }

    /**
     * @return array|RedirectResponse
     */
    #[Route(path: '/forgot-password', name: 'security_password_forgot', methods: 'GET|POST')]
    #[Template('security/forgot_password.html.twig')]
    public function passwordForgot(Request $request)
    {
        $form = $this->createForm(
            PasswordForgotType::class,
            [],
            [
                'action' => $this->generateUrl('security_password_forgot'),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->request(
                    'reset_password',
                    null,
                    null,
                    'POST',
                    [
                        'json' => $form->getData() + ['portal' => 'intranet'],
                    ]
                );
            } catch (ClientException $e) {
                // Errors are silent for security reason (avoid to find logins)
            }

            $this->addFlash(
                'success',
                $this->translator->trans('security.forgot-success', [], 'security')
            );

            return $this->redirectToRoute('login');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/reset-password-confirmation/{id}/{token}', name: 'security_password_reset_confirmation', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('security/reset_password_confirmation.html.twig')]
    public function passwordResetConfirmation(Request $request, $id, $token)
    {
        if ($request->isMethod('GET')) {
            try {
                $this->client->request('reset_password_confirmation', $id, $token, 'GET');
            } catch (ClientException $e) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('security.reset_my-error', [], 'security')
                );

                return $this->redirectToRoute('security_password_forgot');
            }
        }

        $form = $this->createForm(
            PasswordResetType::class,
            [],
            [
                'action' => $this->generateUrl('security_password_reset_confirmation', ['id' => $id, 'token' => $token]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->request(
                    'reset_password_confirmation',
                    $id,
                    $token,
                    'POST',
                    ['json' => ['newPassword' => $form->get('newPassword')->getData()]],
                );

                $this->addFlash(
                    'success',
                    $this->translator->trans('security.success', [], 'security')
                );

                return $this->redirectToRoute('login');
            } catch (ClientException $e) {
                $statusCode = $e->getResponse()->getStatusCode();

                if (\in_array($statusCode, [404, 410], true)) {
                    $this->addFlash(
                        'error',
                        $this->translator->trans('security.reset_my-error', [], 'security')
                    );

                    return $this->redirectToRoute('security_password_forgot');
                }

                // 422: weak password — mapped onto the form via ViolationMapper, like other controllers
                $this->violationMapper->mapToForm($e, $form, ['clearPassword' => 'newPassword.first']);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/sso-login', name: 'sso_login')]
    #[Template('security/sso_login.html.twig')]
    public function ssoLogin(Request $request)
    {
        $email = $request->query->get('email');
        $form = $this->createForm(SsoLoginType::class, ['email' => $email]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (mb_stripos($form->get('email')->getData(), 'sageparts.com')) {
                return $this->redirectToRoute('sage_login');
            }

            return $this->redirectToRoute('alvest_login', ['email' => $form->get('email')->getData()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/alvest-login', name: 'alvest_login')]
    public function alvestSsoLogin(Request $request)
    {
        throw new \Exception('Please configure your firewall.');
    }

    #[Route(path: '/sage-login', name: 'sage_login')]
    public function sage(Request $request)
    {
        throw new \Exception('Please configure your firewall.');
    }
}

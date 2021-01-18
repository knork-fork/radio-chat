<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use App\Repository\UserRepository;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $userRepository;
    private $router;
    private $encoder;

    public function __construct(UserRepository $userRepository, RouterInterface $router, UserPasswordEncoderInterface $encoder)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->encoder = $encoder;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'login' && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        return [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // todo: call dummy hash if no user found
        return $this->userRepository->findOneBy(["username" => $credentials["username"]]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->encoder->isPasswordValid($user, $credentials["password"]);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate("index"));
    }

    protected function getLoginUrl()
    {
        return $this->router->generate("login");
    }
}

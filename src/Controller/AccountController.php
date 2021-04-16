<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UserRegister;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Security\LoginAuthenticator;

class AccountController extends AbstractController
{
    private $tokenStorage;
    private $router;
    private $userRegister;
    private $guardHandler;

    public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router, UserRegister $userRegister, GuardAuthenticatorHandler $guardHandler)
    {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->userRegister = $userRegister;
        $this->guardHandler = $guardHandler;
    }

    public function login()
    {
        // Rest of the logic is in src/Security/LoginAuthenticator.php

        if ($this->tokenStorage->getToken()->getUsername() === "anon.")
        {
            //die("Failed login");
            // temp
            return new RedirectResponse($this->router->generate("index"));
        }
        else
        {
            return new RedirectResponse($this->router->generate("index"));
        }
    }

    public function register(Request $request, LoginAuthenticator $loginInterface)
    {
        $formData = [
            "displayname" => $request->request->get("displayname"),
            "username" => $request->request->get("username"),
            "password" => $request->request->get("password"),
            "confirm_password" => $request->request->get("confirm-password")
        ];

        if ($this->tokenStorage->getToken()->getUsername() === "anon.")
        {
            $user = $this->userRegister->register($formData);

            if ($user != null)
            {
                // Success
                return $this->guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $loginInterface,
                    "main"
                );
            }
            else
            {
                // Fail
                //die("Failed register");
                // temp
                return new RedirectResponse($this->router->generate("index"));
            }
        }

        return new RedirectResponse($this->router->generate("index"));
    }

    public function signout()
    {
        // This should not be called
        throw new \Exception("Something went wrong.");
    }
}
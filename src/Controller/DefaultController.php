<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Repository\FriendRepository;

class DefaultController extends AbstractController
{
    private $tokenStorage;
    private $router;
    private $friendRepository;

    public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router, FriendRepository $friendRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->friendRepository = $friendRepository;
    }

    public function index()
    {
        $logged_in;

        if ($this->tokenStorage->getToken()->getUsername() !== "anon.")
        {
            // Logged in

            $user = $this->tokenStorage->getToken()->getUser();

            // Get friends
            $friendships = $this->friendRepository->getFriends($user);
            $friends = array();
            foreach ($friendships as $friend)
            {
                $friends[] = $friend->getUserTarget();
            }

            return $this->render("homepage/homepage.html.twig", [
                "page_title" => "RadioChat",
                "show_login" => false,
                "user" => $user,
                "friends" => $friends
            ]);
        }
        else
        {
            // Not logged in
            
            return $this->render("homepage/homepage.html.twig", [
                "page_title" => "RadioChat",
                "show_login" => true
            ]);
        }
    }
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Repository\FriendRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FriendService;
use Symfony\Component\HttpFoundation\JsonResponse;

class FriendController extends AbstractController
{
    private $tokenStorage;
    private $router;
    private $friendRepository;
    private $friendService;

    public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router, FriendRepository $friendRepository, FriendService $friendService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->friendRepository = $friendRepository;
        $this->friendService = $friendService;
    }

    public function addFriend(Request $request)
    {
        $unique_user_id = $request->request->get("user_unique_id");

        // Check if properly formatted
        $regex = "/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/";
        if (preg_match($regex, $unique_user_id)) 
        {
            $current_user = $this->tokenStorage->getToken()->getUser();
            if ($current_user != "anon.")
            {
                $friend = $this->friendService->addFriend($current_user, $unique_user_id);
                if ($friend != null)
                {
                    $ret = Array(
                        "id" => $friend->getId(),
                        "unique_user_id" => $friend->getUniqueUserId(),
                        "displayname" => $friend->getDisplayname()
                    );
                    return new JsonResponse($ret);
                }
            }
        }

        return new Response("fail", Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function pingFriend()
    {
        // This route is added for documentation purposes only, 'friend pinging' feature is implemented
        // entirely through websockets (see build/js/socket/common.js)

        return new Response("BAD_REQUEST", Response::HTTP_BAD_REQUEST);
    }
}
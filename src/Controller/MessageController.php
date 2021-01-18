<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\MessageCorruptor;
use App\Service\CorruptionCalculator;
use App\Repository\FriendRepository;

class MessageController extends AbstractController
{
    private $tokenStorage;
    private $messageCorruptor;
    private $corruptionCalculator;
    private $friendRepository;

    public function __construct(TokenStorageInterface $tokenStorage, MessageCorruptor $messageCorruptor, CorruptionCalculator $corruptionCalculator, FriendRepository $friendRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->messageCorruptor = $messageCorruptor;
        $this->corruptionCalculator = $corruptionCalculator;
        $this->friendRepository = $friendRepository;
    }

    public function getMessage(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        
        if ($user == "anon.")
            return new Response("Not logged in.", Response::HTTP_FORBIDDEN);

        $frequency = $request->request->get("frequency");
        $unique_hash = $request->request->get("unique_hash");

        if (!isset($frequency) || !isset($unique_hash))
            return new Response("Missing parameters.", Response::HTTP_BAD_REQUEST);

        // Connect to redis
        $redis = new \Redis();
        try 
        {
            $redis->connect("127.0.0.1", 6379);
        } 
        catch (\RedisException $e) 
        {
            return new Response("Failed connecting to redis.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Get message from redis by $unique_hash
        $redis_message = $redis->get($unique_hash);
        if (!$redis_message)
            return new Response("Invalid message.", Response::HTTP_BAD_REQUEST);
        $redis_arr = json_decode($redis_message, true);

        // Calculate corruption percentage based on difference between user's current frequency and message source frequency
        $percentages = Array(0, 1, 2, 5, 10, 15, 20, 30, 40);
        $corruption = $this->corruptionCalculator->calculateCorruption($frequency, $redis_arr["frequency"], $percentages);

        // Set message based on corruption level
        if ($corruption <= 40)
        {
            $redis_arr["corruptedSet"] = json_decode($redis_arr["corruptedSet"], true);
            $correct_message = $redis_arr["corruptedSet"][$corruption];
        }
        else
        {
            // Don't show message
            $correct_message = "";
        }

        // Set author name based on corruption level
        if ($corruption == 0)
        {
            // Author name always visible
            $author_name = $redis_arr["author_name"];
        }
        else if ($corruption <= 10)
        {
            // Show author name only if added as friend
            if ($this->friendRepository->isFriend($user->getId(), (int)$redis_arr["author_id"]))
                $author_name = $redis_arr["author_name"];
            else
                $author_name = "";
        }
        else
        {
            // Don't show author name
            $author_name = "";
        }

        $ret = Array(
            "message" => $correct_message,
            "corruption" => "$corruption%",
            "author_name" => $author_name
        );
        return new JsonResponse($ret);
    }

    public function sendMessage(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        
        if ($user == "anon.")
            return new Response("Not logged in.", Response::HTTP_FORBIDDEN);

        $frequency = $request->request->get("frequency");
        $message = $request->request->get("message");

        if (!isset($frequency) || !isset($message))
            return new Response("Missing parameters.", Response::HTTP_BAD_REQUEST);

        // Connect to redis
        $redis = new \Redis();
        try 
        {
            $redis->connect("127.0.0.1", 6379);
        } 
        catch (\RedisException $e) 
        {
            return new Response("Failed connecting to redis.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $message = $this->limitCharacters($message);

        // Check if message empty or contains only spaces
        if ($message == "" || strlen(trim($message)) == 0)
            return new Response("Invalid message.", Response::HTTP_BAD_REQUEST);

        // Generate a json with corrupted versions of the message
        $corruptedSet = json_encode($this->messageCorruptor->corruptMessage($message));
        
        // Message identifier as key for redis keystore
        $key = md5($corruptedSet);

        $messageObj = [
            "id" => $key,
            "author_id" => $user->getId(),
            "author_name" => $user->getDisplayname(),
            "frequency" => $frequency,
            "corruptedSet" => $corruptedSet
        ];
        $json = json_encode($messageObj);

        // Save message to redis with a TTL of 20 seconds - should be enough for all users to receive the message
        $redis->set($key, $json, 20);

        // Publish unique_hash to 'radio_main' channel to update all other users with this new message
        $redis->publish("socket-redis-down", '{"type":"publish", "data": {"channel":"radio_main", "event":"message", "data":{"unique_hash": "' . $key . '"}}}');

        return new JsonResponse($messageObj);
    }

    private function limitCharacters(string $message) : string
    {
        // Replace all characters not in list with space
        $list = "a-zA-Z0-9!#$%&()?.,:\- ";
        $message = preg_replace("/[^$list]/", " ", $message);

        // Trim duplicate spaces
        $message = preg_replace("/\s+/", " ", $message);

        return $message;
    }
}

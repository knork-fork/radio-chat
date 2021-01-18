<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Friend;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;

class FriendService
{
    private $em;
    private $userRepository;
    private $friendRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, FriendRepository $friendRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->friendRepository = $friendRepository;
    }

    public function addFriend(User $source_user, string $friend_unique_id) : ?User
    {

        // Check if adding yourself
        if ($source_user->getUniqueUserId() == $friend_unique_id)
            return null;

        // Check if user exists
        $target_user = $this->userRepository->findOneBy(
            array("unique_user_id" => $friend_unique_id)
        );
        if ($target_user == null)
            return null;

        // Check if friendship exists
        $friendship = $this->friendRepository->findOneBy(
            array(
                "userSource" => $source_user,
                "userTarget" => $target_user)
        );
        if ($friendship != null)
            return null;

        $friend = new Friend();

        $friend->setUserSource($source_user);
        $friend->setUserTarget($target_user);

        $this->em->persist($friend);
        $this->em->flush();

        return $target_user;
    }
}
<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserRepository;

class UserRegister
{
    private $em;
    private $passwordEncoder;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    public function register(Array $formData) : ?User
    {
        if ($this->validate($formData))
        {  
            $user = new User();
            $user->setUsername($formData["username"]);
            $user->setDisplayname($formData["displayname"]);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $formData["password"]
            ));
            $user->setUniqueUserId($this->generateUniqueId());

            //$user->setCreated(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();

            return $user;
        }

        return null;
    }

    public function validate(Array $formData) : bool
    {
        if (!$this->checkEmpty($formData))
        {
            return false;
        }

        if (!$this->checkDuplicate($formData["username"]))
        {
            return false;
        }

        return true;
    }

    public function checkEmpty(Array $formData) : bool
    {
        if ($formData["username"] == "")
            return false;

        if ($formData["displayname"] == "")
            return false;

        if ($formData["password"] == "")
            return false;

        if ($formData["password"] !== $formData["confirm_password"])
            return false;

        return true;
    }

    public function checkDuplicate(string $username) : bool
    {
        $entity = $this->userRepository->findOneBy([ 
            'username' => $username
        ]);
    
        return ($entity == null);
    }

    private function generateUniqueId() : string
    {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $uniqueId = "";

        // Generate random string in format XXXX-XXXX-XXXX-XXXX
        for ($i = 1; $i < 20; $i++) 
        {
            if ($i % 5 == 0)
                $uniqueId .= "-";
            else
                $uniqueId .= $characters[rand(0, $charactersLength - 1)];
        }

        return $uniqueId;
    }
}

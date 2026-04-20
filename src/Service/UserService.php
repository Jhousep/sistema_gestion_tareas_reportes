<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private Security $security
    ) {}

    public function getUsers(): array
    {
        $user = $this->security->getUser();

        // Si no hay usuario logueado
        if (!$user instanceof User) {
            return [];
        }

        // ADMIN → ve todos
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->userRepository->findAll();
        }

        // USER normal → solo su propio usuario
        return $this->userRepository->findBy([
            'id' => $user->getId()
        ]);
    }
}

<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserController
{
    public function __construct(
        private UserService $userService
    ) {}

    #[Route('', name: 'user_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $filters = $request->query->all();

        $users = $this->userService->getUsers($filters);

        $data = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ];
        }, $users);

        return new JsonResponse($data);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class UserMeController extends AbstractController
{
    #[Route('/me', name: 'user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        // obtenemos el usuario autenticado
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => 'Usuario no autenticado'
            ], 401);
        }

        return new JsonResponse([
            'roles' => $user->getRoles() // obtenemos los roles del usuario
        ]);
    }
}

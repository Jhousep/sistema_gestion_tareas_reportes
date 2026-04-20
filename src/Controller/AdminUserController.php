<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class AdminUserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    // ADMIN: GENERAR TOKEN
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users/{id}/reset-password', methods: ['POST'])]
    public function resetPassword(User $user): JsonResponse
    {
        $token = bin2hex(random_bytes(32));

        $user->setResetToken($token);
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Token generado correctamente',
            'token' => $token,
            'expiresAt' => $user->getResetTokenExpiresAt()->format('c')
        ]);
    }

    // USUARIO: CAMBIAR PASSWORD
    #[Route('/reset-password', methods: ['POST'])]
    public function changePassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $token = $data['token'] ?? null;
        $email = $data['email'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$email || !$newPassword) {
            return new JsonResponse(['error' => 'Datos incompletos'], 400);
        }

        $user = $this->userRepository->findOneBy([
            'email' => $email,
            'resetToken' => $token
        ]);

        if (!$user) {
            return new JsonResponse(['error' => 'Token o usuario inválido'], 400);
        }

        if ($user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            return new JsonResponse(['error' => 'Token expirado'], 400);
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $newPassword)
        );

        // limpiar token
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }
}

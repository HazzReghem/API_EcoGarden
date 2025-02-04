<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController{
    #[Route('/user', name: 'create_user', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['password'], $data['city'], $data['email'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setEmail($data['email']);
        $user->setCity($data['city']);
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        $token = $JWTManager->create($user);

        return new JsonResponse([
            'message' => 'Utilisateur créé',
            'token' => $token
        ], 201);
    }

    #[Route('/auth', name: 'user_auth', methods: ['POST'])]
    public function login(): void {}
}

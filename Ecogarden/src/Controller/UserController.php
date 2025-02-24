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
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    #[Route('/user/{id}', name: 'update_user', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        // Champs autorisés
        $allowedFields = ['username', 'email', 'city', 'password'];
        $invalidFields = array_diff(array_keys($data), $allowedFields);

        if (!empty($invalidFields)) {
            return new JsonResponse([
                'error' => 'Les champs suivants sont invalides : ' . implode(', ', $invalidFields)
            ], 400);
        }

        // Vérification des champs avant mise à jour
        if (isset($data['username'])) {
            if (!is_string($data['username']) || empty(trim($data['username']))) {
                return new JsonResponse(['error' => 'Le champ "username" est invalide'], 400);
            }
            $user->setUsername($data['username']);
        }

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['error' => 'Le champ "email" doit être une adresse email valide'], 400);
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['city'])) {
            if (!is_string($data['city']) || empty(trim($data['city']))) {
                return new JsonResponse(['error' => 'Le champ "city" est invalide'], 400);
            }
            $user->setCity($data['city']);
        }

        if (isset($data['password'])) {
            if (!is_string($data['password']) || strlen($data['password']) < 6) {
                return new JsonResponse(['error' => 'Le mot de passe doit contenir au moins 6 caractères'], 400);
            }
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur mis à jour'], 200);
    }

    #[Route('/user/{id}', name: 'delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé'], 200);
    }
}

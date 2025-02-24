<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Conseil;
use App\Repository\ConseilRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

use App\EventListener\ExceptionListener;

final class ConseilController extends AbstractController{

    #[Route('/conseil/{mois}', name: 'get_conseil_by_month', methods: ['GET'])]
    #[isGranted('ROLE_USER')]
    public function getConseilByMonth(int $mois, ConseilRepository $conseilRepository, SerializerInterface $serializer): JsonResponse
    {
        if ($mois < 1 || $mois > 12) {
            return $this->json(['message' => 'Mois invalide'], 400);
        }

        $conseils = $conseilRepository->findAll(); 

        $filteredConseils = array_filter($conseils, function ($conseil) use ($mois) {
            $months = $conseil->getMonths();
            if (is_string($months)) {
                $months = unserialize($months);
            }

            return in_array($mois, $months);
        });

        if (empty($filteredConseils)) {
            return $this->json(['message' => 'Aucun conseil trouvé pour ce mois'], 404);
        }

        $jsonData = $serializer->serialize($filteredConseils, 'json', ['groups' => 'conseil:read']);
        return new JsonResponse($jsonData, 200, [], true);
    }

    #[Route('/conseil', name: 'get_conseil_current_month', methods: ['GET'])]
    #[isGranted('ROLE_USER')]   
    public function getConseilCurrentMonth(ConseilRepository $conseilRepository, SerializerInterface $serializer): JsonResponse
    {
        $mois = (int) date('n'); // Récupère le mois actuel
        $conseil = $conseilRepository->findAll();

        // filtrer conseils pour mois actuel
        $filteredConseils = array_filter($conseil, function ($conseil) use ($mois) {
            $months = $conseil->getMonths();
            if (is_string($months)) {
                $months = unserialize($months);
            }

            return in_array($mois, $months);
        });

        if (empty($filteredConseils)) {
            return $this->json(['message' => 'Aucun conseil trouvé pour ce mois'], 404);
        }

        $jsonData = $serializer->serialize($filteredConseils, 'json', ['groups' => 'conseil:read']);

        return new JsonResponse($jsonData, 200, [], true);
    }

    #[Route('/conseil', name: 'create_conseil', methods: ['POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function createConseil(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['months'], $data['content'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $conseil = new Conseil();
        $conseil->setMonths($data['months']);
        $conseil->setContent($data['content']);
        $conseil->setCreatedAt(new \DateTime());

        $em->persist($conseil);
        $em->flush();

        return new JsonResponse(['message' => 'Conseil créé'], 201);
    }

    #[Route('/conseil/{id}', name: 'update_conseil', methods: ['PUT'])]
#[IsGranted('ROLE_ADMIN')]
public function updateConseil(int $id, Request $request, ConseilRepository $conseilRepository, EntityManagerInterface $em): JsonResponse
{
    $conseil = $conseilRepository->find($id);

    if (!$conseil) {
        return new JsonResponse(['error' => 'Conseil non trouvé'], 404);
    }

    $data = json_decode($request->getContent(), true);

    if (!is_array($data)) {
        return new JsonResponse(['error' => 'Données invalides'], 400);
    }

    // Liste des champs autorisés
    $allowedFields = ['months', 'content'];
    $invalidFields = array_diff(array_keys($data), $allowedFields);

    if (!empty($invalidFields)) {
        return new JsonResponse([
            'error' => 'Les champs suivants sont invalides : ' . implode(', ', $invalidFields)
        ], 400);
    }

    // Vérifier et mettre à jour uniquement si valide
    if (isset($data['months'])) {
        if (!is_array($data['months'])) {
            return new JsonResponse(['error' => 'Le champ "months" doit être un tableau'], 400);
        }
        $conseil->setMonths($data['months']);
    }

    if (isset($data['content'])) {
        if (!is_string($data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Le champ "content" est invalide'], 400);
        }
        $conseil->setContent($data['content']);
    }

    $conseil->setUpdatedAt(new \DateTime());
    $em->flush();

    return new JsonResponse(['message' => 'Conseil mis à jour'], 200);
}

    
    #[Route('/conseil/{id}', name: 'delete_conseil', methods: ['DELETE'])]
    #[isGranted('ROLE_ADMIN')]
    public function deleteConseil(int $id, ConseilRepository $conseilRepository, EntityManagerInterface $em): JsonResponse
    {
        $conseil = $conseilRepository->find($id);

        if (!$conseil) {
            return new JsonResponse(['message' => 'Conseil non trouvé'], 404);
        }

        $em->remove($conseil);
        $em->flush();

        return new JsonResponse(['message' => 'Conseil supprimé'], 200);
    }
}

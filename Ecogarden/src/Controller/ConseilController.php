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

final class ConseilController extends AbstractController{

    #[Route('/conseil/{mois}', name: 'get_conseil_by_month', methods: ['GET'])]
    #[isGranted('ROLE_USER')]
    public function getConseilByMonth(int $mois, ConseilRepository $conseilRepository): JsonResponse
    {
        if ($mois < 1 || $mois > 12) {
            return $this->json(['message' => 'Mois invalide'], 400);
        }

        $conseils = $conseilRepository->findBy(['mois' => $mois]);

        return new JsonResponse($conseils, 200, [], ['groups' => 'conseil:read']);
    }

    #[Route('/conseil', name: 'get_conseil_current_month', methods: ['GET'])]
    #[isGranted('ROLE_USER')]   
    public function getConseilCurrentMonth(ConseilRepository $conseilRepository): JsonResponse
    {
        $mois = (int) date('n');
        $conseil = $conseilRepository->findBy(['mois' => $mois]);

        return new JsonResponse($conseil, 200, [], ['groups' => 'conseil:read']);
    }

    #[Route('/conseil', name: 'create_conseil', methods: ['POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function createConseil(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['mois'], $data['conseil'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $conseil = new Conseil();
        $conseil->setMois($data['mois']);
        $conseil->setConseil($data['conseil']);

        $em->persist($conseil);
        $em->flush();

        return new JsonResponse(['message' => 'Conseil créé'], 201);
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

    #[Route('/conseil/{id}', name: 'update_conseil', methods: ['PUT'])]
    #[isGranted('ROLE_ADMIN')]
    public function updateConseil(int $id, Request $request, ConseilRepository $conseilRepository, EntityManagerInterface $em): JsonResponse
    {
        $conseil = $conseilRepository->find($id);

        if (!$conseil) {
            return new JsonResponse(['message' => 'Conseil non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['mois'], $data['conseil'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $conseil->setMois($data['mois']);
        $conseil->setConseil($data['conseil']);

        $em->flush();

        return new JsonResponse(['message' => 'Conseil mis à jour'], 200);
    }
}

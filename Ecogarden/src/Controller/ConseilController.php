<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Conseil;
use App\Repository\ConseilRepository;

final class ConseilController extends AbstractController{

    #[Route('/conseils', name: 'get_all_conseils', methods: ['GET'])]
    public function getAllConseil(EntityManagerInterface $entityManager): JsonResponse
    {
        $conseils = $entityManager->getRepository(Conseil::class)->findAll();
        // $conseils = $this->ConseilRepository->findAll();
        
        if(!$conseils){
            return $this->json(['message' => 'Aucun conseil trouvé'], 404);
        }

        // foreach ($conseils as $conseil) {
        //     dump($conseil);
        // }

        // Return all conseils in JSON format
        return $this->json($conseils, 200, [], ['groups' => 'conseil:read']);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Doctrine\ORM\EntityManagerInterface;

final class MeteoController extends AbstractController{

    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache, EntityManagerInterface $em)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->em = $em;
    }

    #[Route('/meteo/{city}', name: 'get_meteo_by_city', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getMeteoByCity(string $city): JsonResponse
    {
        return $this->fetchMeteo($city);
    }

    #[Route('/meteo', name: 'get_meteo_user', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getMeteoForUser(): JsonResponse
    {
        $user = $this->getUser();
        $city = $user->getCity(); 

        if (!$city) {
            return new JsonResponse(['error' => 'Aucune ville définie pour cet utilisateur.'], 400);
        }

        return $this->fetchMeteo($city);
    }

    private function fetchMeteo(string $city): JsonResponse
    {
        $apiKey = $_ENV['OPENWEATHER_API_KEY'];
        $cacheKey = 'weather_'.$city;

        try {
            // Vérifie si la météo est en cache
            $weatherData = $this->cache->get($cacheKey, function (ItemInterface $item) use ($city, $apiKey) {
                $item->expiresAfter(1800); // Cache pendant 30 minutes

                // Appel API OpenWeatherMap
                $response = $this->httpClient->request(
                    'GET',
                    "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric&lang=fr"
                );

                if ($response->getStatusCode() !== 200) {
                    throw new \Exception('Ville non trouvée');
                }

                return $response->toArray();
            });

            return new JsonResponse($weatherData);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Impossible de récupérer la météo pour cette ville.'], 404);
        }
    }
}

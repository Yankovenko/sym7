<?php

namespace App\Controller\Api;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;

#[Tag('Auth')]
class AuthController extends AbstractController
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        $user = new InMemoryUser('api_user', '', ['ROLE_USER']);
        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/validate', name: 'api_validate', methods: ['GET'])]
    public function validate(UserInterface $user): JsonResponse
    {
        return new JsonResponse([
            'message' => 'This is a protected endpoint',
            'user_id' => $user->getUserIdentifier(),
            'user_roles' => $user->getRoles(),
        ]);
    }
}
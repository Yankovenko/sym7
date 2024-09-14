<?php

namespace App\Controller\Api;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes\Tag;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;

#[Tag('Auth')]
class AuthController extends AbstractController
{
    public function __construct(readonly private JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the JWT token',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'token',
                    type: 'string'
                )
            ]
        ),
    )]
    public function login(): JsonResponse
    {
        $user = new InMemoryUser('api_user', '', ['ROLE_USER']);
        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/validate', name: 'api_validate', methods: ['GET'])]
    #[OA\Response(
        response: 401,
        description: 'Invalid JWT Token',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the validate information',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'user_name', type: 'string'),
                new OA\Property(property: 'user_roles', type: 'array', items: new OA\Items(type: 'string')),
            ]
        ),
    )]
    public function validate(UserInterface $user): JsonResponse
    {
        return new JsonResponse([
            'message' => 'This is a protected endpoint',
            'user_id' => $user->getUserIdentifier(),
            'user_roles' => $user->getRoles(),
        ]);
    }
}
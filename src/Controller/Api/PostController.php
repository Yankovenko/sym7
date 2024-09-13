<?php

namespace App\Controller\Api;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
#[Tag('Post')]
#[Route('/api')]
class PostController extends AbstractController
{

    #[Route('/posts', name: 'post_index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of posts',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Post::class, groups: ['read']))
        )
    )]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->json($posts);
    }

    #[Route('/posts/search', name: 'post_search', methods: ['GET'])]
    #[OA\Parameter(parameter: 'query', name: 'query',
        description: 'Filter by query',
        in: 'query', required: false)]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of posts',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Post::class, groups: ['read']))
        )
    )]
    public function search(Request $request, PostRepository $postRepository): JsonResponse
    {
        $query = $request->query->get('query');
        $posts = $postRepository->findByTitleOrContent($query);

        return $this->json($posts);
    }

    #[Route('/posts/{id}', name: 'post_show', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of posts',
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['read'])
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Post not found'
    )]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return $this->json(['message' => 'Post not found'], 404);
        }

        return $this->json($post);
    }

    #[Route('/posts', name: 'post_create', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Data for creating a new post',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['edit'])
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the created post',
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['read'])
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Something went wrong'
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $post = new Post();
        $this->fillData($post, $data);

        $errors = $validator->validate($post);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['message' => $errorsString], 400);
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json($post, 201);
    }

    #[Route('/posts/{id}', name: 'post_update', methods: ['PUT'])]
    #[OA\RequestBody(
        description: 'Data for update the post',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['edit'])
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of posts',
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['read'])
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Post not found'
    )]
    #[OA\Response(
        response: 400,
        description: 'Something went wrong'
    )]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return $this->json(['message' => 'Post not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $this->fillData($post, $data);

        $errors = $validator->validate($post);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['message' => $errorsString], 400);
        }

        $entityManager->flush();

        return $this->json($post);
    }

    #[Route('/posts/{id}', name: 'post_delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'Post deleted'
    )]
    #[OA\Response(
        response: 404,
        description: 'Post not found'
    )]
    #[OA\Response(
        response: 400,
        description: 'Something went wrong'
    )]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return $this->json(['message' => 'Post not found'], 404);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return $this->json(['message' => 'Post deleted'], 200);
    }

    private function fillData(Post $post, array $data): void
    {
        if (array_key_exists('title', $data)) {
            $post->setTitle($data['title']);
        }
        if (array_key_exists('content', $data)) {
            $post->setContent($data['content']);
        }
    }
}
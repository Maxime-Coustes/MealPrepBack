<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\TagCollection;
use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tag', name: 'tag_')]
class TagController extends AbstractController
{
    public function __construct(private readonly TagService $tagService) {}

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tags = new TagCollection();

        foreach ($data['tags'] ?? [] as $t) {
            $tag = new Tag();
            $tag->setName($t['name']);
            $tags->addTag($tag);
        }

        $result = $this->tagService->createTagCollection($tags);

        return $this->json($result);
    }


    #[Route('/tag/list', name: 'tag_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        dd('here');
        $tags = $this->tagService->findAll();

        $data = array_map(fn($tag) => [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            // ajouter d'autres propriétés si nécessaire
        ], $tags);

        return $this->json($data);
    }




    #[Route('/update', name: 'update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tags = new TagCollection();

        foreach ($data as $t) {
            $existingTag = $this->tagService->find($t['id']); // récupère depuis la BDD
            if (!$existingTag) {
                continue; // tag non trouvé, éventuellement loguer ou gérer
            }

            $existingTag->setName($t['name']); // modification
            $tags->addTag($existingTag);       // ajout à la collection
        }

        $result = $this->tagService->updateTags($tags);

        return $this->json($result);
    }


    #[Route('/{id}', name: 'read', methods: ['GET'])]
    public function read(): JsonResponse
    {
        $tags = $this->tagService->findAll();

        $data = array_map(fn($tag) => [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
        ], $tags);

        return $this->json($data);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->tagService->deleteTagById($id);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        return $this->json(['deleted' => $id]);
    }
}

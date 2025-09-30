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


    #[Route('/list', name: 'tag_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
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
        $tagsToUpdate = new TagCollection();
        $notFound = new TagCollection();

        foreach ($data as $t) {
            if (empty($t['id'])) {
                // Si pas d'ID fourni
                continue;
            }

            $existingTag = $this->tagService->find($t['id']); // récupère depuis la BDD
            if (!$existingTag) {
                $notFound->addTag(new Tag($t['id'])); // ou juste log l'id
                continue;
            }

            // Modification de l'entité existante
            $existingTag->setName($t['name']);
            $tagsToUpdate->addTag($existingTag);
        }

        if ($tagsToUpdate->isEmpty()) {
            return $this->json([
                'updated' => [],
                'not_found' => $notFound->getTags(),
                'message' => 'Aucun tag existant n\'a été fourni.'
            ], 404);
        }

        $updated = $this->tagService->updateTags($tagsToUpdate);

        return $this->json([
            'updated' => $updated['updated']->getTags(),
            'not_found' => array_merge($notFound->getTags(), $updated['not_found']->getTags()),
        ]);
    }



    #[Route('/{id}', name: 'read', methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $tag = $this->tagService->find($id);

        if (!$tag) {
            return $this->json([
                'message' => 'Tag introuvable.'
            ], 404);
        }

        // Transformer l'entité en tableau
        $data = [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            // ajoute d'autres champs si nécessaire
        ];

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

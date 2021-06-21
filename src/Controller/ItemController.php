<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Service\ItemService;
use Riverline\MultiPartParser\Converters\HttpFoundation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;

class ItemController extends AbstractController
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(): JsonResponse
    {
        $allItems = $this->cache->get('items_' . $this->getUser()->getId(), function () {
            $items = $this->getDoctrine()->getRepository(Item::class)->findBy(['user' => $this->getUser()]);

            $allItems = [];
            foreach ($items as $item) {
                $oneItem['id'] = $item->getId();
                $oneItem['data'] = $item->getData();
                $oneItem['created_at'] = $item->getCreatedAt();
                $oneItem['updated_at'] = $item->getUpdatedAt();
                $allItems[] = $oneItem;
            }

            return $allItems;
        });

        return $this->json($allItems);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ItemService $itemService)
    {
        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        $itemService->create($this->getUser(), $data);

        $this->cache->delete('items_' . $this->getUser()->getId());

        return $this->json([]);
    }

    /**
     * @Route("/item", name="item_update", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, ItemService $itemService)
    {
        $input = [];
        foreach(HttpFoundation::convert($request)->getParts() as $part) {
            $input[$part->getName()] = $part->getBody();
        }

        if (empty($input['id'])) {
            return $this->json(['error' => 'No id parameter']);
        }

        if (empty($input['data'])) {
            return $this->json(['error' => 'No data parameter']);
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->find($input['id']);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $itemService->update($item, $input['data']);

        $this->cache->delete('items_' . $this->getUser()->getId());

        return $this->json([]);
    }

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, int $id)
    {
        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->find($id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($item);
        $manager->flush();

        $this->cache->delete('items_' . $this->getUser()->getId());

        return $this->json([]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Malfunction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MalfunctionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $malfunctionRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->malfunctionRepository = $entityManager->getRepository(Malfunction::class);
    }

    /**
     * @Route("/malfunction")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handlePost($request);
        }

        /** @var Malfunction[] $malfunctions */
        $malfunctions = $this->malfunctionRepository->findAll();

        $data = [];
        foreach ($malfunctions as $malfunction) {
            $data[] = [
                'id' => $malfunction->id,
                'name' => $malfunction->name,
            ];
        }

        return $this->render('malfunction/index.html.twig', [
            'data' => json_encode($data,  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    private function handlePost(Request $request): void
    {
        $updatedIds = array_map('\intval', explode(',', $request->request->get('updatedIds', '')));
        $deletedIds = array_map('\intval', explode(',', $request->request->get('deletedIds', '')));

        $newMalfunctions = [];
        $updatedMalfunctionArrays = [];
        foreach ($request->request->get('malfunctions', []) as $malfunctionArr) {
            $id = (int)$malfunctionArr['id'];
            if ($id <= 0) {
                $newMalfunctions[] = new Malfunction($malfunctionArr['name']);
            } elseif (in_array($id, $updatedIds, true)) {
                $updatedMalfunctionArrays[$id] = $malfunctionArr;
            }
        }

        // create
        foreach ($newMalfunctions as $malfunction) {
            $this->entityManager->persist($malfunction);
        }

        // update
        /** @var Malfunction[] $updatedMalfunctions */
        $updatedMalfunctions = $this->malfunctionRepository->findBy(['id' => array_keys($updatedMalfunctionArrays)]);

        foreach ($updatedMalfunctions as $malfunction) {
            $malfunction->name = $updatedMalfunctionArrays[$malfunction->id]['name'];
            $this->entityManager->persist($malfunction);
        }

        $this->entityManager->flush();

        // delete
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->delete(Malfunction::class, 'm')
            ->where($qb->expr()->in('m.id', ':deletedIds'))
            ->setParameter(':deletedIds', $deletedIds)
            ->getQuery()
            ->execute();
    }
}

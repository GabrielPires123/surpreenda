<?php

namespace App\Controller\Front;

use App\Repository\KitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class KitController extends AbstractController
{
    public function __construct(
        private readonly KitRepository $kitRepository,
    ) {
    }

    #[Route('/kits', name: 'kit_index')]
    public function index(): Response
    {
        $kits = $this->kitRepository->findBy(['ativo' => true], ['nome' => 'ASC']);

        return $this->render('kit/index.html.twig', [
            'kits' => $kits,
        ]);
    }

    #[Route('/kit/{id}', name: 'kit_show')]
    public function show(string $id): Response
    {
        $kit = $this->kitRepository->find($id);

        if ($kit === null || !$kit->isAtivo()) {
            throw $this->createNotFoundException('Kit não encontrado.');
        }

        return $this->render('kit/show.html.twig', [
            'kit' => $kit,
        ]);
    }
}

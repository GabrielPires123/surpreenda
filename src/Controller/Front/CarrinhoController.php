<?php

namespace App\Controller\Front;

use App\Repository\KitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CarrinhoController extends AbstractController
{
    private const SESSION_KEY = 'carrinho';

    public function __construct(
        private readonly KitRepository $kitRepository,
    ) {
    }

    #[Route('/carrinho', name: 'carrinho_index')]
    public function index(SessionInterface $session): Response
    {
        $itens = $this->getItens($session);

        return $this->render('carrinho/index.html.twig', [
            'itens' => $itens,
            'total' => $this->calcularTotal($itens),
        ]);
    }

    #[Route('/carrinho/adicionar/{kitId}', name: 'carrinho_adicionar')]
    public function adicionar(string $kitId, SessionInterface $session, Request $request): Response
    {
        $kit = $this->kitRepository->find($kitId);

        if ($kit === null || !$kit->isAtivo()) {
            $this->addFlash('error', 'Kit não encontrado ou indisponível.');
            return $this->redirectToRoute('kit_index');
        }

        $itens = $this->getItens($session);
        $itens[$kitId] = ($itens[$kitId] ?? 0) + 1;

        $session->set(self::SESSION_KEY, $itens);
        $this->addFlash('success', 'Kit adicionado ao carrinho!');

        return $this->redirectToRoute('carrinho_index');
    }

    #[Route('/carrinho/remover/{kitId}', name: 'carrinho_remover')]
    public function remover(string $kitId, SessionInterface $session): Response
    {
        $itens = $this->getItens($session);
        unset($itens[$kitId]);
        $session->set(self::SESSION_KEY, $itens);

        return $this->redirectToRoute('carrinho_index');
    }

    #[Route('/carrinho/limpar', name: 'carrinho_limpar', methods: ['POST'])]
    public function limpar(SessionInterface $session): Response
    {
        $session->remove(self::SESSION_KEY);
        $this->addFlash('success', 'Carrinho limpo.');

        return $this->redirectToRoute('carrinho_index');
    }

    #[Route('/carrinho/atualizar/{kitId}', name: 'carrinho_atualizar', methods: ['POST'])]
    public function atualizar(string $kitId, Request $request, SessionInterface $session): Response
    {
        $quantidade = (int) $request->request->get('quantidade', 1);
        if ($quantidade < 1) {
            return $this->remover($kitId, $session);
        }

        $itens = $this->getItens($session);
        $itens[$kitId] = $quantidade;
        $session->set(self::SESSION_KEY, $itens);

        return $this->redirectToRoute('carrinho_index');
    }

    private function getItens(SessionInterface $session): array
    {
        return $session->get(self::SESSION_KEY, []);
    }

    private function calcularTotal(array $itens): float
    {
        $total = 0;
        foreach ($itens as $kitId => $quantidade) {
            $kit = $this->kitRepository->find($kitId);
            if ($kit) {
                $total += $kit->getPreco() * $quantidade;
            }
        }
        return $total;
    }
}

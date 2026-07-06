<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Enum\SubscriptionStatus;
use App\Service\Interface\AssinaturaServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/minhas-assinaturas', name: 'assinatura_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class AssinaturaController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(AssinaturaServiceInterface $assinaturaService): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cliente = $user->getCliente();

        if (!$cliente) {
            return $this->redirectToRoute('cadastro_pet_get');
        }

        $assinaturas = $assinaturaService->listAssinaturasByUserId($user->getId());
        $assinaturasDetalhadas = [];

        foreach ($assinaturas as $assinatura) {
            $assinaturasDetalhadas[] = [
                'assinatura' => $assinatura,
                'proxima_cobranca' => $assinaturaService->getProximaCobranca($assinatura),
                'is_vencida' => $assinaturaService->isVencida($assinatura),
                'is_active' => $assinaturaService->isActive($assinatura),
            ];
        }

        return $this->render('assinatura/index.html.twig', [
            'assinaturas' => $assinaturasDetalhadas,
        ]);
    }

    #[Route('/{id}/cancelar', name: 'cancelar', methods: ['POST'])]
    public function cancelar(
        string $id,
        Request $request,
        AssinaturaServiceInterface $assinaturaService
    ): Response {
        if (!$this->isCsrfTokenValid('cancelar_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de segurança inválido.');
            return $this->redirectToRoute('assinatura_index');
        }

        try {
            $assinatura = $assinaturaService->getAssinaturaOrThrow($id);
            $assinaturaService->cancelar($assinatura);
            $this->addFlash('success', 'Assinatura cancelada com sucesso.');
        } catch (\InvalidArgumentException|\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('assinatura_index');
    }

    #[Route('/{id}/pausar', name: 'pausar', methods: ['POST'])]
    public function pausar(
        string $id,
        Request $request,
        AssinaturaServiceInterface $assinaturaService
    ): Response {
        if (!$this->isCsrfTokenValid('pausar_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de segurança inválido.');
            return $this->redirectToRoute('assinatura_index');
        }

        try {
            $assinatura = $assinaturaService->getAssinaturaOrThrow($id);
            $assinaturaService->pauseSubscription($assinatura);
            $this->addFlash('success', 'Assinatura pausada com sucesso.');
        } catch (\InvalidArgumentException|\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('assinatura_index');
    }
}

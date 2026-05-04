<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CadastroController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    #[Route('/cadastro', name: 'cadastro_usuario', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('cadastro/index.html.twig');
    }

    #[Route('/cadastro', name: 'cadastro_usuario_post', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $nome = $request->request->get('nome', '');
        $sobrenome = $request->request->get('sobrenome', '');
        $email = $request->request->get('email', '');
        $senha = $request->request->get('senha', '');
        $telefone = $request->request->get('telefone', '');
        $cpf = preg_replace('/\D/', '', $request->request->get('cpf', ''));

        try {
            $this->authService->registerUser(
                $nome . ' ' . $sobrenome,
                $email,
                $cpf,
                $senha,
                $telefone
            );
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cadastro_usuario');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cadastro_usuario');
        } catch (\Throwable $e) {
            error_log("CADASTRO ERROR: " . get_class($e) . " - " . $e->getMessage());
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cadastro_usuario');
        }

        $this->addFlash('success', 'Conta criada com sucesso!');
        return $this->redirectToRoute('home_login');
    }

    #[Route('/cadastro/pet', name: 'cadastro_pet')]
    public function pet(): Response
    {
        return $this->render('cadastro/pet.html.twig');
    }
}

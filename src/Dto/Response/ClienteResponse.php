<?php

namespace App\Dto\Response;

use App\Entity\Cliente;
use App\Entity\User;

class ClienteResponse
{
    public string $id;
    public string $nome;
    public string $email;
    public string $cpf;
    public ?EnderecoResponse $enderecoPrincipal = null;
    /** @var EnderecoResponse[] */
    public array $enderecos = [];
    /** @var TelefoneResponse[] */
    public array $telefones = [];
    /** @var PetResponse[] */
    public array $pets = [];
    public ?AssinaturaResponse $assinatura = null;
    /** @var PedidoResponse[] */
    public array $pedidos = [];

    public static function fromUserAndCliente(User $user, Cliente $cliente): self
    {
        $response = new self();
        $response->id = $cliente->getId();
        $response->nome = $user->getNome();
        $response->email = $user->getEmail();
        $response->cpf = $cliente->getCpf();

        foreach ($cliente->getEnderecos() as $endereco) {
            $enderecoDto = EnderecoResponse::fromEntity($endereco);
            $response->enderecos[] = $enderecoDto;
            if ($endereco->isPrincipal()) {
                $response->enderecoPrincipal = $enderecoDto;
            }
        }

        foreach ($cliente->getTelefones() as $telefone) {
            $response->telefones[] = TelefoneResponse::fromEntity($telefone);
        }

        foreach ($cliente->getPets() as $pet) {
            $response->pets[] = PetResponse::fromEntity($pet);
        }

        if ($cliente->getAssinatura() !== null) {
            $response->assinatura = AssinaturaResponse::fromEntity($cliente->getAssinatura());
        }

        foreach ($cliente->getPedidos() as $pedido) {
            $response->pedidos[] = PedidoResponse::fromEntity($pedido);
        }

        return $response;
    }
}

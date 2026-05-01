<?php

namespace App\Dto\Response;

use App\Entity\Pedido;

class PedidoResponse
{
    public string $id;
    public string $status;
    public float $valorTotal;
    public string $dataPedido;
    public ?string $dataEntregaEstimada = null;
    public ?array $kit = null;
    public ?array $pet = null;
    public string $clienteId;
    public ?string $observacoes = null;
    public ?array $assinatura = null;

    public static function fromEntity(Pedido $entity): self
    {
        $response = new self();
        $response->id = $entity->getId();
        $response->status = $entity->getStatus()->value;
        $response->valorTotal = $entity->getValorTotal();
        $response->dataPedido = $entity->getDataPedido()->format('Y-m-d H:i:s');
        $response->dataEntregaEstimada = $entity->getDataEntregaEstimada()?->format('Y-m-d H:i:s');
        $response->clienteId = $entity->getCliente()->getId();
        $response->observacoes = $entity->getObservacoes();

        $kit = $entity->getKit();
        if ($kit !== null) {
            $response->kit = [
                'id' => $kit->getId(),
                'nome' => $kit->getNome(),
                'preco' => $kit->getPreco(),
            ];
        }

        $pet = $entity->getPet();
        if ($pet !== null) {
            $response->pet = [
                'id' => $pet->getId(),
                'nome' => $pet->getNome(),
            ];
        }

        $assinatura = $entity->getAssinatura();
        if ($assinatura !== null) {
            $response->assinatura = AssinaturaResponse::fromEntity($assinatura);
        }

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Cliente;
use App\Entity\Kit;
use App\Entity\Pedido;
use App\Entity\Pet;

interface PedidoServiceInterface
{
    public function createPedido(Cliente $cliente, Kit $kit, Pet $pet): Pedido;
    public function checkout(int $userId, Kit $kit, Pet $pet): array;
    public function listPedidosByUserId(int $userId): array;
    public function getPedidoOrThrow(string $pedidoId): Pedido;
    public function cancelarPedido(string $pedidoId): Pedido;
    public function trackPedido(string $pedidoId): Pedido;
}

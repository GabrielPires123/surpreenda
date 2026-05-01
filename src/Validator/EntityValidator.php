<?php

namespace App\Validator;

use App\Entity\Assinatura;
use App\Entity\Cliente;
use App\Entity\Endereco;
use App\Entity\Kit;
use App\Entity\Categoria;
use App\Entity\Pedido;
use App\Entity\Pet;
use App\Entity\Produto;
use App\Entity\Telefone;
use App\Entity\User;

/**
 * Centraliza todas as validações de entidades.
 * Cada método valida uma entidade e lança ValidationException em caso de erro.
 */
class EntityValidator
{
    public function validateCliente(Cliente $cliente): void
    {
        $violations = [];

        if ($cliente->getCpf() !== null) {
            $cpf = preg_replace('/\D/', '', $cliente->getCpf());
            if (strlen($cpf) !== 11) {
                $violations[] = 'CPF deve ter 11 dígitos.';
            }
            if (!$this->isValidCpf($cpf)) {
                $violations[] = 'CPF inválido.';
            }
        }

        if (count($violations) > 0) {
            throw new ValidationException('Cliente inválido.', $violations);
        }
    }

    public function validateEndereco(Endereco $endereco): void
    {
        $violations = [];

        $cep = preg_replace('/\D/', '', $endereco->getCep());
        if (strlen($cep) !== 8) {
            $violations[] = 'CEP deve ter 8 dígitos.';
        }

        if (empty($endereco->getLogradouro())) {
            $violations[] = 'O logradouro é obrigatório.';
        }

        if (empty($endereco->getNumero())) {
            $violations[] = 'O número é obrigatório.';
        }

        if (empty($endereco->getBairro())) {
            $violations[] = 'O bairro é obrigatório.';
        }

        if (empty($endereco->getCidade())) {
            $violations[] = 'A cidade é obrigatória.';
        }

        if (strlen($endereco->getEstado()) !== 2) {
            $violations[] = 'O estado deve ter 2 caracteres.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Endereço inválido.', $violations);
        }
    }

    public function validatePet(Pet $pet): void
    {
        $violations = [];

        if (empty($pet->getNome()) || strlen($pet->getNome()) < 2 || strlen($pet->getNome()) > 50) {
            $violations[] = 'O nome do pet deve ter entre 2 e 50 caracteres.';
        }

        if ($pet->getPeso() <= 0) {
            $violations[] = 'O peso deve ser um valor positivo.';
        }

        if ($pet->getIdadeMeses() < 0) {
            $violations[] = 'A idade deve ser zero ou positiva.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Pet inválido.', $violations);
        }
    }

    public function validateTelefone(Telefone $telefone): void
    {
        $violations = [];

        $ddd = preg_replace('/\D/', '', $telefone->getDdd());
        if (strlen($ddd) !== 2) {
            $violations[] = 'DDD deve ter 2 dígitos.';
        }

        $numero = preg_replace('/\D/', '', $telefone->getNumero());
        if (strlen($numero) < 8 || strlen($numero) > 9) {
            $violations[] = 'Número de telefone inválido.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Telefone inválido.', $violations);
        }
    }

    public function validateProduto(Produto $produto): void
    {
        $violations = [];

        if (empty($produto->getNome()) || strlen($produto->getNome()) < 3 || strlen($produto->getNome()) > 100) {
            $violations[] = 'O nome do produto deve ter entre 3 e 100 caracteres.';
        }

        if ($produto->getPrecoCusto() < 0) {
            $violations[] = 'O preço de custo não pode ser negativo.';
        }

        if ($produto->getPrecoVenda() <= 0) {
            $violations[] = 'O preço de venda deve ser positivo.';
        }

        if ($produto->getEstoque() < 0) {
            $violations[] = 'O estoque não pode ser negativo.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Produto inválido.', $violations);
        }
    }

    public function validateKit(Kit $kit): void
    {
        $violations = [];

        if (empty($kit->getNome()) || strlen($kit->getNome()) < 3 || strlen($kit->getNome()) > 100) {
            $violations[] = 'O nome do kit deve ter entre 3 e 100 caracteres.';
        }

        if ($kit->getPreco() <= 0) {
            $violations[] = 'O preço deve ser positivo.';
        }

        if ($kit->getQuantidadeItens() <= 0) {
            $violations[] = 'A quantidade de itens deve ser positiva.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Kit inválido.', $violations);
        }
    }

    public function validatePedido(Pedido $pedido): void
    {
        $violations = [];

        if ($pedido->getValorTotal() <= 0) {
            $violations[] = 'O valor total deve ser positivo.';
        }

        if ($pedido->getValorDesconto() !== null && $pedido->getValorDesconto() < 0) {
            $violations[] = 'O valor de desconto não pode ser negativo.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Pedido inválido.', $violations);
        }
    }

    public function validateAssinatura(Assinatura $assinatura): void
    {
        $violations = [];

        if (empty($assinatura->getPlano())) {
            $violations[] = 'O plano é obrigatório.';
        }

        if ($assinatura->getValor() <= 0) {
            $violations[] = 'O valor deve ser positivo.';
        }

        if ($assinatura->getIntervaloDias() <= 0) {
            $violations[] = 'O intervalo deve ser positivo.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Assinatura inválida.', $violations);
        }
    }

    public function validateUser(User $user): void
    {
        $violations = [];

        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $violations[] = 'E-mail inválido.';
        }

        if (empty($user->getFirstName()) || strlen($user->getFirstName()) > 50) {
            $violations[] = 'O primeiro nome é obrigatório e deve ter no máximo 50 caracteres.';
        }

        if (empty($user->getLastName()) || strlen($user->getLastName()) > 50) {
            $violations[] = 'O sobrenome é obrigatório e deve ter no máximo 50 caracteres.';
        }

        $password = $user->getPassword();
        if (empty($password) || strlen($password) < 8) {
            $violations[] = 'A senha deve ter no mínimo 8 caracteres.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Usuário inválido.', $violations);
        }
    }

    public function validateCategoria(Categoria $categoria): void
    {
        $violations = [];

        if (empty($categoria->getNome()) || strlen($categoria->getNome()) < 3 || strlen($categoria->getNome()) > 50) {
            $violations[] = 'O nome da categoria deve ter entre 3 e 50 caracteres.';
        }

        if (count($violations) > 0) {
            throw new ValidationException('Categoria inválida.', $violations);
        }
    }

    /**
     * Valida digito verificador de CPF.
     */
    private function isValidCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Validate first digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $rest = ($sum * 10) % 11;
        if ($rest === 10) {
            $rest = 0;
        }
        if ($rest !== intval($cpf[9])) {
            return false;
        }

        // Validate second digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $rest = ($sum * 10) % 11;
        if ($rest === 10) {
            $rest = 0;
        }
        if ($rest !== intval($cpf[10])) {
            return false;
        }

        return true;
    }
}

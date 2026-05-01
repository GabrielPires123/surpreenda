<?php

namespace App\Dto\Response;

class TelefoneResponse
{
    public string $id;
    public string $ddd;
    public string $numero;
    public string $fullNumber;
    public string $tipo;

    public function __construct(string $id, string $ddd, string $numero, string $tipo)
    {
        $this->id = $id;
        $this->ddd = $ddd;
        $this->numero = $numero;
        $this->fullNumber = '(' . $ddd . ') ' . $numero;
        $this->tipo = $tipo;
    }

    public static function fromEntity(\App\Entity\Telefone $entity): self
    {
        return new self(
            $entity->getId(),
            $entity->getDdd(),
            $entity->getNumero(),
            $entity->getTipo()
        );
    }
}

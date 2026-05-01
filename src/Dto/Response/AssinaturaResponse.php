<?php

namespace App\Dto\Response;

use App\Entity\Assinatura;

class AssinaturaResponse
{
    public string $id;
    public string $plano;
    public float $valor;
    public string $status;
    public int $intervaloDias;
    public string $dataInicio;
    public ?string $dataFim = null;
    public ?string $dataUltimaCobranca = null;
    public ?string $proximaCobranca = null;

    public function __construct(
        string $id,
        string $plano,
        float $valor,
        string $status,
        int $intervaloDias,
        string $dataInicio,
        ?string $dataFim,
        ?string $dataUltimaCobranca,
        ?string $proximaCobranca
    ) {
        $this->id = $id;
        $this->plano = $plano;
        $this->valor = $valor;
        $this->status = $status;
        $this->intervaloDias = $intervaloDias;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->dataUltimaCobranca = $dataUltimaCobranca;
        $this->proximaCobranca = $proximaCobranca;
    }

    public static function fromEntity(Assinatura $entity): self
    {
        return new self(
            $entity->getId(),
            $entity->getPlano(),
            $entity->getValor(),
            $entity->getStatus()->value,
            $entity->getIntervaloDias(),
            $entity->getDataInicio()->format('Y-m-d\TH:i:sP'),
            $entity->getDataFim()?->format('Y-m-d\TH:i:sP'),
            $entity->getDataUltimaCobranca()?->format('Y-m-d\TH:i:sP'),
            $entity->getProximaCobranca()?->format('Y-m-d\TH:i:sP')
        );
    }
}

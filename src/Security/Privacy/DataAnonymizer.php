<?php

namespace App\Security\Privacy;

/**
 * Serviço responsável por anonimizar dados pessoais em conformidade com a LGPD.
 *
 * Quando um usuário solicita a exclusão de sua conta ou quando o período de
 * retenção expira, este serviço substitui dados identificáveis por valores
 * genéricos irreversíveis.
 */
class DataAnonymizer
{
    private const ANONYMOUS_NAME = 'Usuário Anonimizado';
    private const ANONYMOUS_EMAIL_PREFIX = 'anon_';
    private const ANONYMOUS_CPF = '00000000000';

    public function __construct(
        private readonly string $appSecret,
    ) {
    }

    /**
     * Gera um e-mail anonimizado único a partir do e-mail original.
     */
    public function anonymizeEmail(string $email): string
    {
        $hash = substr(hash('sha256', $email . $this->appSecret), 0, 8);
        return self::ANONYMOUS_EMAIL_PREFIX . $hash . '@anonimized.local';
    }

    /**
     * Gera um CPF anonimizado a partir do CPF original.
     * Mantém o formato mas torna irreversível.
     */
    public function anonymizeCpf(string $cpf): string
    {
        $hash = hash('sha256', $cpf . $this->appSecret);
        return preg_replace('/\D/', '', substr($hash, 0, 11));
    }

    /**
     * Retorna o nome padrão para usuários anonimizados.
     */
    public function getAnonymousName(): string
    {
        return self::ANONYMOUS_NAME;
    }

    /**
     * Anonimiza um endereço de e-mail substituindo por hash irreversível.
     */
    public function anonymizeString(string $value): string
    {
        return 'anon_' . substr(hash('sha256', $value . $this->appSecret), 0, 12);
    }

    /**
     * Anonimiza número de telefone.
     */
    public function anonymizePhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);
        return str_repeat('*', strlen($clean) - 4) . substr($clean, -4);
    }

    /**
     * Anonimiza um endereço de e-mail substituindo por hash irreversível.
     */
    public function anonymizeAddress(string $address): string
    {
        return $this->anonymizeString($address);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428133320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "assinatura" (id UUID NOT NULL, status VARCHAR(255) NOT NULL, plano VARCHAR(50) NOT NULL, valor DOUBLE PRECISION NOT NULL, intervalo_dias INT NOT NULL, data_inicio TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, data_fim TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, data_proximo_envio TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, renovacoes INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cliente_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F078070DE734E51 ON "assinatura" (cliente_id)');
        $this->addSql('CREATE TABLE "categoria" (id UUID NOT NULL, nome VARCHAR(50) NOT NULL, descricao VARCHAR(255) DEFAULT NULL, icone_url VARCHAR(255) DEFAULT NULL, ordem INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E10122D54BD530C ON "categoria" (nome)');
        $this->addSql('CREATE TABLE "cliente" (id UUID NOT NULL, cpf VARCHAR(11) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, anonymized_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F41C9B253E3E11F0 ON "cliente" (cpf)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F41C9B25A76ED395 ON "cliente" (user_id)');
        $this->addSql('CREATE TABLE "endereco" (id UUID NOT NULL, cep VARCHAR(8) NOT NULL, logradouro VARCHAR(100) NOT NULL, numero VARCHAR(10) NOT NULL, complemento VARCHAR(100) DEFAULT NULL, bairro VARCHAR(50) NOT NULL, cidade VARCHAR(50) NOT NULL, estado VARCHAR(2) NOT NULL, is_principal BOOLEAN NOT NULL, cliente_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F8E0D60EDE734E51 ON "endereco" (cliente_id)');
        $this->addSql('CREATE TABLE "kit" (id UUID NOT NULL, nome VARCHAR(100) NOT NULL, descricao VARCHAR(500) DEFAULT NULL, preco DOUBLE PRECISION NOT NULL, imagem_url VARCHAR(255) DEFAULT NULL, quantidade_itens INT NOT NULL, ativo BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE kit_produto (kit_id UUID NOT NULL, produto_id UUID NOT NULL, PRIMARY KEY (kit_id, produto_id))');
        $this->addSql('CREATE INDEX IDX_D5096A303A8E60EF ON kit_produto (kit_id)');
        $this->addSql('CREATE INDEX IDX_D5096A30105CFD56 ON kit_produto (produto_id)');
        $this->addSql('CREATE TABLE kit_categoria (kit_id UUID NOT NULL, categoria_id UUID NOT NULL, PRIMARY KEY (kit_id, categoria_id))');
        $this->addSql('CREATE INDEX IDX_FD488FD13A8E60EF ON kit_categoria (kit_id)');
        $this->addSql('CREATE INDEX IDX_FD488FD13397707A ON kit_categoria (categoria_id)');
        $this->addSql('CREATE TABLE "pedido" (id UUID NOT NULL, status VARCHAR(255) NOT NULL, valor_total DOUBLE PRECISION NOT NULL, valor_desconto DOUBLE PRECISION DEFAULT NULL, observacoes TEXT DEFAULT NULL, codigo_rastreio VARCHAR(100) DEFAULT NULL, data_pedido TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, data_envio TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, data_entrega TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, anonymized_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cliente_id UUID NOT NULL, kit_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C4EC16CEDE734E51 ON "pedido" (cliente_id)');
        $this->addSql('CREATE INDEX IDX_C4EC16CE3A8E60EF ON "pedido" (kit_id)');
        $this->addSql('CREATE TABLE "pet" (id UUID NOT NULL, nome VARCHAR(50) NOT NULL, tipo VARCHAR(255) NOT NULL, raca VARCHAR(50) DEFAULT NULL, peso DOUBLE PRECISION NOT NULL, idade_meses INT NOT NULL, foto_url VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cliente_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_E4529B85DE734E51 ON "pet" (cliente_id)');
        $this->addSql('CREATE TABLE "produto" (id UUID NOT NULL, nome VARCHAR(100) NOT NULL, descricao VARCHAR(500) DEFAULT NULL, preco_custo DOUBLE PRECISION NOT NULL, preco_venda DOUBLE PRECISION NOT NULL, estoque INT NOT NULL, imagem_url VARCHAR(255) DEFAULT NULL, ativo BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, categoria_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5CAC49D73397707A ON "produto" (categoria_id)');
        $this->addSql('CREATE TABLE "telefone" (id UUID NOT NULL, ddd VARCHAR(2) NOT NULL, numero VARCHAR(9) NOT NULL, tipo VARCHAR(20) NOT NULL, cliente_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2132E361DE734E51 ON "telefone" (cliente_id)');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, anonymized_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE "assinatura" ADD CONSTRAINT FK_2F078070DE734E51 FOREIGN KEY (cliente_id) REFERENCES "cliente" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "cliente" ADD CONSTRAINT FK_F41C9B25A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "endereco" ADD CONSTRAINT FK_F8E0D60EDE734E51 FOREIGN KEY (cliente_id) REFERENCES "cliente" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE kit_produto ADD CONSTRAINT FK_D5096A303A8E60EF FOREIGN KEY (kit_id) REFERENCES "kit" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kit_produto ADD CONSTRAINT FK_D5096A30105CFD56 FOREIGN KEY (produto_id) REFERENCES "produto" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kit_categoria ADD CONSTRAINT FK_FD488FD13A8E60EF FOREIGN KEY (kit_id) REFERENCES "kit" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kit_categoria ADD CONSTRAINT FK_FD488FD13397707A FOREIGN KEY (categoria_id) REFERENCES "categoria" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE "pedido" ADD CONSTRAINT FK_C4EC16CEDE734E51 FOREIGN KEY (cliente_id) REFERENCES "cliente" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "pedido" ADD CONSTRAINT FK_C4EC16CE3A8E60EF FOREIGN KEY (kit_id) REFERENCES "kit" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "pet" ADD CONSTRAINT FK_E4529B85DE734E51 FOREIGN KEY (cliente_id) REFERENCES "cliente" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "produto" ADD CONSTRAINT FK_5CAC49D73397707A FOREIGN KEY (categoria_id) REFERENCES "categoria" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "telefone" ADD CONSTRAINT FK_2132E361DE734E51 FOREIGN KEY (cliente_id) REFERENCES "cliente" (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "assinatura" DROP CONSTRAINT FK_2F078070DE734E51');
        $this->addSql('ALTER TABLE "cliente" DROP CONSTRAINT FK_F41C9B25A76ED395');
        $this->addSql('ALTER TABLE "endereco" DROP CONSTRAINT FK_F8E0D60EDE734E51');
        $this->addSql('ALTER TABLE kit_produto DROP CONSTRAINT FK_D5096A303A8E60EF');
        $this->addSql('ALTER TABLE kit_produto DROP CONSTRAINT FK_D5096A30105CFD56');
        $this->addSql('ALTER TABLE kit_categoria DROP CONSTRAINT FK_FD488FD13A8E60EF');
        $this->addSql('ALTER TABLE kit_categoria DROP CONSTRAINT FK_FD488FD13397707A');
        $this->addSql('ALTER TABLE "pedido" DROP CONSTRAINT FK_C4EC16CEDE734E51');
        $this->addSql('ALTER TABLE "pedido" DROP CONSTRAINT FK_C4EC16CE3A8E60EF');
        $this->addSql('ALTER TABLE "pet" DROP CONSTRAINT FK_E4529B85DE734E51');
        $this->addSql('ALTER TABLE "produto" DROP CONSTRAINT FK_5CAC49D73397707A');
        $this->addSql('ALTER TABLE "telefone" DROP CONSTRAINT FK_2132E361DE734E51');
        $this->addSql('DROP TABLE "assinatura"');
        $this->addSql('DROP TABLE "categoria"');
        $this->addSql('DROP TABLE "cliente"');
        $this->addSql('DROP TABLE "endereco"');
        $this->addSql('DROP TABLE "kit"');
        $this->addSql('DROP TABLE kit_produto');
        $this->addSql('DROP TABLE kit_categoria');
        $this->addSql('DROP TABLE "pedido"');
        $this->addSql('DROP TABLE "pet"');
        $this->addSql('DROP TABLE "produto"');
        $this->addSql('DROP TABLE "telefone"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

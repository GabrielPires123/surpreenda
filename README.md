# Surpreenda Pet

E-commerce de kits surpresa recorrentes para cães e gatos.

## Stack

- **PHP** 8.2+
- **Symfony** 7.2
- **Doctrine ORM** 3.x
- **PostgreSQL** 16 (Docker) / SQLite (local)
- **Twig** para templates

## Rodando o Projeto

### Com Docker (recomendado)

O Docker sobe tudo junto: banco, app e mailer. É o ambiente mais próximo do produção.

```bash
# Sobe os containers (o entrypoint já roda migrations e fixtures sozinho)
docker compose up -d --build

# Acessa a aplicação
# App:     http://localhost:8080
# Mailpit: http://localhost:8025

# Ver os logs
docker compose logs -f php

# Parar (mantém os dados do banco)
docker compose down

# Parar e limpar tudo (apaga o banco também)
docker compose down -v
```

Se precisar rodar migrations ou fixtures na mão:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

### Com Symfony Local Server (mais rápido pro dia a dia)

Não tem nada de errado em usar o servidor local — é até mais ágil pra desenvolver.
O truque aqui é usar SQLite em vez de PostgreSQL pra não precisar instalar driver extra.

```bash
# Instala as dependências
composer install

# O .env.local já está configurado com SQLite.
# Se quiser usar PostgreSQL local, troque a DATABASE_URL nele.

# Cria o banco e aplica as migrations
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

# Sobe o servidor
symfony server:start

# Acessa em http://localhost:8000

# Pra parar
symfony server:stop
```

Se o Symfony reclamar de driver faltando (`could not find driver`), é porque o PHP local não tem o `pdo_pgsql` habilitado. O `.env.local` com SQLite contorna isso.

### Docker vs Local

| | Docker | Local (symfony server) |
|---|---|---|
| Banco | PostgreSQL 16 | SQLite ou PostgreSQL local |
| Mailer | Mailpit incluído | Precisa configurar à parte |
| Performance | Leve overhead | Mais rápido |
| Setup | Zero config | Instalar PHP, Composer, etc |
| Ideal pra | Equipe / CI / Staging | Dev individual |

## Credenciais de Teste

Depois de rodar as fixtures, usa essas credenciais pra logar:

- **Email:** gabriel@teste.com
- **Senha:** 12345678
- **CPF:** 52998224725

## Rotas

### Páginas (Twig)

| Rota | O que faz | Precisa login? |
|---|---|---|
| `/` | Página inicial | Não |
| `/cadastro` | Formulário de cadastro | Não |
| `/login` | Formulário de login | Não |
| `/logout` | Encerra sessão | Não |
| `/produtos` | Lista de produtos | Não |
| `/categorias` | Lista de categorias | Não |
| `/perfil` | Editar perfil | Sim |
| `/ajuda` | Página de ajuda | Não |

### API (JSON)

| Prefixo | O que faz |
|---|---|
| `/api/auth/*` | Login e registro com JWT |
| `/api/pedidos/*` | Gerenciar pedidos |
| `/api/pets/*` | CRUD de pets |
| `/api/profile/*` | Dados do perfil |
| `/api/kits/*` | Listar kits |
| `/api/assinatura/*` | Gerenciar assinaturas |

## Como o Código Está Organizado

```
src/
├── Controller/
│   ├── Backend/       # API (retorna JSON, autentica com JWT)
│   └── Front/         # Web (renderiza Twig, autentica com sessão)
├── Entity/            # Modelos Doctrine
├── Enum/              # Enums tipados (status, tipos, etc)
├── Repository/        # Queries customizadas
├── Security/
│   ├── AccessTokenHandler.php
│   ├── Privacy/       # Anonimização LGPD
│   └── UserProvider.php
├── Service/
│   └── AuthService.php  # Registro e login centralizados
├── Validator/
│   ├── Cpf.php
│   ├── CpfValidator.php
│   └── EntityValidator.php
└── Kernel.php

templates/
├── base.html.twig       # Layout base (header + footer compartilhados)
├── components/          # Partials reutilizáveis
├── cadastro/
├── landing/
├── login/
├── perfil/
├── product/
├── category/
└── ajuda/

assets/styles/
├── shared.css           # Estilos globais (carregados via base.html.twig)
├── app.css              # Customizações da aplicação
└── */                   # CSS específico por feature

config/
├── packages/            # Config de cada bundle
├── services.yaml        # Injeção de dependência
└── bundles.php          # Bundles ativos

migrations/              # Migrations Doctrine
```

## Entidades

Todas as entidades usam UUID v4 como identificador primário e attributes do Doctrine pra mapeamento ORM.

### User

Conta de acesso do sistema. Autentica com o Security Component do Symfony e tem relação 1:1 com Cliente.

### Cliente

Dados pessoais do usuário. Guarda CPF, endereços, telefones, pets e pedidos. Relação 1:N com cada um desses.

### Produto

Itens individuais que compõem os kits. Cada produto pertence a uma categoria e tem preço de custo e venda.

### Kit

Combinação de produtos com preço próprio. O custo total é calculado automaticamente com base nos produtos incluídos.

### Assinatura

Plano recorrente vinculado a um Cliente. Status possíveis: ativo, pausado, cancelado ou expirado.

### Pedido

Cada entrega gerada a partir de uma assinatura. Acompanha status (pendente, confirmado, enviado, entregue, cancelado) e código de rastreio.

## Diagrama

```
USER 1──1 CLIENTE
          ├── N ENDERECO
          ├── N TELEFONE
          ├── N PET
          ├── N PEDIDO ── KIT
          └── 1 ASSINATURA

KIT ── N PRODUTO ── 1 CATEGORIA
```

## Enums

| Enum | Valores |
|---|---|
| `PetType` | `dog`, `cat` |
| `OrderStatus` | `pending`, `confirmed`, `shipped`, `delivered`, `cancelled` |
| `SubscriptionStatus` | `active`, `paused`, `cancelled`, `expired` |

## LGPD — Anonimização

O serviço `DataAnonymizer` cuida de mascarar dados pessoais quando necessário:

| Método | O que faz |
|---|---|
| `anonymizeEmail()` | Hash SHA-256 do e-mail com salt |
| `anonymizeCpf()` | Hash irreversível do CPF |
| `anonymizeString()` | Hash genérico pra qualquer string |
| `anonymizePhone()` | Mantém só os 4 últimos dígitos |
| `anonymizeAddress()` | Hash do endereço completo |
| `getAnonymousName()` | Nome padrão pra usuários anonimizados |

As entidades `User`, `Cliente` e `Pedido` têm campo `anonymizedAt` pra auditoria.

## Docker Services

| Serviço | Imagem | Porta |
|---|---|---|
| `php` | PHP 8.2-fpm customizado | 8080 |
| `database` | PostgreSQL 16-alpine | 5432 |
| `mailer` | Mailpit | 1025 / 8025 |

## Comandos Úteis

```bash
# Docker
docker compose up -d
docker compose down
docker compose logs -f php
docker compose exec php bash

# Dentro do container
php bin/console doctrine:schema:validate
php bin/console make:migration
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console cache:clear
php bin/console debug:router

# Local
symfony server:start
symfony server:stop
symfony console doctrine:fixtures:load --no-interaction
php bin/console debug:router
```

## Próximos Passos

- [ ] Validação completa de CPF no cadastro
- [ ] Página de perfil funcional
- [ ] Carrinho de compras
- [ ] Fluxo de assinatura
- [ ] Tests (PHPUnit)
- [ ] Admin Dashboard (Symfony UX/EasyAdmin)

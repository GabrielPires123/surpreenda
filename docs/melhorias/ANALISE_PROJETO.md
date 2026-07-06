# Análise do Projeto — Surpreenda

## 1. O que o projeto faz

E-commerce de assinaturas de kits de produtos para pets (cães e gatos). O cliente:

- Se cadastra, cria perfil e cadastra pets
- Navega categorias e produtos individualmente
- Monta kits pré-montados (combinações de produtos + categorias)
- Adiciona kits ao carrinho e finaliza pedidos
- Assinaturas recorrentes com status (active, paused, cancelled, expired)
- Painel de ajuda, landing page, autenticação web + API JWT

## 2. Como faz — Arquitetura

### Padrão geral

Service-Repository com interfaces. Controllers delegam para services que delegam para repositories. Entities Doctrine com UUID como PK. Relacionamento User → Cliente (1:1), Cliente → Pet/Endereco/Telefone/Pedido/Assinatura (1:N), Kit ↔ Produto/Categoria (ManyToMany).

### Camadas

| Camada | Diretório | Responsabilidade |
|---|---|---|
| Controllers | `src/Controller/` | Rotas, request/response, validação superficial |
| DTOs | `src/Dto/Request/` | Deserialização de JSON de entrada |
| Entities | `src/Entity/` | Modelo de domínio Doctrine |
| Enums | `src/Enum/` | PetType, OrderStatus, SubscriptionStatus |
| Repositories | `src/Repository/` | Queries Doctrine com interfaces |
| Services | `src/Service/` | Regra de negócio, cálculos, orquestração |
| Security | `src/Security/` | UserProvider, Voters, AccessTokenHandler, Privacy |
| Validators | `src/Validator/` | Validação manual de entidades |
| DataFixtures | `src/DataFixtures/` | Dados de seed para dev |
| Templates | `templates/` | Twig com herança via base.html.twig |
| Migrations | `migrations/` | Schema PostgreSQL |

### Fluxo de autenticação

1. **Web**: form_login Symfony → session → `home_login` route
2. **API**: POST `/api/auth/login` → json_login → gera JWT via `JwtService`
3. **API protected**: access_token handler → `JwtService::decodeToken()`
4. **Register**: POST `/api/auth/register` → sem auth → cria User + Cliente
5. **Voters**: `AssinaturaVoter`, `ClienteVoter`, `PedidoVoter` — isolamento cross-user

### Fluxo de pedido

1. Cliente adiciona kit ao carrinho (session/cart service)
2. Checkout via `CheckoutRequest` DTO → valida kit, pet, endereco
3. Cria `Pedido` com status `pending`
4. Se assinatura ativa → cria `AssinaturaItem` vinculado
5. Status transita: pending → confirmed → shipped → delivered

## 3. Tecnologias

### Backend

| Tecnologia | Versão | Uso |
|---|---|---|
| PHP | 8.2 | Runtime |
| Symfony | 7.3 | Framework web, DI, security, console, mailer, http-client |
| Doctrine ORM | 3.4 | Persistência, migrations, fixtures |
| PostgreSQL | via Docker | Banco de produção |
| SQLite | dev local | Banco de desenvolvimento |
| lcobucci/jwt | 5.5 | Geração e validação de tokens JWT |
| Faker | 2.0 | Dados de teste/fixtures |
| PHPStan | 2.1 | Análise estática |
| PHP-CS-Fixer | 3.80 | Formatação de código |
| Rector | 2.0 | Refatoração automática |
| PHPUnit | 11.5 | Testes unitários |
| LiipTestFixturesBundle | 3.4 | Fixtures em testes |
| Symfony Mailer + MailerSend | | Envio de e-mails transacionais |
| Symfony HTTP Client | | Chamadas externas (ex: APIs de pagamento) |

### Frontend

| Tecnologia | Uso |
|---|---|
| Twig | Templates com herança |
| CSS inline nos templates | Sem build tool, sem framework CSS |
| SVG inline | Ilustrações em templates |
| JavaScript vanilla | Interações mínimas nos templates |

### Infra

| Tecnologia | Uso |
|---|---|
| Docker | PostgreSQL, ambiente isolado |
| Dockerfile | PHP 8.2-FPM + Composer + built-in server |
| Symfony CLI | Dev server com SQLite |

## 4. O que pode ser melhorado — Prioridades

---

### PRIORIDADE 1 — Crítico (segurança e correção)

#### 1.1 DTOs sem validação com Symfony Validator

**O que está:** `CheckoutRequest`, `EnderecoRequest`, `LoginRequest`, `RegisterRequest`, `TelefoneRequest` fazem validação manual com `if (empty($data['field']))` e lançam `InvalidArgumentException`.

**Problema:**
- Validação manual é propensa a erros e inconsistências
- Cada DTO reinventa a roda com lógica diferente
- Sem integração com o sistema de validação do Symfony
- `empty()` não distingue campo ausente de campo vazio string
- `file_get_contents('php://input')` como fallback é inseguro e difícil de testar

**Como melhorar:** Usar `Symfony\Component\Validator\Constraints` nos DTOs com atributos (`#[Assert\NotBlank]`, `#[Assert\Email]`, etc.) e o `ValidatorInterface` nos controllers. Isso centraliza regras, gera mensagens padronizadas e permite validação automática via `$validator->validate()`.

#### 1.2 Senhas fracas permitidas

**O que está:** `RegisterRequest` exige `strlen($password) >= 6`.

**Problema:** 6 caracteres é insuficiente para produção. Sem requisito de complexidade.

**Como melhorar:** Mínimo 8 caracteres, com pelo menos 1 maiúscula, 1 número e 1 caractere especial. Implementar verificação contra lista de senhas comuns vazadas (HaveIBeenPwned API ou lista local).

#### 1.3 JwtService — sem expiração configurável no encode

**O que está:** `JwtService::encodeToken()` gera token com `expiresAt(new DateTimeImmutable('+7 days'))` hardcoded.

**Problema:** Expiração fixa de 7 dias sem configuração. Sem refresh token — quando o JWT expira, o usuário precisa logar novamente.

**Como melhorar:** Tornar TTL configurável via parâmetro. Implementar refresh token com endpoint dedicado `/api/auth/refresh`. Armazenar refresh tokens no banco com invalidação por rotação.

#### 1.4 EntityValidator manual duplica validação do Symfony

**O que está:** `EntityValidator` valida CPF, CEP, email, telefone manualmente com regex e algoritmos próprios.

**Problema:**
- Duplica funcionalidade que o Symfony Validator já oferece
- Validações manuais são mais propensas a bugs
- O Validator do Symfony integra com formulários, APIs e gera violações padronizadas

**Como melhorar:** Migrar para `#[Assert\Cpf]` (custom constraint), `#[Assert\Email]`, `#[Assert\Length]`, `#[Assert\Regex]` diretamente nas entities. Manter apenas validações de domínio complexas (ex: CPF check digits) como constraints customizadas.

#### 1.5 Sem rate limiting na API

**O que está:** Nenhum mecanismo de rate limiting configurado.

**Problema:** Endpoints de login e register são vulneráveis a brute force e criação massiva de contas.

**Como melhorar:** Usar `symfony/rate-limiter` com configuração em `config/packages/rate_limiter.yaml`. Limitar login a 5 tentativas/minuto por IP, register a 3 por hora por IP.

---

### PRIORIDADE 2 — Alto (arquitetura e manutenibilidade)

#### 2.1 Controllers fazem trabalho demais

**O que está:** Controllers como `AssinaturaController`, `PedidoController`, `CartController` contêm lógica de negócio, validação, manipulação de entidades e formatação de resposta.

**Problema:** Controllers devem orquestrar, não executar lógica de domínio. Difícil de testar unitariamente, difícil de reutilizar.

**Como melhorar:** Mover validações complexas para services. Controllers devem apenas: receber request → delegar para service → retornar response. Exemplo: `CartService::adicionarKit()`, `PedidoService::finalizar()`.

#### 2.2 Services sem testes

**O que está:** `KitService`, `AuthService`, `JwtService`, `CartService`, `PedidoService`, `AssinaturaService` — nenhum possui teste unitário correspondente.

**Problema:** Lógica de negócio (cálculo de margem de lucro, validação de CPF, geração de JWT) sem cobertura de testes. Bugs em produção são descobertos tarde.

**Como melhorar:** Criar testes unitários para cada service, mockando repositories. Testar casos de erro (kit inexistente, CPF inválido, token expirado).

#### 2.3 Exception handling inconsistente

**O que está:** Alguns services lançam `InvalidArgumentException`, outros `ValidationException`, outros retornam `null`. Controllers nem sempre tratam exceções.

**Problema:** APIs retornam 500 genéricos em vez de 400/404 apropriados. O cliente não sabe o que errou.

**Como melhorar:** Criar exceptions tipadas (`ResourceNotFoundException`, `BusinessRuleException`, `AuthenticationException`) com um `ExceptionListener` que converte para JSON responses padronizadas: `{ "error": { "code": 400, "message": "..." } }`.

#### 2.4 Sem paginação nas listagens

**O que está:** `KitController::index`, `ProductController::index`, `CategoryController::index` retornam todos os registros sem limite.

**Problema:** Performance degrada com crescimento do catálogo. Memory limit do PHP pode ser atingido.

**Como melhorar:** Usar `Pagerfanta` (bundle Symfony) ou paginação manual com `OFFSET/LIMIT`. Adicionar parâmetros `?page=1&limit=20` nas rotas.

#### 2.5 N+1 queries em listagens de Kit

**O que está:** `KitController` lista kits e depois itera sobre produtos de cada kit no template.

**Problema:** Cada kit gera queries adicionais para carregar produtos e categorias (relacionamentos ManyToMany com lazy loading).

**Como melhorar:** Usar `JOIN` no repository (`findDisponiveisComProdutos()`) com `fetch="EAGER"` ou query explícita com `leftJoin('k.produtos', 'p')->addSelect('p')`.

---

### PRIORIDADE 3 — Médio (qualidade e DX)

#### 3.1 Frontend sem build tool

**O que está:** CSS inline nos templates Twig, JavaScript vanilla inline. Sem Webpack, Vite, Encore ou similar.

**Problema:** Sem minificação, sem cache busting, sem organização de assets, sem hot reload em dev.

**Como melhorar:** Instalar `symfony/ux` + `@symfony/stimulus-bundle` + `Vite` ou `Webpack Encore`. Separar CSS em arquivos dedicados com design tokens centralizados.

#### 3.2 Sem API versionamento

**O que está:** Rotas `/api/auth/*`, `/api/kit/*` sem prefixo de versão.

**Problema:** Mudanças breaking na API quebram clientes mobile ou third-party.

**Como melhorar:** Adicionar prefixo `/api/v1/` em todas as rotas. Configurar routing com `prefix: /api/v1`. Quando houver mudança breaking, criar `/api/v2/`.

#### 3.3 Dockerfile usa built-in server PHP

**O que está:** `CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]`

**Problema:** Servidor built-in do PHP não é adequado para produção. Sem worker pool, sem keepalive eficiente, sem HTTPS nativo.

**Como melhorar:** Usar Nginx ou Caddy como reverse proxy + PHP-FPM. Ou migrar para FrankenPHP para produção (single binary, HTTP/2, early hints).

#### 3.4 Sem CI/CD pipeline

**O que está:** Nenhum arquivo `.github/workflows/`, `.gitlab-ci.yml`, ou similar.

**Problema:** Deploys manuais, sem testes automáticos, sem linting no pipeline.

**Como melhorar:** Criar workflow GitHub Actions com: `phpstan analyse`, `php-cs-fixer --dry-run`, `rector --dry-run`, `phpunit`, `doctrine:migrations:validate`. Rodar em todo push para `Dev` e `main`.

#### 3.5 Validação de CPF apenas no validator manual

**O que está:** `EntityValidator::validateCliente()` valida CPF com `isValidCpf()`, mas controllers de registro não chamam esse validator.

**Problema:** CPF inválido pode ser persistido se o controller não chamar o validator explicitamente.

**Como melhorar:** Usar Doctrine lifecycle events (`@PrePersist`) ou validator do Symfony nas entities para garantir que CPF seja validado antes de persistir, independente do ponto de entrada.

#### 3.6 Senhas armazenadas com hasherto padrão do Symfony

**O que está:** `User::hashPassword()` usa `UserPasswordHasherInterface`.

**Problema:** Isso é correto, mas o algoritmo não é explicitamente configurado. O padrão `auto` usa bcrypt, que é aceitável mas não ótimo.

**Como melhorar:** Configurar `argon2id` explicitamente em `security.yaml` — é o algoritmo recomendado pela OWASP atualmente.

---

### PRIORIDADE 4 — Baixo (nice-to-have)

#### 4.1 Sem cache HTTP

**O que está:** Nenhuma configuração de cache (ETag, Last-Modified, Cache-Control).

**Problema:** Páginas como lista de kits e produtos são re-renderizadas a cada request.

**Como melhorar:** Usar `Symfony\Component\HttpKernel\Attribute\Cache` nos controllers para listagens. Configurar Varnish ou CDN para assets estáticos.

#### 4.2 Sem logs estruturados

**O que está:** Monolog padrão do Symfony loga em texto.

**Problema:** Difícil de analisar logs em produção, integrar com ELK, Datadog, etc.

**Como melhorar:** Configurar Monolog para output JSON em produção. Adicionar correlation IDs por request.

#### 4.3 Sem health check endpoint

**O que está:** Nenhum endpoint `/health` ou `/ready`.

**Problema:** Orquestradores (Kubernetes, Docker Swarm) não têm como verificar se a app está viva.

**Como melhorar:** Criar `HealthController` com checks: database connection, disk space, memory usage.

#### 4.4 Templates Twig sem blocos de cache

**O que está:** Templates renderizam tudo dinamicamente a cada request.

**Problema:** Header e footer são idênticos em todas as páginas mas são re-renderizados.

**Como Melhorar:** Usar `{% cache %}` do Twig para blocos estáticos (header, footer, categorias sidebar). Invalidar cache quando dados mudam.

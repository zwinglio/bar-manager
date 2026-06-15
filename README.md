# Sistema de Gerenciamento de Bares e Restaurantes

Aplicativo web para gerenciamento de bares e restaurantes, com painel administrativo, painel para donos de restaurante e interface mobile para garçons.

## Tecnologias

- **Backend:** PHP 8.4, Laravel 13, Filament v5, Livewire v4
- **Frontend Admin:** Filament + Tailwind CSS v4
- **Frontend Garçom:** Vue 3, Inertia.js, Vuetify 4, Material Design 3
- **Banco de dados:** SQLite (padrão)
- **Autenticação:** Laravel Auth + Spatie Laravel Permission
- **Ferramentas:** Laravel Pint, PHPUnit 12, Laravel Boost

## Estrutura do Sistema

O sistema possui três interfaces principais:

### 1. Painel Administrativo (`/admin`)

Painel Filament para administradores do sistema.

- Gerenciamento de restaurantes
- Gerenciamento de usuários (administradores e donos de restaurante)
- Controle de acesso via papéis (roles) `admin` e `restaurant`

### 2. Painel do Restaurante (`/restaurant`)

Painel Filament exclusivo para o dono do restaurante autenticado.

- **Mesas:** Cadastro e controle de mesas do restaurante
- **Produtos:** Cardápio com categorias, fotos, preços, custos e disponibilidade
- **Categorias:** Organização do cardápio em categorias
- **Garçons:** Cadastro de garçons com credenciais de acesso à interface mobile
- **Controle de mesas:** Abertura/fechamento de mesas e acompanhamento de pedidos

### 3. Interface do Garçom (`/waiter/{restaurant-slug}`)

Interface mobile desenvolvida com Vue 3 + Vuetify para uso dos garçons.

- Login com usuário e senha
- Visualização de todas as mesas do restaurante
- Abertura de novas mesas (informando número de pessoas)
- Adição e remoção de produtos à mesa
- Acompanhamento do total em tempo real
- Fechamento da conta

## Modelos Principais

| Modelo | Descrição |
|--------|-----------|
| `User` | Administradores e donos de restaurante (autenticação Filament) |
| `Restaurant` | Dados do restaurante (nome, endereço, telefone, etc.) |
| `Product` | Itens do cardápio (nome, descrição, foto, preço, custo) |
| `ProductCategory` | Categorias do cardápio (ex: Bebidas, Porções, etc.) |
| `RestaurantTable` | Mesas do restaurante com controle de abertura/fechamento |
| `RestaurantTableProduct` | Itens pedidos em uma mesa (quantidade, preço unitário) |
| `Waiter` | Garçons com autenticação independente para a interface mobile |

## Requisitos

- PHP >= 8.3
- Composer
- Node.js / Bun
- SQLite (ou outro banco configurado no `.env`)

## Instalação

```bash
# Clone o repositório
git clone <repo-url>
cd bar-management-filament

# Instale as dependências PHP
composer install

# Configure o ambiente
cp .env.example .env
php artisan key:generate

# Execute as migrations
php artisan migrate

# Instale as dependências frontend
npm install

# Compile os assets
npm run build
```

Ou utilize o script de setup do Composer:

```bash
composer run setup
```

## Desenvolvimento

```bash
# Inicie o servidor de desenvolvimento completo
composer run dev
```

Este comando inicia simultaneamente:
- Servidor PHP (`php artisan serve`)
- Worker de filas (`php artisan queue:listen`)
- Logs em tempo real (`php artisan pail`)
- Vite dev server (`npm run dev`)

## Testes

```bash
# Execute todos os testes
php artisan test --compact

# Execute um teste específico
php artisan test --compact --filter=nomeDoTeste
```

## Rotas Principais

| Rota | Descrição |
|------|-----------|
| `/admin` | Painel administrativo (role: admin) |
| `/admin/login` | Login do administrador |
| `/restaurant` | Painel do restaurante (role: restaurant) |
| `/restaurant/login` | Login do dono do restaurante |
| `/waiter/{restaurant}` | Login da interface do garçom |
| `/waiter/{restaurant}/tables` | Lista de mesas (garçom autenticado) |
| `/waiter/{restaurant}/tables/{id}` | Detalhes e pedidos de uma mesa |

## Principais Funcionalidades

- **Multi-tenant:** Cada restaurante gerencia apenas seus próprios dados
- **Autenticação em camadas:** Admin, dono de restaurante e garçom com sistemas de auth separados
- **Interface mobile otimizada:** Design responsivo com Material Design 3 para uso em tablets e smartphones
- **Controle de estoque:** Produtos com marcação de disponibilidade e custo
- **Acompanhamento de vendas:** Total por mesa e histórico de fechamentos

## Licença

MIT

<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Categoria;
use App\Entity\Cliente;
use App\Entity\Pet;
use App\Entity\Produto;
use App\Entity\User;
use App\Enum\PetType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // === Categorias ===
        $categorias = [];
        $catsData = [
            ['Alimentos', 'Racoes, petiscos e suplementos para seu pet'],
            ['Brinquedos', 'Diversao garantida para caes e gatos'],
            ['Acessorios', 'Coleiras, guias, caminhas e mais'],
            ['Higiene', 'Shampoos, tapetes higienicos e limpeza'],
            ['Kits Surpresa', 'Caixas personalizadas com mimos para seu pet'],
        ];

        foreach ($catsData as [$nome, $descricao]) {
            $categoria = new Categoria();
            $categoria->setNome($nome);
            $categoria->setDescricao($descricao);
            $manager->persist($categoria);
            $categorias[$nome] = $categoria;
        }

        // === Produtos ===
        $produtosData = [
            ['Racao Premium Caes Adultos 15kg', 'Racao seca de alta qualidade para caes adultos de porte medio.', 89.90, 189.90, 'Alimentos', 150],
            ['Petiscos Naturais para Gatos', 'Petiscos 100% naturais feitos com salmao desidratado.', 12.90, 29.90, 'Alimentos', 300],
            ['Bola Resistente para Caes', 'Bola de borracha ultra resistente para caes de porte medio e grande.', 19.90, 49.90, 'Brinquedos', 200],
            ['Ratinho de Pelucia para Gatos', 'Brinquedo em formato de rato com catnip natural.', 8.90, 19.90, 'Brinquedos', 400],
            ['Coleira Ajustavel Premium', 'Coleira de nylon com fecho de metal e ajuste de 30 a 50cm.', 18.90, 39.90, 'Acessorios', 250],
            ['Cama Ortopedica para Pets', 'Cama com espuma viscoelastica para caes idosos. Tamanho M.', 79.90, 159.90, 'Acessorios', 80],
            ['Shampoo Neutro Caes e Gatos', 'Shampoo hipoalergenico com pH neutro. 500ml.', 14.90, 34.90, 'Higiene', 180],
            ['Tapete Higienico 30 unid.', 'Tapete higienico super absorvente com 60x60cm.', 29.90, 59.90, 'Higiene', 500],
            ['Kit Surpresa Caes Pequenos', 'Caixa com 5 itens surpresos para caes de porte pequeno.', 44.90, 89.90, 'Kits Surpresa', 100],
            ['Kit Surpresa Gatos', 'Caixa com 4 itens surpresos para gatos.', 39.90, 79.90, 'Kits Surpresa', 120],
            ['Kit Surpresa Caes Grandes', 'Caixa com 6 itens surpresos para caes de porte grande.', 64.90, 129.90, 'Kits Surpresa', 60],
        ];

        foreach ($produtosData as [$nome, $descricao, $precoCusto, $precoVenda, $catNome, $qtd]) {
            $produto = new Produto();
            $produto->setNome($nome);
            $produto->setDescricao($descricao);
            $produto->setPrecoCusto($precoCusto);
            $produto->setPrecoVenda($precoVenda);
            $produto->setEstoque($qtd);
            $produto->setCategoria($categorias[$catNome]);
            $manager->persist($produto);
        }

        // === Usuario de teste ===
        $user = new User();
        $user->setFirstName('Gabriel');
        $user->setLastName('Silva');
        $user->setEmail('gabriel@teste.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $manager->persist($user);

        $cliente = new Cliente();
        $cliente->setUser($user);
        $cliente->setCpf('12345678901');
        $manager->persist($cliente);

        // === Pets de teste ===
        $pet1 = new Pet();
        $pet1->setNome('Rex');
        $pet1->setCliente($cliente);
        $pet1->setTipo(PetType::DOG);
        $pet1->setRaca('Golden Retriever');
        $pet1->setPeso(30.0);
        $pet1->setIdadeMeses(36);
        $manager->persist($pet1);

        $pet2 = new Pet();
        $pet2->setNome('Mimi');
        $pet2->setCliente($cliente);
        $pet2->setTipo(PetType::CAT);
        $pet2->setRaca('Siames');
        $pet2->setPeso(4.5);
        $pet2->setIdadeMeses(24);
        $manager->persist($pet2);

        $manager->flush();
    }
}

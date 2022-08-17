# Bancos Brasil Value Object

Biblioteca PHP que fornece o objeto de valor `Banco` para representar bancos brasileiros.

As instâncias criadas são sempre válidas, criadas e verificadas a partir da lista de bancos incluída pela biblioteca.

## Roadmap

Projeto está em construção.

Esses são os itens que vou trabalhar nos próximos dias em meu tempo livre:

- [x] classe Banco
  - [ ] testes unitários
- lista de bancos
  - [ ] script para gerar a lista estática
- integração com Doctrine
  - [ ] tipo Doctrine DBAL + teste
  - [ ] entidade + fábrica que carrega dados do banco de dados
  - [ ] migrations?
- integração com Symfony Framework
  - [ ] Symfony Validator Constraint: IsBank() + teste
  - [ ] Symfony Form Type: BancoType + teste
- [ ] CI

## Funcionalidades

@TODO: Descrever.

### Integração com Doctrine

A biblioteca vem com um tipo Doctrine DBAL que permite mapear o objeto de valor `Banco` como um atributo de uma entidade
Doctrine.

## Requisitos

A biblioteca exige PHP >= 8.1.

> Não tenho a menor intenção em suportar versões antigas.

## Instalação

Use [composer](https://getcomposer.org/) para instalar a biblioteca:

```bash
composer install xthiago/bancos-brasil-value-object
```

### Doctrine

Para usar a integração com Doctrine você precisa configurar o tipo Doctrine DBAL oferecido por este pacote.

#### Standalone Doctrine

Você deve registrar o tipo DBAL no bootstrap da sua aplicação conforme a seguir:

```php
<?php

\Doctrine\DBAL\Types\Type::addType(
    'xthiago_banco_brasil',
    \Xthiago\ValueObject\BancosBrasil\Persistence\DoctrineDbalType::class
);
```

#### Symfony Framework

Se você está usando Symfony Framework, você apenas precisa editar a configuração do Doctrine e incluir o seguinte:

```yaml
doctrine:
  dbal:
    types:
      xthiago_banco_brasil: Xthiago\ValueObject\BancosBrasil\Persistence\DoctrineDbalType
```

## Uso

Exemplos básicos:

```php
<?php
namespace YourApp;

use Xthiago\ValueObject\BancosBrasil\Banco;

// Criar a partir de um código de banco.
$banco = Banco::fromCode(237);
echo $banco->nome; // imprime: `Banco Bradesco S.A`.

// Lança erro se código não existir:
try {
    $codigoQueNaoExiste = 9999;
    $bancoOuNull = Banco::fromString($codigoQueNaoExiste);
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // imprime: `Banco inexistente`.
}

// Ou use um construtor que retorna null:
$codigoQueNaoExiste = 9999;
$bancoOuNull = Banco::tryFromString($codigoQueNaoExiste);
var_dump($bancoOuNull); // imprime: `null`.
````

Mapeando um atributo com tipo `Banco` em sua entidade (exemplo: `Conta`):

```php
<?php
namespace YourApp;

use Doctrine\ORM\Mapping as ORM;
use Xthiago\ValueObject\BancosBrasil\Banco;

/**
 * @ORM\Entity()
 */
class Conta
{
    public function __construct(
        /**
         * @ORM\Column(type="xthiago_banco_brasil", name="banco")
         */
        private Banco $banco,

        // Outros atributos.
    ) {
    }

    public function banco(): Banco
    {
        return $this->banco;
    }
}
```

## Contribuindo

Pull requests são bem vindas. Para refatorações maiores, por favor abra uma issue antes para discutirmos a alteração.

Certifique-se de atualizar os testes.

## Licença

[MIT](LICENSE)

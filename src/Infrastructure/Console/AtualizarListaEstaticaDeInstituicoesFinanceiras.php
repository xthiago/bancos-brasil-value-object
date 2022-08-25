<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil\Infrastructure\Console;

use DateTimeImmutable;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xthiago\ValueObject\BancosBrasil\InstituicaoFinanceira;
use Xthiago\ValueObject\BancosBrasil\ISPB;
use Xthiago\ValueObject\BancosBrasil\NumeroCodigo;

#[AsCommand(
    name: 'instituicoes:atualizar',
    description: 'Atualiza a lista estática de instituições financeiras.',
)]
class AtualizarListaEstaticaDeInstituicoesFinanceiras extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //load the CSV document from a file path
        $csv = Reader::createFromPath(__DIR__ . '/../../../resources/ParticipantesSTRport.csv', 'r');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();
        $records = $csv->getRecords();

        $instituicoes = [];
        foreach ($records as $record) {
            $instituicoes[] = InstituicaoFinanceira::fromFactory(
                $record['Número_Código'] !== 'n/a' ? NumeroCodigo::fromString($record['Número_Código']) : null,
                ISPB::fromString($record['ISPB']),
                $record['Nome_Extenso'],
                $record['Nome_Reduzido'],
                $record['Participa_da_Compe'] === 'Sim',
                $record['Acesso_Principal'],
                DateTimeImmutable::createFromFormat('d/m/Y', $record['Início_da_Operação']),
            );
        }
        $a = 'b';
        //echo $csv->toString(); //returns the CSV document as a string

        return Command::SUCCESS;
    }
}

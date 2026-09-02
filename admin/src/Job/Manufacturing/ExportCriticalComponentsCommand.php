<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'manufacturing:critical-components')]
class ExportCriticalComponentsCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Export Critical Components (400 only)';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = 'SELECT
                itm001.t_item,
                itm001.t_dsca,
                itm001.t_csig,
                itm001.t_suno,
                itm001.t_kitm,
                itm001.t_stoc,
                itm001.t_sfst,
                itm001.t_ordr,
                itm001.t_allo,
                itm001.t_cuni,
                itm001.t_cwar,
                itm001.t_citg,
                itm001.t_cpcp,
                itm001.t_copr,
                itm001.t_pics,
                itm001.t_osys,
                itm001.t_cpha,
                com001.t_namb,
                itm001.t_oltm,
                itm001.t_crmp,
                com020.t_nama
            FROM
                ttiitm001400 itm001,
                ttccom001400 com001,
                ttccom020400 com020
            WHERE
                itm001.t_buyr=com001.t_emno AND
                itm001.t_suno=com020.t_suno AND
                itm001.t_crmp = 1 AND
                itm001.t_kitm = 1';

        $csv = '';
        $csv .= '"Item";';
        $csv .= '"Description";';
        $csv .= '"Sig Code";';
        $csv .= '"Suppl";';
        $csv .= '"Kind";';
        $csv .= '"On hand";';
        $csv .= '"Safe";';
        $csv .= '"On order";';
        $csv .= '"Alloc";';
        $csv .= '"Unit";';
        $csv .= '"Wareh";';
        $csv .= '"Itm grp";';
        $csv .= '"Cost Price Comp";';
        $csv .= '"Std Cost";';
        $csv .= '"Consum.";';
        $csv .= '"Order Sys";';
        $csv .= '"Phan";';
        $csv .= '"Byr";';
        $csv .= '"LT";';
        $csv .= '"Critical component status";';
        $csv .= '"Suppl name";';
        $csv .= \chr(13);

        $rows = \tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

        foreach ($rows as $value1) {
            $csv .= '"'.$value1['t_item'].'";';
            $csv .= '"'.$value1['t_dsca'].'";';
            $csv .= '"'.$value1['t_csig'].'";';
            $csv .= '"'.$value1['t_suno'].'";';
            $csv .= '"'.$value1['t_kitm'].'";';
            $csv .= '"'.$value1['t_stoc'].'";';
            $csv .= '"'.$value1['t_sfst'].'";';
            $csv .= '"'.$value1['t_ordr'].'";';
            $csv .= '"'.$value1['t_allo'].'";';
            $csv .= '"'.$value1['t_cuni'].'";';
            $csv .= '"'.$value1['t_cwar'].'";';
            $csv .= '"'.$value1['t_citg'].'";';
            $csv .= '"'.$value1['t_cpcp'].'";';
            $csv .= '"'.$value1['t_copr'].'";';
            $csv .= '"'.$value1['t_pics'].'";';
            $csv .= '"'.$value1['t_osys'].'";';
            $csv .= '"'.$value1['t_cpha'].'";';
            $csv .= '"'.$value1['t_namb'].'";';
            $csv .= '"'.$value1['t_oltm'].'";';
            $csv .= '"'.$value1['t_crmp'].'";';
            $csv .= '"'.$value1['t_nama'].'";';
            $csv .= \chr(13);
        }
        $filename = '/mnt/baanivc4.www-rw//CriticalComponents/CriticalComponents400.csv';
        $file = fopen($filename, 'w');
        fwrite($file, $csv, mb_strlen($csv));
        fclose($file);

        $this->logger->info(\sprintf('File %s updated', $filename));

        return Command::SUCCESS;
    }
}

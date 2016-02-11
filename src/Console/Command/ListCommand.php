<?php
/**
 * Created by PhpStorm.
 * User: antonpauli
 * Date: 11/02/16
 * Time: 12:39
 */

namespace IronShark\Typo3ConfigurationManager\Console\Command;


use Riimu\Kit\PHPEncoder\PHPEncoder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IronShark\Typo3ConfigurationManager\Helper;

class ListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('config:list')
            ->setDescription('Display all configurations as flat key / value list')
            ->addOption(
                'source-file',
                's',
                InputOption::VALUE_OPTIONAL,
                'Source config-file path',
                realpath(__DIR__ . "/../../typo3conf/LocalConfiguration.php")
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getOption('source-file');

        // check file
        if(!file_exists($configFile)){
            $output->writeln('<error>Config file not found at path: '.$configFile.'</error>');
            exit(1);
        }

        $encoder = new PHPEncoder();
        $config = include $configFile;
        $flatConfig = Helper::dot(Helper::unserializeRecursive($config));

        foreach($flatConfig as $path => $value){
            $value = $encoder->encode($value);
            $output->writeln(sprintf('<info>%s => %s</info>', $path, $value));
        }
    }
}
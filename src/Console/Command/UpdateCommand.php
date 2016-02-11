<?php
/**
 * Created by PhpStorm.
 * User: antonpauli
 * Date: 11/02/16
 * Time: 13:07
 */

namespace IronShark\Typo3ConfigurationManager\Console\Command;

use Riimu\Kit\PHPEncoder\PHPEncoder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IronShark\Typo3ConfigurationManager\Helper;

class UpdateCommand  extends Command
{
    protected function configure()
    {
        $this
            ->setName('config:update')
            ->setDescription('Replace values in LocalConfiguration.php')
            ->addOption(
                'source-file',
                's',
                InputOption::VALUE_OPTIONAL,
                'Source config-file path',
                realpath(__DIR__ . "/../../typo3conf/LocalConfiguration.php")
            )
            ->addOption(
                'destination-file',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Destination config-file path, source file will be overwritten if no destination provided'
            )
            ->addOption(
                'value-file',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Path to file with new values'
            )
            ->addOption(
                'value-json',
                'j',
                InputOption::VALUE_OPTIONAL,
                'New values as JSON'
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Path for single element configuration e.g EXT.extConf.sfpmedialibrary.apiUrl'
            )
            ->addOption(
                'value',
                null,
                InputOption::VALUE_OPTIONAL,
                'Value for single element configuration e.g https://api.tld'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getOption('source-file');
        $newConfigFile = $input->getOption('destination-file') ?: $configFile;
        $valueFile = $input->getOption('value-file');
        $valueJson = $input->getOption('value-json');
        $singlePath = $input->getOption('path');
        $singleValue = $input->getOption('value');

        // check file
        if(!file_exists($configFile)){
            $output->writeln('<error>Config file not found at path: '.$configFile.'</error>');
            exit(1);
        }

        // destination file is writeable?
        if(file_exists($newConfigFile) && !is_writable($newConfigFile)){
            $output->writeln('<error>Config file id not writable: '.$newConfigFile.'</error>');
            exit(1);
        }

        // check value file if given
        if($valueFile && !file_exists($valueFile)){
            $output->writeln('<error>File with values was not found at path: '.$valueFile.'</error>');
            exit(1);
        }

        if(!$singlePath && $singleValue){
            $output->writeln('<error>Please provide path for following value: '.$singleValue.'</error>');
            exit(1);
        }

        // check value json
        if($valueJson && !isJson($valueJson)){
            $output->writeln('<error>New values JSON is invalid: '.$valueJson.'</error>');
            exit(1);
        }

        // no values provided
        if((!$valueFile && !$valueJson) && !$singlePath) {
            $output->writeln('<error>At least one value input type should be provided, use command -h to display usage information</error>');
            exit(1);
        }

        // load value map
        $valueMap = [];
        $encoder = new PHPEncoder();

        if($singlePath) {
            $valueMap[$singlePath] = $singleValue;
        } else {
            $valueMap = $valueJson ? json_decode($valueJson) : include $valueFile;
        }

        $config = include $configFile;

        // handle all map items
        foreach($valueMap as $path => $value){
            $oldValue = Helper::get(Helper::unserializeRecursive($config), $path);

            $output->writeln(sprintf(
                'Replace value at path: <info>%s: %s => %s</info>',
                $path,
                $encoder->encode($oldValue),
                $encoder->encode($value)
            ));
            $config = Helper::arrayPathReplace($config, $path, $value);
        }

        // export to file

        // get file content
        $fileContent = file_get_contents($configFile);

        // keep head, remove old return values
        $fileContent = strstr($fileContent, "return ", true);

        // add new data
        $fileContent .= "return " . $encoder->encode($config) . ";";

        // write file
        if(file_put_contents($newConfigFile, $fileContent) === false){
            $output->writeln('<error>Unable to write file: '.$newConfigFile.'</error>');
            exit(1);
        }
    }
}
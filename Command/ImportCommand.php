<?php

namespace Pumukit\ImportDataBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pumukit\CoreBundle\Services\ImportMappingDataService;
use Pumukit\SchemaBundle\Services\FactoryService;
use Pumukit\SchemaBundle\Services\SeriesPicService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ImportCommand extends Command
{
    private $seriesPic = 'https://replay.teltek.es/uploads/pic/5ce7f712cc464671048b45b0/logo_picture.png';
    private $documentManager;
    private $file;
    private $importMappingDataService;
    private $factoryService;
    private $seriesPicService;

    public function __construct(DocumentManager $documentManager, FactoryService $factoryService, SeriesPicService $seriesPicService, ImportMappingDataService $importMappingDataService)
    {
        $this->documentManager = $documentManager;
        $this->factoryService = $factoryService;
        $this->seriesPicService = $seriesPicService;
        $this->importMappingDataService = $importMappingDataService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('pumukit:data:import')
            ->setDescription('Import MultimediaObject JSON data to PuMuKIT')
            ->addOption('file', 'S', InputOption::VALUE_REQUIRED, 'Path of JSON file')
            ->setHelp(
                <<<'EOT'

            Examples:
            php app/console pumukit:data:import --file="{path}/{file}.json"

EOT
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->file = $input->getOption('file');
        if (!$this->file) {
            throw new \Exception('File option must be defined');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->importMappingDataService->validatePath($this->file)) {
            throw new FileNotFoundException('File not found');
        }

        $output->writeln(' ***** Import demo multimedia objects ***** ');
        $fileContent = $this->importMappingDataService->processFileData($this->file);

        $seriesTitle = 'Demo '.str_replace('_', ' ', strtoupper(pathinfo($this->file, PATHINFO_FILENAME)));
        $seriesTitle = [
            'en' => $seriesTitle,
            'es' => $seriesTitle,
        ];

        $series = $this->factoryService->createSeries(null, $seriesTitle);

        $this->seriesPicService->addPicUrl($series, $this->seriesPic);

        $output->writeln('Series '.$series->getTitle());

        foreach ($fileContent as $dataElement) {
            $multimediaObject = $this->factoryService->createMultimediaObject($series);
            $multimediaObject->setProperty('demo', true);

            $this->importMappingDataService->insertMappingData($multimediaObject, $dataElement);
            $output->writeln('   '.$multimediaObject->getTitle());
        }

        $this->documentManager->flush();
        
        return 0;
    }
}

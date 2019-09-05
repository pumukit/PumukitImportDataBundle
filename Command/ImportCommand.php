<?php

namespace Pumukit\ImportDataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ImportCommand extends ContainerAwareCommand
{
    private $seriesPic = 'https://replay.teltek.es/uploads/pic/5ce7f712cc464671048b45b0/logo_picture.png';
    private $documentManager;
    private $file;
    private $importMappingDataService;
    private $factoryService;
    private $seriesPicService;

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

    /**
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->documentManager = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $this->file = $input->getOption('file');
        if (!$this->file) {
            throw new \Exception('File option must be defined');
        }
        $this->importMappingDataService = $this->getContainer()->get('pumukitcore.import_mapping_data');
        $this->factoryService = $this->getContainer()->get('pumukitschema.factory');
        $this->seriesPicService = $this->getContainer()->get('pumukitschema.seriespic');
    }

    /**
     * @throws \ErrorException
     * @throws \Exception
     */
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
    }
}

<?php

namespace Shovel\Commands;

use Shovel\Bystander;
use Shovel\Instructions;
use Shovel\Message;
use Shovel\Messages\DigProgress;
use Shovel\Messages\PreDiggingStatusReport;
use Shovel\Shoveler;
use Shovel\ShovelFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class Dig extends Command implements Bystander
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Shoveler
     */
    private $shoveler;

    protected function configure()
    {
        $this
            ->setName('dig')
            ->setDescription('Shits the bed.')
            ->addArgument('instructions', InputArgument::REQUIRED, 'Instructions to provide to shoveler in YAML format.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('OH LAWD ITS THE DIGGER');
        $this->shoveler = $this->getShoveler($input);
        $this->shoveler->addBystander($this);
        $this->shoveler->pickUpShovels();

        $this->io->section('THE BREAKING OF GROUNDS');

        if ($this->shoveler->hasBrokenGround() === false) {
            $breakIt = $this->io->confirm('Tables have not been created in destination. Dig dug?', false);

            if (! $breakIt) {
                $this->io->error('WELL I AM JUST GOING TO LEAVE THEN SINCE IM NOT NEEDED');
                exit(1);
            }

            $this->shoveler->breakGround();

            $this->io->success('I guess I broke the grounds.');
        } else {
            $this->io->writeln('I didn\'t have any ground to break. Sad day.');
        }

        $this->io->section('THE DIGGENING BEGINS');

        $this->shoveler->dig();
        $this->io->newLine(2);

        $this->io->section('DARKNESS FALLS');
        $this->io->success('We dug real good.');
    }

    public function handlePreDiggingStatusReport(PreDiggingStatusReport $pile)
    {
        $this->io->writeln(sprintf("The source pile has %s very large vegetables in it. Gross.", $pile->sourcePileSize));
        $this->io->writeln(sprintf("The destination pile has %s concerns. Very nice.", $pile->destPileSize));

        $this->io->newLine();
        $this->io->progressStart($pile->sourcePileSize);
    }

    public function handleDigProgress(DigProgress $progress)
    {
        $this->io->progressAdvance(1);
    }

    public function tell(Message $message)
    {
        $method = 'handle' . class_basename($message);

        if (! method_exists($this, $method)) {
            return;
        }

        $this->{$method}($message);
    }

    private function getShoveler(InputInterface $input)
    {
        $instructions = new Instructions(
            Yaml::parse(
                file_get_contents(realpath($input->getArgument('instructions')))
            )
        );

        return new Shoveler($instructions, new ShovelFactory());
    }
}
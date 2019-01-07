<?php

/**
 * Description of TranslationCommand
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationCommand extends Command{
    
    protected static $defaultName = 'kematjaya:translation:configure';
    
    protected function configure()
    {
        $this->setDescription('setting default translation configure');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("configure translation.");
    }
}

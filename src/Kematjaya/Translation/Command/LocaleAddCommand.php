<?php

/**
 * Description of LocaleAddCommand
 *
 * @author Nur Hidayatullah <kematjaya0@gmail.com>
 */

namespace Kematjaya\Translation\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Intl\Intl;

class LocaleAddCommand extends Command {
    
    protected static $defaultName = 'kematjaya:translation:add-locale';
    
    private $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setDescription('add locale');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('add locale for language');
        $code = $io->ask('locale code (separate with comma "," if more than one, ex: en, fr)', 'en');
        $locale = Intl::getLocaleBundle()->getLocales();
        $codes = explode(",", $code);
        $locale_user = array();
        foreach($codes as $code){
            $code = trim($code);
            if(!in_array($code, $locale)) {
                $io->error(sprintf("locale code '%s' is not exists in list", $code));
                die();
            }
            $locale_user[] = $code;
        }
        
        $kernel = $this->container->get('kernel');
        $servicesSources = $kernel->getProjectDir(). '/config/services.yaml';
        $services = Yaml::parseFile($servicesSources);
        if(!isset($services["parameters"]["locale_supported"])) {
            $services["parameters"]["locale_supported"] = $locale_user;
            $services['parameters']['app_locales'] = implode("|", $locale_user);
        } else {
            $locales = array_unique(array_merge($locale_user, $services["parameters"]["locale_supported"]));
            $services["parameters"]["locale_supported"] = $locales;
            $services['parameters']['app_locales'] = implode("|", $locales);
        }
        
        $yaml = Yaml::dump($services);
        file_put_contents($servicesSources, $yaml);
        $output->writeln("====== clearing cache. ======");
        $command = $this->getApplication()->find('cache:clear');
        $arguments = array(
            'command' => 'cache:clear'
        );

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);
            
        $io->note("add locale successfully");
    }
    
}

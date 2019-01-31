<?php

/**
 * Description of LocaleConfigureCommand
 *
 * @author Nur Hidayatullah <kematjaya0@gmail.com>
 */

namespace Kematjaya\Translation\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class LocaleConfigureCommand extends Command {
    
    protected static $defaultName = 'kematjaya:translation:configure';
    
    private $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setDescription('configure for translation.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('configure kematjaya translation.');
        $kernel = $this->container->get('kernel');
        
        $progress = new ProgressBar(clone $output->section());
        $progress->start(100);
        $cicle = 100 / 4;
        // 1. update services.yml
        $servicesSources = $kernel->getProjectDir(). '/config/services.yaml';
        $services = Yaml::parseFile($servicesSources);
        if(!isset($services["parameters"]["locale_supported"])) {
            $services["parameters"]["locale_supported"] = array($services["parameters"]["locale"]);
            $services['parameters']['app_locales'] = $services["parameters"]["locale"];
            $yaml = Yaml::dump($services);
            file_put_contents($servicesSources, $yaml);
        }
        $progress->advance($cicle);
        usleep(10000);
        
        // 2. update framework.yaml
        $frameworkSources = $kernel->getProjectDir(). '/config/packages/framework.yaml';
        $framework = Yaml::parseFile($frameworkSources);
        if(isset($framework['framework']['translator']["fallback"])) {
            $framework['framework']['translator']["fallback"] = "%locale%";
        } else{
            $framework['framework']['translator'] = ["fallback" => "%locale%"];
        }
        $yamlFramework = Yaml::dump($framework);
        file_put_contents($frameworkSources, $yamlFramework);
        $progress->advance($cicle);
        usleep(10000);
        
        // 3. update routes.yaml
        $routesSources = $kernel->getProjectDir(). '/config/routes.yaml';
        $routes = Yaml::parseFile($routesSources);
        $routes['kematjaya'] = array(
            'resource' => '@TranslationBundle/Resources/config/routing/all.xml'
        );
        $routesYaml = Yaml::dump($routes);
        file_put_contents($routesSources, $routesYaml);
        $progress->advance($cicle);
        usleep(10000);
        
        // 4. update annotations.yaml
        $annotationsSources = $kernel->getProjectDir(). '/config/routes/annotations.yaml';
        $annotations = Yaml::parseFile($annotationsSources);
        $annotations["controllers"]["prefix"]           = "/{_locale}";
        $annotations["controllers"]["requirements"]     = array("_locale" => "%app_locales%");
        $annotations["controllers"]["defaults"]         = array("_locale" => "%locale%");
        $annotationsYaml = Yaml::dump($annotations);
        file_put_contents($annotationsSources, $annotationsYaml);
        $progress->advance($cicle);
        usleep(10000);
        
        // clear cache
        $command = $this->getApplication()->find('cache:clear');
        $arguments = array(
            'command' => 'cache:clear'
        );

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);
            
        $io->note("add locale successfully");
    }
}

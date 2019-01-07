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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;

class TranslationCommand extends Command{
    
    protected static $defaultName = 'kematjaya:translation:configure';
    
    private $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this
            ->setDescription('setting default translation configure')
            ->addArgument('app_locales', InputArgument::REQUIRED, 'language code, separate with character "," if more than one, example= en,id,fr')    
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->container->get('kernel');
        try {
            $output->writeln("====== update services.yaml ======");
            
            $servicesSources = $kernel->getProjectDir(). '/config/services.yaml';
            $services = Yaml::parseFile($servicesSources);
            $app_locales = explode(",", $input->getArgument('app_locales'));
            
            $services['parameters']['app_locales'] = implode("|", $app_locales);
            $services['parameters']['locale_supported'] = $app_locales;
            //dump($services);exit;
            $yaml = Yaml::dump($services);
            file_put_contents($servicesSources, $yaml);
            $output->writeln("====== update services.yaml successfull. ======");
            
            $output->writeln("====== update framework.yaml ======");
            
            $frameworkSources = $kernel->getProjectDir(). '/config/packages/framework.yaml';
            $framework = Yaml::parseFile($frameworkSources);
            if(isset($framework['framework']['translator']["fallback"])) {
                $framework['framework']['translator']["fallback"] = "%locale%";
            } else{
                $framework['framework']['translator'] = ["fallback" => "%locale%"];
            }
            
            $yamlFramework = Yaml::dump($framework);
            file_put_contents($frameworkSources, $yamlFramework);
            $output->writeln("====== update framework.yaml successfull. ======");
            
            $output->writeln("====== update routes.yaml ======");
            
            $routesSources = $kernel->getProjectDir(). '/config/routes.yaml';
            $routes = Yaml::parseFile($routesSources);
            $routes['kematjaya'] = array(
                'resource' => '@TranslationBundle/Resources/config/routing/all.xml'
            );
            
            $routesYaml = Yaml::dump($routes);
            file_put_contents($routesSources, $routesYaml);
            $output->writeln("====== update routes.yaml successfull. ======");
            
            $output->writeln("====== update config/routes/annotations.yaml ======");
            $annotationsSources = $kernel->getProjectDir(). '/config/routes/annotations.yaml';
            $annotations = Yaml::parseFile($annotationsSources);
            $annotations["controllers"]["prefix"]           = "/{_locale}";
            $annotations["controllers"]["requirements"]     = array("_locale" => "%app_locales%");
            $annotations["controllers"]["defaults"]         = array("_locale" => "%locale%");
            $annotationsYaml = Yaml::dump($annotations);
            file_put_contents($annotationsSources, $annotationsYaml);
            $output->writeln("====== update config/routes/annotations.yaml successfull. ======");
            
            $output->writeln("====== clearing cache. ======");
            $command = $this->getApplication()->find('cache:clear');
            $arguments = array(
                'command' => 'cache:clear'
            );

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, $output);
            
        } catch (Exception $ex) {
            $output->writeln("====== error : ".$ex->getMessages()." ======");
        }
    }
}

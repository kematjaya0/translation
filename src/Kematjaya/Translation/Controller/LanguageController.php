<?php

/**
 * Description of LanguageController
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\KernelInterface;
use Kematjaya\Translation\Form\KmjLanguageType;
use Kematjaya\Translation\Filter\KmjLanguageFilterType;
use Symfony\Component\Form\Form;
use Nahid\JsonQ\Jsonq;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class LanguageController extends AbstractController{
    
    private $limit = 10;
    
    private $session;
    
    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }
    
    
    private function getTranslatorData(KernelInterface $kernel) {
        try{
            $sources = array();
            $data = array();
            foreach($this->getParameter('locale_supported') as $v) {
                $filename = $kernel->getRootDir().'/Resources/translations/messages.'.$v.'.yml';
                if(!file_exists($filename)) {
                    $handle  = fopen($filename, 'w');
                    $yaml = (isset($sources[$v])) ? Yaml::dump($sources[$v]) : Yaml::dump(array());
                    fwrite($handle, $yaml);
                }
                $sources[$v] = Yaml::parseFile($filename);
                if($sources[$v]) {
                    foreach($sources[$v] as $key => $value) {
                        if(!isset($data[$key][$v])) {
                            $data[$key][$v] = $value;
                        }
                    }
                }
                    
            }
            
            return $data;
        } catch (Exception $ex) {
            printf('Unable to parse the YAML string: %s', $exception->getMessage());die();
        }
    }
    
    public function setFilters($filters = array(), $name)
    {
        $this->session->set($name, $filters);
    }
    
    public function getFilters($name)
    {
        return $this->session->get($name, []);
    }
    
    public function index(Request $request, KernelInterface $kernel)
    {
        $data = $this->getTranslatorData($kernel);
        
        if($request->get('_reset')) {
            $this->setFilters(null, KmjLanguageFilterType::class);
        }
        if($request->get('_limit') && is_numeric($request->get('_limit'))) {
            $request->getSession()->set('limit', $request->get('_limit'));
        }
        
        $form = $this->get('form.factory')->create(KmjLanguageFilterType::class, $this->getFilters(KmjLanguageFilterType::class));
        
        $filters = $request->get($form->getName());
        if($filters) {
            $this->setFilters($filters, KmjLanguageFilterType::class);
            $form = $this->get('form.factory')->create(KmjLanguageFilterType::class, $filters);
            
            if(isset($filters['key']) && strlen($filters['key']) > 0) {
                $results = array();
                foreach($data as $key => $value)
                {
                      if(stristr($key,$filters['key'])!==FALSE)
                        $results[$key] = $value;
                }
                $data = $results;
            }
            
            $trans = array('translate' => $data);
            $jsonq = new Jsonq();
            $jsonq->collect($trans);
            $res = $jsonq->from('translate');
            
            if(isset($filters['translated']) && strlen($filters['translated']) > 0) {
                foreach($this->getParameter('locale_supported') as $v){
                    $res = $res->orWhere($v, 'contains', $filters['translated']);
                }
                
            }
            $data = $res->get();
        }
        
        $adapter = new ArrayAdapter($data);
        $paginator = new Pagerfanta($adapter);
        $paginator->setAllowOutOfRangePages(true);
        //  Set pages based on the request parameters.
        $paginator->setMaxPerPage($request->query->get('_limit', $this->limit));
        $paginator->setCurrentPage($request->query->get('page', 1));
        
        return $this->render('@Translation/language/index.html.twig', array(
            'title' => 'Language', 'pagers' => $paginator, 'data' => $data, 'filter' => $form->createView()
        ));
    }
}

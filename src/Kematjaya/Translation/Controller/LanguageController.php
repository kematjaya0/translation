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
use Symfony\Component\Translation\Loader\YamlFileLoader;


class LanguageController extends AbstractController{
    
    private $limit = 10;
    
    private function getTranslationSetting(Request $request)
    {
        $kernel = $this->container->get('kernel');
        $transConfig = $kernel->getProjectDir(). '/config/packages/translation.yaml';
        $loader = new YamlFileLoader();
        $resource = $loader->load($transConfig, $request->getSession()->get('_locale'));
        return $resource;
    }
    
    private function getTranslatorData($resource) {
        $kernel = $this->container->get('kernel');
        $transPath = str_replace('%kernel.project_dir%', $kernel->getProjectDir(), $resource->get('framework.translator.default_path'));
        
        try{
            $sources = array();
            $data = array();
            
            foreach($this->container->getParameter('locale_supported') as $v) {
                $filename = $transPath.'/messages.'.$v.'.yml';
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
        $this->container->get('session')->set($name, $filters);
    }
    
    public function getFilters($name)
    {
        return $this->container->get('session')->get($name, []);
    }
    
    public function index(Request $request)
    {
        $resource = $this->getTranslationSetting($request);
        
        $data = $this->getTranslatorData($resource);
        //dump($data);exit;
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
                foreach($this->container->getParameter('locale_supported') as $v){
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
    
    function create(Request $request)
    {
        $form = $this->createForm(KmjLanguageType::class);
        
        if ($this->processForm($form, $request, array())) {
            return $this->redirectToRoute('kematjaya_language_index');
        }
        
        return $this->render('backend/ms_language/create.html.twig', 
            [
                'title' => 'Language', 
                'form' => $form->createView()
            ]);
    }
    
    private function processForm(Form $form, Request $request, $data = array())
    {
        $kernel = $this->container->get('kernel');
        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            $edit = (!empty($data)) ? true : false;
            $formData = $request->get($form->getName());
            $transData = $this->getTranslatorData($kernel);
            
            if(!$edit) {
                if(isset($transData[$formData['key']])) {
                    $this->addFlash('error', 'key "'.$formData['key'].'" already exist');
                    return false;
                }
            }
            
            if($form->isValid())
            {
                $type = ($edit) ? "update" : "add";
                $sources = array();
                
                try{
                    foreach($this->getParameter('locale_supported') as $v) {
                        $filename = $kernel->getRootDir().'/Resources/translations/messages.'.$v.'.yml';
                        if(!file_exists($filename)) {
                            $handle  = fopen($filename, 'w');
                            $yaml = Yaml::dump($sources);
                            fwrite($handle, $yaml);
                        }
                        $sources = Yaml::parseFile($filename);
                        $sources[$formData['key']] = $formData[$v];

                        $yaml = Yaml::dump($sources);

                        file_put_contents($filename, $yaml);
                    }

                    $this->addFlash('success', $this->getTranslator()->trans('messages.'.$type.'.success'));
                    return true;
                } catch (Exception $ex) {
                    $this->addFlash('error', $this->getTranslator()->trans('messages.'.$type.'.error') . ' : ' . $ex->getMessages());
                }
            }
            
        }
        
        return false;
    }
    
    public function edit(Request $request, $id, KernelInterface $kernel)
    {
        $transData = $this->getTranslatorData($kernel);
        $data = (isset($transData[$id])) ? $transData[$id] : array();
        $data['key'] = $id;
        $form = $this->createForm(MsLanguageType::class, $data);
        
        if ($this->processForm($kernel, $form, $request, $data)) {
            return $this->redirectToRoute('ms_language_index');
        }
        
        return $this->render('backend/ms_language/edit.html.twig', 
            [
                'title' => 'Language', 
                'id' => $id,
                'form' => $form->createView()
            ]);
    }
    
    
    public function delete(Request $request, $id, KernelInterface $kernel)
    {
        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token')))
        {
            try{
                foreach($this->getParameter('locale_supported') as $v) {
                    $sources = Yaml::parseFile($kernel->getRootDir().'/Resources/translations/messages.'.$v.'.yml');
                    
                    if(isset($sources[$id])) {
                        unset($sources[$id]);
                    }
                    
                    $yaml = Yaml::dump($sources);

                    file_put_contents($kernel->getRootDir().'/Resources/translations/messages.'.$v.'.yml', $yaml);
                }

                $this->addFlash('success', $this->getTranslator()->trans('messages.deleted.success'));
            } catch (Exception $ex) {
                $this->addFlash('error', $this->getTranslator()->trans('messages.deleted.error') . ' : ' . $ex->getMessages());
            }
                
        }else{
            $this->addFlash('error', $this->getTranslator()->trans('messages.deleted.error') . ' : token not valid.');
        }
         
        return $this->redirectToRoute('ms_language_index');
    }
}

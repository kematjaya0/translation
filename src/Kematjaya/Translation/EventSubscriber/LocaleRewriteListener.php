<?php

/**
 * Description of LocaleRewriteListener
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocaleRewriteListener implements EventSubscriberInterface{
    
    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    private $router;
    
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface;
     */
    private $container;
    /**
    * @var routeCollection \Symfony\Component\Routing\RouteCollection
    */
    private $routeCollection;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $supportedLocales;

    /**
     * @var string
     */
    private $localeRouteParam;

    public function __construct(RouterInterface $router, $defaultLocale = 'en', ContainerInterface $container, $localeRouteParam = '_locale')
    {
        $this->router = $router;
        $this->routeCollection = $router->getRouteCollection();
        $this->container = $container;
        $this->defaultLocale = $defaultLocale;
        $this->supportedLocales = ($this->container->hasParameter('locale_supported')) ? $this->container->getParameter('locale_supported'): array($this->container->getParameter('locale'));
        $this->localeRouteParam = $localeRouteParam;
    }
    
    public function isLocaleSupported($locale) 
    {
        return in_array($locale, $this->supportedLocales);
    }
    
    public function onKernelRequest(GetResponseEvent $event) // function yang wajib ada di setiap class yang implements dari interface EventSubscriberInterface
    {
        $request = $event->getRequest(); // mengambil object Request
        $path = $request->getPathInfo(); // mengambil path / url dari request

        $route_exists = false;
        // check dari routing yang tersedia, apakah url ada atau tidak
        foreach($this->routeCollection as $routeObject){
            $routePath = $routeObject->getPath();
            if($routePath == "/{_locale}".$path){
                $route_exists = true;
                break;
            }
        }
        
        // jika path routing ada
        if($route_exists == true){
            $locale = $request->getPreferredLanguage(); // ambil bahasa 
            
            if($locale==""  || $this->isLocaleSupported($locale)==false){
                $locale = $request->getDefaultLocale();
            }
            
            $event->setResponse(new RedirectResponse($request->getSchemeAndHttpHost().$request->getBaseUrl()."/".$locale.$path));
        }
    }
    
    public static function getSubscribedEvents() 
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 19)),
        );
    }
}

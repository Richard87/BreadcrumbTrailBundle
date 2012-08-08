<?php

namespace Tactics\Bundle\BreadcrumbTrailBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tactics\Bundle\BreadcrumbTrailBundle\Trail\Trail;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DomCrawler\Crawler;

class TrailResponseListener
{
    /**
     *
     * @var Trail A Trail instance
     */
    protected $trail;
    
    /**
     *
     * @var Router A Router instance
     */
    protected $router;

    /**
     * Constructor.
     *
     * @param Session $session A Session instance
     */
    public function __construct(Trail $trail, Router $router)
    {
        $this->trail = $trail;
        $this->router = $router;
    }
    
    
    public function onResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST && false)
        {
            $request = $event->getRequest();
            $response = $event->getResponse();
            
            $routeName = $request->get('_route');
            
            if (
                // ignore routes prefixed with _ like profiler urls
                (substr($routeName, 0, 1) == '_')
                // only GET & HEAD go into trail
                || ! $request->isMethodSafe()
                || ! $request->isXmlHttpRequest()
                || ($response->headers->get('content-type') != 'text/html')
            )
            {
                return;
            }
            
            // SF 2.1+
            $routeParams = $request->get('_route_params', null);
            
            // SF 2.0
            // http://stackoverflow.com/questions/9378714/get-current-url-in-twig-template
            if ($routeParams === null)
            {
                $routeParams = $request->query->all();
                foreach ($this->router->getRouteCollection()->get($routeName)->compile()->getVariables() as $variable) {
                    $routeParams[$variable] = $request->attributes->get($variable);
                }
            }
            
            $crawler = new Crawler();
            $crawler->addHtmlContent($response->getContent());

            $title = $crawler->filter('head > title')->text();
            
            $this->trail->add($title, $routeName, $routeParams);
        }
    }
}
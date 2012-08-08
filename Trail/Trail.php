<?php

/*
 * This file is part of the APYBreadcrumbTrailBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tactics\Bundle\BreadcrumbTrailBundle\Trail;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use APY\BreadcrumbTrailBundle\BreadcrumbTrail\Breadcrumb;


class Trail implements \IteratorAggregate, \Countable
{
    /**
     * @var Breadcrumb[] Array of breadcrumbs
     */
    private $trail;

    /**
     * @var UrlGeneratorInterface URL generator class
     */
    private $router;
    
    /**
     *
     * @var Session a Session instace
     */
    protected $session;
    

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $router URL generator class
     */
    public function __construct(UrlGeneratorInterface $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
        
        $this->trail = $this->session->get('tactics_trail', array());
    }


    /**
     * Add breadcrumb
     *
     * @param mixed   $breadcrumb_or_title  A Breadcrumb instance or the title of the breadcrumb
     * @param string  $routeName            The name of the route
     * @param mixed   $routeParameters      An array of parameters for the route
     * @param Boolean $routeAbsolute        Whether to generate an absolute URL
     * @return self
     */
    function add($breadcrumb_or_title, $routeName = null, $routeParameters = array(), $routeAbsolute = false)
    {
        
        if ($breadcrumb_or_title instanceof Breadcrumb) {
            array_push($this->trail, $breadcrumb_or_title);
        } else {
            if (!is_string($breadcrumb_or_title)) {
                throw new \InvalidArgumentException('The title of a breadcrumb must be a string.');
            }

            $url = null;
            if ( !is_null($routeName) ) {
                $url = $this->router->generate($routeName, $routeParameters, $routeAbsolute);
            }

            array_push($this->trail, new Breadcrumb($breadcrumb_or_title, $url));
        }
        
        $this->trail = array_slice($this->trail, -5);
        
        $this->session->set('tactics_trail', $this->trail);        
        
        return $this;
    }

    /**
     * Reset the trail
     *
     * @return self
     */
    public function reset() {
        $this->trail = array();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function count() {
        return count($this->trail);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator() {
        return new \ArrayIterator($this->trail);
    }
}

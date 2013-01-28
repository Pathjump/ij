<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Objects controller.
 *
 */
class ObjectsController extends Controller {

    /**
     * Generates a URL from the given parameters.
     *
     * @param string  $route      The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generateUrl($route, $parameters = array(), $absolute = false) {
        if ($this->getRequest()->get('access_method')) {
            if (!isset($parameters['access_method'])) {
                $parameters['access_method'] = 'facebook';
            }
        }
        return $this->container->get('router')->generate($route, $parameters, $absolute);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string  $route      The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generateNormalUrl($route, $parameters = array(), $absolute = false) {
        if (isset($parameters['access_method'])) {
            unset($parameters['access_method']);
        }
        return $this->container->get('router')->generate($route, $parameters, $absolute);
    }

}

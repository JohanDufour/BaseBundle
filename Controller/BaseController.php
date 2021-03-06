<?php

namespace GeorgetteParty\BaseBundle\Controller;
use Krovitch\BaseBundle\Utils\ClassGuesser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Class BaseController
 * Abstract BaseController. Contains magic methods
 * @package Georgetteparty\BaseBundle\Controller
 */
abstract class BaseController extends Controller
{
    /**
     * PreExecute hook. PreExecuteListener should be activated
     */
    public function preExecute()
    {
    }

    /**
     * Return current translator
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * Return current session
     * @return Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    public function getConfig($key)
    {
        return $this->container->getParameter($key);
    }

    /**
     * Return the manager linked with this controller
     * @param null $managerName
     * @return \GeorgetteParty\BaseBundle\Manager\BaseManager
     */
    protected function getManager($managerName = null)
    {
        // try to find automatically the repository name
        if (!$managerName) {
            $guesser = new ClassGuesser($this);
            $managerName = $guesser->getClass(array('Manager', 'Controller'));
            $bundle = strtolower($guesser->getBundle());

            // add bundle prefix
            if (substr($managerName, 0, 7) != $bundle) {
                $managerName = $bundle . $managerName . '_manager';
            }
        }
        $managerName = Container::camelize($managerName);

        return $this->get($managerName);
    }

    /**
     * Set a flash notice in session for next request. The message is translated
     * @param $message
     * @param array $parameters
     * @internal param bool $useTranslation
     * @internal param array $translationParameters
     * @return void
     */
    protected function setMessage($message, $parameters = array())
    {
        $this->getSession()->getFlashBag()->add('notice', $this->translate($message, $parameters));
    }

    /**
     * Redirects response to an url or a route
     * @param string $url
     * @param int $status
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        if (substr($url, 0, 1) == '@') {
            $route = substr($url, 1);
            $url = $this->generateUrl($route);
        }
        return parent::redirect($url, $status);
    }

    /**
     * Redirect 404 if not $condition
     * @param $condition
     * @param $message
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function redirect404Unless($condition, $message)
    {
        if (!$condition) {
            throw $this->createNotFoundException($message);
        }
    }

    /**
     * Translate $string
     * @param $string
     * @param array $parameters
     * @return string
     */
    protected function translate($string, $parameters = array())
    {
        return $this->getTranslator()->trans($string, $parameters);
    }


    // TODO make it generic with configuration parameter
//    public function getPager()
//    {
//        return $this->get('knp_paginator');
//    }

    // TODO useful ? maybe with parameters in conf
    /*public function log(\Exception $e, $notify = false, $message = '')
    {
        // on loggue l'erreur et on informe l'utilisateur que la création du fichier ne s'est pas correctement terminée
        $this->getManager('Log')->create($e->getMessage(), $e->getTraceAsString());
        $this->get('logger')->err($e->getMessage());

        if ($notify) {
            // on informe l'utilisateur qu'une erreur s'est déroulée
            $this->setMessage($message, array(), 'error');
        }
    }*/
}
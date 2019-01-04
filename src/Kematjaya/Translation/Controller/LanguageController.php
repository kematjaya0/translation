<?php

/**
 * Description of LanguageController
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LanguageController extends AbstractController{
    
    public function index()
    {
        return $this->render('@Translation/language/index.html.twig', array(
            'test' => 'aaa',
        ));
    }
}

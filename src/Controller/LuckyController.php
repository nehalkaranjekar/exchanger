<?php
// src/Controller/LuckyController.php
namespace App\Controller;

// use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LuckyController extends AbstractController
{
    public function number()
    {
        return $this->redirect('/rates');
    }

    public function baseHtml()
    {
        $number = random_int(0, 100);

        return $this->render('base.html.twig');
    }
}
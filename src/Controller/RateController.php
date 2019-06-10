<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Rates;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RateController extends AbstractController
{
    /**
     * @Route("/latest-rates", name="rate")
     */
    public function index()
    {
        return $this->render('rate/index.html.twig', [
            'controller_name' => 'RateController',
        ]);
    }

    /**
	* @Route("/create-rate")
	*/
	public function createAction(Request $request) {

	  $rates = new Rates();
	  $form = $this->createFormBuilder($rates)
	    ->add('rates', TextType::class,
	    array('required' => true, 'attr' => array('placeholder' => 'Please enter decimal value','class' => 'form-control', 'id' => 'rate')))
	    ->add('currency', ChoiceType::class, array(
			    'choices'  => [
			    	'Select Currency' => "",
			        'INR' => "INR",
			        'EUR' => "EUR"
			    ],'attr' => array('class' => 'form-control', 'id' => 'currency')
			))
	    ->add('save', SubmitType::class, array('label' => 'Save Rates','attr' => array('class' => 'btn btn-default')))
	    ->getForm();

	  $form->handleRequest($request);

	  if ($form->isSubmitted()) {

	    $rates = $form->getData();
	    $rates->setCreated_at(\DateTime::createFromFormat('Y-m-d h:i:s',date('Y-m-d h:i:s')));
	    $em = $this->getDoctrine()->getManager();
	    $em->persist($rates);
	    $em->flush();

	    return $this->redirect('rates/');

	  }

	  return $this->render(
	    'rate/edit.html.twig',
	    array('form' => $form->createView())
	    );

	}

	
	/**
	* @Route("/view-rates/{id}")
	*/   
	public function viewAction($id) {
		$rate = $this->getDoctrine()
			->getRepository(Rates::class)
			->find($id);
		if (!$rate) {
			throw $this->createNotFoundException(
			'There are no rates with the following id: ' . $id
			);
		}
		return $this->render(
			'rate/view.html.twig',
			array('rate' => $rate)
			);
	}	
	
	/**
	* @Route("/rates")
	*/  
	public function showAction() {
		$rates = $this->getDoctrine()
			->getRepository(Rates::class)
			->findAll();
		$exchange_rates = $this->callExchangeAPI();
		return $this->render(
			'rate/show.html.twig',
			array('rates' => $rates,'exchange_rates' => $exchange_rates)
			);
	}
	
	/**
	* @Route("/delete-rate/{id}")
	*/ 
	public function deleteAction($id) {
		$em = $this->getDoctrine()->getManager();
		$rate = $em->getRepository(Rates::class)->find($id);
		if (!$rate) {
			throw $this->createNotFoundException(
			'There are no rates with the following id: ' . $id
			);
		}
		$em->remove($rate);
		$em->flush();
		return $this->redirect('/rates');
	}
	
	/**
	* @Route("/update-rate/{id}")
	*/  
	public function updateAction(Request $request, $id) {
		$em = $this->getDoctrine()->getManager();
		$rate = $em->getRepository(Rates::class)->find($id);

		if (!$rate) {
			throw $this->createNotFoundException(
			'There are no rates with the following id: ' . $id);
		}

	  	$form = $this->createFormBuilder($rate)
	    ->add('rates', TextType::class,
	    array('required' => true, 'attr' => array('placeholder' => 'Please enter decimal value','class' => 'form-control', 'id' => 'rate')))
	    ->add('currency', ChoiceType::class, array(
			    'choices'  => [
			    	'Select Currency' => "",
			        'INR' => "INR",
			        'EUR' => "EUR"
			    ],'attr' => array('class' => 'form-control', 'id' => 'currency')
			))
	    ->add('save', SubmitType::class, array('label' => 'Save Rates','attr' => array('class' => 'btn btn-default')))
	    ->getForm();

		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			$rate = $form->getData();
			$rate->setUpdated_at(\DateTime::createFromFormat('Y-m-d h:i:s',date('Y-m-d h:i:s')));
			$em->flush();
			return $this->redirect('/rates');
		}
		return $this->render(
			'rate/edit.html.twig',
			array('form' => $form->createView())
			);
	}

    /**
	* @Route("/exchange-rates")
	*/
	public function callExchangeAPI()
	{
	    $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.exchangeratesapi.io/latest?base=USD');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);

		// If using JSON...
		$data = json_decode($response);
		return $data->rates;
	}

	    /**
	* @Route("/get-rates/{id}")
	*/
	public function getRateByCountry($id)
	{
	    $rates = $this->callExchangeAPI();
		return new Response(json_encode(['rate' => $rates->$id]));
	}
}

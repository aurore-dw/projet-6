<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TricksRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{

    
    //Fonction qui affiche les 8 premiers tricks de la page d'accueil + affiche les messages de succès
    #[Route('/', name: 'app_home')]
    public function index(TricksRepository $tricksRepository, SessionInterface $session): Response
    {

        //Affiche les 8 premiers tricks
        $tricks = $tricksRepository->findBy([], ['create_at' => 'DESC'], 8);
        //On compte tous les tricks disponnibles
        $totalTricks = $tricksRepository->countAllTricks();
        //Message succès ajout trick
        $successMessage = $session->getFlashBag()->get('success', []);
        //Message succès édition trick
        $successMessageEdit = $session->getFlashBag()->get('successEdit', []);
        //Message succès suppression trick
        $successMessageDelete = $session->getFlashBag()->get('danger', []);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tricks' => $tricks,
            'totalTricks' => $totalTricks,
            'successMessage' => $successMessage,
            'successMessageEdit' => $successMessageEdit,
            'successMessageDelete' => $successMessageDelete,
        ]);
    }

}

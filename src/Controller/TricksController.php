<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/tricks')]
class TricksController extends AbstractController
{
    #[Route('/', name: 'app_tricks_index', methods: ['GET'])]
    public function index(TricksRepository $tricksRepository): Response
    {
        /*return $this->render('tricks/index.html.twig', [
            'tricks' => $tricksRepository->findAll(),
        ]);*/
        $tricks = $tricksRepository->findBy([], null, 8);

        return $this->render('tricks/index.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/load-more-tricks', name:'load_more_tricks')]
    public function loadMoreTricks(Request $request)
    {
        $loadedTricks = $request->query->getInt('loadedTricks', 0);

        // Charger les tricks supplémentaires depuis la base de données
        $tricks = $this->entityManager->getRepository(Tricks::class)
            ->findBy([], null, 8, $loadedTricks);

        return $this->render('tricks/_tricks.html.twig', [
            'tricks' => $tricks
        ]);
    }

    #[Route('/new', name: 'app_tricks_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TricksRepository $tricksRepository, SecurityController $lastUsername): Response
    {
        $trick = new Tricks();
        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedPictures = $form->get('pictures')->getData();

            foreach ($uploadedPictures as $uploadedPicture) {
                $filename = md5(uniqid()) . '.' . $uploadedPicture->guessExtension();
                $uploadedPicture->move(
                    $this->getParameter('picture_directory'),
                    $filename
                );
                $trick->addPicture($filename);
            }
            // Récupérer les liens vidéo
            $videos = $form->get('videos')->getData();
            $videoLinks = explode(',', $videos); // Sépare les liens vidéo par une virgule
            $trick->setVideos($videoLinks);
            
            $trick->setCreateAt(new \DateTime());
            $trick->setUser($lastUsername->getUser());

            $tricksRepository->save($trick, true);

            return $this->redirectToRoute('app_tricks_index');
        }

        return $this->renderForm('tricks/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_tricks_show', methods: ['GET'])]
    public function show(Tricks $trick): Response
    {
        return $this->render('tricks/show.html.twig', [
            'trick' => $trick,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tricks_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tricks $trick, TricksRepository $tricksRepository, $id): Response
    {
        //Récupére les images existantes
        $existingPictures = $trick->getPictures();
        $form = $this->createForm(TricksType::class, $trick, [
            'existing_pictures' => $existingPictures,
        ]);
        //Récupére les liens vidéos
        $existingVideos = $trick->getVideos();
        $form = $this->createForm(TricksType::class, $trick, [
        'existing_videos' => $existingVideos,
        'mapped' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        // Supprimer les images sélectionnées
        $selectedPictures = $request->request->get('selected_pictures');
        $selectedPictures = json_decode($selectedPictures, true);
        if (!empty($selectedPictures)) {
            //$selectedPictures = explode(',', $selectedPictures);
            foreach ($selectedPictures as $selectedPicture) {
                // Supprimer l'image existante 
                $trick->removePicture($selectedPicture);
                // Supprimer l'image du dossier
                $pictureDirectory = $this->getParameter('picture_directory');
                $filesystem = new Filesystem();
                $filesystem->remove($pictureDirectory.'/'.$selectedPicture);
                // Supprimer l'image existante du tableau pictures
                $index = array_search($selectedPicture, $existingPictures, true);
                if ($index !== false) {
                    unset($existingPictures[$index]);
                }
            }
            // Mise à jour des vidéos
            $newVideoLinks = $form->get('videos')->getData();
            $videoLinks = explode(',', $newVideoLinks); // Sépare les liens vidéo par une virgule
            $trick->setVideos($videoLinks);
            // Mettre à jour l'entité Tricks avec les images restantes
            $trick->setPictures(array_values($existingPictures));
            }

            // On ajoute la date de mise à jour
            $trick->setUpdateAt(new \DateTime());

            // Gérer les modifications des images
            $uploadedPictures = $form->get('pictures')->getData();

            // Rétablir les images existantes dans le tableau
            foreach ($existingPictures as $existingPicture) {
                $trick->addPicture($existingPicture);
            }

            // Traitement des nouvelles images
            if (!empty($uploadedPictures)) {
                foreach ($uploadedPictures as $uploadedPicture) {
                    if ($uploadedPicture->isValid() && $uploadedPicture->getError() === UPLOAD_ERR_OK ) {
                    $filename = md5(uniqid()) . '.' . $uploadedPicture->guessExtension();
                    $uploadedPicture->move(
                        $this->getParameter('picture_directory'),
                        $filename
                    );
                    $trick->addPicture($filename);
                }
            }
        }

            // Enregistrer les modifications dans la base de données
            $tricksRepository->save($trick, true);

            return $this->redirectToRoute('app_tricks_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('tricks/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tricks_delete', methods: ['POST'])]
    public function delete(Request $request, Tricks $trick, TricksRepository $tricksRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $tricksRepository->remove($trick, true);
        }

        return $this->redirectToRoute('app_tricks_index', [], Response::HTTP_SEE_OTHER);
    }
}

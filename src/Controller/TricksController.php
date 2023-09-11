<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/tricks')]
class TricksController extends AbstractController
{
    #[Route('/', name: 'app_tricks_index', methods: ['GET'])]
    public function index(TricksRepository $tricksRepository): Response
    {
        //Affiche les 8 premiers tricks
        $tricks = $tricksRepository->findBy([], ['create_at' => 'DESC'], 8);
        //On compte tous les tricks disponnibles
        $totalTricks = $tricksRepository->countAllTricks();

        return $this->render('tricks/index.html.twig', [
            'tricks' => $tricks,
            'totalTricks' => $totalTricks,
        ]);
    }

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/load-more-tricks', name:'load_more_tricks')]
    public function loadMoreTricks(Request $request, TricksRepository $tricksRepository): Response
    {
        $offset = $request->query->getInt('offset', 0);
        $limit = 8;

        // Récupère les id des tricks déjà chargés
        $loadedTrickIds = $request->getSession()->get('loaded_trick_ids', []);

        // Exclut les tricks déjà chargés de la requête
        $tricks = $tricksRepository->findPaginatedTricks($offset, $limit, $loadedTrickIds);

        // Renvoie les tricks sous forme de HTML à inclure directement dans la page
        return $this->render('tricks/_tricks.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    #[Route('/new', name: 'app_tricks_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TricksRepository $tricksRepository, SecurityController $lastUsername, SessionInterface $session): Response
    {
        //On vérifie si l'utilisateur est bien connecté et si son compte est vérifié
        $user = $lastUsername->getUser();
        if (!$user || !$user->isVerified()) {
            $session->getFlashBag()->add('error', 'Vous devez être connecté et avoir un compte vérifié pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }
        
        $trick = new Tricks();
        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupére les liens vidéo
            $videos = $form->get('videos')->getData();
            $videoLinks = explode(',', $videos); // Sépare les liens vidéo par une virgule
            $trick->setVideos($videoLinks);

            $trick->setCreateAt(new \DateTime());
            $trick->setUser($lastUsername->getUser());

            // Enregistre l'entité Trick dans la base de données
            $tricksRepository->save($trick, true);

            // Gére les images après l'enregistrement pour avoir accès à l'id généré
            $uploadedPictures = $form->get('pictures')->getData();
            $pictureDirectory = $this->getParameter('picture_directory');
            $imageNumber = 1;

            if (empty($uploadedPictures)) {
                // Aucune image sélectionnée, définir une image par défaut
                $defaultPicture = 'default.jpg';
                $trick->addPicture($defaultPicture);
            } else {
                // Traitement des images
                foreach ($uploadedPictures as $uploadedPicture) {
                    // Permet d'éviter l'ajout des chemins temporaires dans la bdd
                    if ($uploadedPicture->getClientOriginalExtension() !== 'tmp') {
                        $filename = $trick->getId() . '_' . $imageNumber . '.' . $uploadedPicture->guessExtension();
                        $uploadedPicture->move(
                            $pictureDirectory,
                            $filename
                        );
                        $trick->addPicture($filename);
                        $imageNumber++;
                    }
                }
            }

            // Enregistre à nouveau l'entité Trick pour mettre à jour les images avec les noms de fichier corrects
            $tricksRepository->save($trick, true);

            // Ajoute un message de succès dans la session
            $session->getFlashBag()->add('success', 'Le trick a été enregistré avec succès !');

            //return $this->redirectToRoute('app_tricks_index');
            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('tricks/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_tricks_show', methods: ['GET', 'POST'])]
    public function show(Request $request, CommentRepository $commentRepository, SecurityController $lastUsername, Tricks $trick, TricksRepository $tricksRepository, $id, SessionInterface $session): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreateAt(new \DateTime(date('Y-m-d H:i:s')));
            $comment->setUser($lastUsername->getUser());
            $trick = $tricksRepository->find($id);
            $comment->setTrick($trick);
            $commentRepository->save($comment, true);
            return $this->redirectToRoute('app_tricks_show', ['id' => $trick->getId(), '_fragment' => 'comment-section'], Response::HTTP_SEE_OTHER);
        }
        return $this->render('tricks/show.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tricks_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tricks $trick, TricksRepository $tricksRepository, $id, SessionInterface $session, SecurityController $lastUsername): Response
    {
        //On vérifie si l'utilisateur est bien connecté et si son compte est vérifié
        $user = $lastUsername->getUser();
        if (!$user || !$user->isVerified()) {
            $session->getFlashBag()->add('error', 'Vous devez être connecté et avoir un compte vérifié pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }

        // Récupére les images existantes
        $existingPictures = $trick->getPictures();
        $form = $this->createForm(TricksType::class, $trick, [
            'existing_pictures' => $existingPictures,
        ]);

        // Récupére les liens vidéos existants
        $existingVideos = $trick->getVideos();
        $form = $this->createForm(TricksType::class, $trick, [
            'existing_videos' => $existingVideos,
            'mapped' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Supprime les images sélectionnées
            $selectedPictures = $request->request->get('selected_pictures');
            $selectedPictures = json_decode($selectedPictures, true);
            if (!empty($selectedPictures)) {
                $deletedImageNumber = null;
                foreach ($selectedPictures as $selectedPicture) {
                    // Supprime l'image existante
                    $trick->removePicture($selectedPicture);
                    // Supprime l'image du dossier
                    $pictureDirectory = $this->getParameter('picture_directory');
                    $filesystem = new Filesystem();
                    $filesystem->remove($pictureDirectory.'/'.$selectedPicture);
                    // Supprime l'image existante du tableau pictures
                    $index = array_search($selectedPicture, $existingPictures, true);
                    if ($index !== false) {
                        $deletedImageNumber = $this->getImageNumberFromName($existingPictures[$index]);
                        unset($existingPictures[$index]);
                    }
                }
            }

                // Mise à jour des vidéos
                $newVideoLinks = $form->get('videos')->getData();
                $videoLinks = explode(',', $newVideoLinks); // Séparer les liens vidéo par une virgule
                $trick->setVideos($videoLinks);
                // Met à jour l'entité Tricks avec les images restantes
                $trick->setPictures(array_values($existingPictures));

                // Gérer les nouvelles images
                $uploadedPictures = $form->get('pictures')->getData();
                $nextImageNumber = $deletedImageNumber ?? count($existingPictures) + 1;
                foreach ($uploadedPictures as $uploadedPicture) {
                    if ($uploadedPicture->isValid() && $uploadedPicture->getError() === UPLOAD_ERR_OK) {
                        $filename = $trick->getId() . '_' . $nextImageNumber . '.' . $uploadedPicture->guessExtension();
                        $uploadedPicture->move(
                            $this->getParameter('picture_directory'),
                            $filename
                        );
                        $trick->addPicture($filename);
                        $nextImageNumber++;
                    }
                }
            

            // Met à jour la date de mise à jour
            $trick->setUpdateAt(new \DateTime());

            // Enregistre les modifications dans la base de données
            $tricksRepository->save($trick, true);

            // Ajoute un message de succès dans la session
            $session->getFlashBag()->add('successEdit', 'Le trick a été modifié avec succès !');

            //return $this->redirectToRoute('app_tricks_index', [], Response::HTTP_SEE_OTHER);
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('tricks/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }
    //Permet de reprendre le numéro de l'image supprimée pour la nouvelle image
    private function getImageNumberFromName(string $imageName): ?int
    {
        $imageName = pathinfo($imageName, PATHINFO_FILENAME);
        if (preg_match('/_\d+$/', $imageName, $matches)) {
            return (int)trim($matches[0], '_');
        }
        return null;
    }

    #[Route('/delete/{id}', name: 'app_tricks_delete', methods: ['POST'])]
    public function delete(Request $request, Tricks $trick, TricksRepository $tricksRepository, CommentRepository $commentRepository, SessionInterface $session, SecurityController $lastUsername): Response
    {
        //On vérifie si l'utilisateur est bien connecté et si son compte est vérifié
        $user = $lastUsername->getUser();
        if (!$user || !$user->isVerified()) {
            $session->getFlashBag()->add('error', 'Vous devez être connecté et avoir un compte vérifié pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }

        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            //Supprime les commentaires liés au trick
            $commentRepository->deleteCommentsByTrick($trick);
            //Supprime le trick
            $tricksRepository->remove($trick, true);
            // Ajoute un message de succès dans la session
            $session->getFlashBag()->add('danger', 'Le trick a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Tricks;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


#[Route('/comment')]
class CommentController extends AbstractController
{

    
    //Liste des commentaires
    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository, Tricks $trick, TricksRepository $tricksRepository, $id): Response
    {

        return $this->render('tricks/show.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);

    }

    
    //Création d'un commentaire
    #[Route('/new/{id}', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CommentRepository $commentRepository, SecurityController $lastUsername, Tricks $trick, TricksRepository $tricksRepository, $id, SessionInterface $session): Response
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
            $successMessageComment = $session->getFlashBag()->get('successMessageComment', []);

            return $this->redirectToRoute('app_tricks_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'successMessageComment' => $successMessageComment,
        ]);

    }

    
    //Montre un commentaire
    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {

        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);

    }

    
    //Modifie un commentaire
    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentRepository->save($comment, true);

            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);

    }

    
    //Supprime un commentaire
    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment, true);
        }

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);

    }

    
    //Fonction servant à réccupérer les commentaires en format json pour l'ajax géré par le bouton "charger plus de commentaires"
    #[Route('/load/{id}', name: 'app_load_comments', methods: ['GET'])]
    public function loadCommentsAction(Request $request, PaginatorInterface $paginator, Tricks $trick, TricksRepository $tricksRepository, $id, CommentRepository $commentRepository): JsonResponse
    {

     $offset = $request->query->get('offset', 0);
     $limit = 5;

    // Récupération des commentaires en utilisant la méthode personnalisée du repository
     $trick = $tricksRepository->find($id);
     $comments = $commentRepository->findPaginatedCommentsByTrick($trick, $offset, $limit);

     $commentsArray = [];
     foreach ($comments as $comment) {
        $user = $comment->getUser();
        $username = $user ? $user->getUsername() : 'Utilisateur inconnu';
        $profile_picture = $user ? $user->getProfilePicture() : 'assets/img/profiles/default.jpg' ;
        $commentsArray[] = [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'createAt' => $comment->getCreateAt()->format('d-m-Y H:i:s'),
            'user' => $username,
            'profilePicture' => $profile_picture,
        ];
    }

    // Retournez les commentaires sous forme de JSON
    return new JsonResponse($commentsArray);

    }

}


<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    
    // Affiche la liste des utilisateurs
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);

    }

    
    // Créer un nouvel utilisateur
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);

    }

    
    // Montre le profil de l'utilisateur
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user, Security $security, UserRepository $userRepository, SessionInterface $session): Response
    {

        $user = $security->getUser();
        // Si aucun utilisateur de connecté
        if (!$user) {
            // On redirige vers la page de connexion
            return $this->redirectToRoute('app_home');
        }
        $userId = $user->getId();

        if (!$userId) {
            // On redirige vers la page de connexion
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);

    }

    
    // Modifie le profil de l'utilisateur
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {

            $avatar = $form->get('profile_picture')->getData();

            if ($avatar !== null && $avatar !== $user->getProfilePicture()) {
                $newAvatar = $form->get('profile_picture')->getData();
                $avatarName = $this->generateUniqueFileName() . '.' . $avatar->guessExtension();
                $newAvatar->move(
                    $this->getParameter('avatar_directory'),
                    $avatarName
                );
                $user->setProfilePicture($avatarName);
            }

            $userRepository->save($user, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);

    }

    
    // Supprime le compte utilisateur
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);

    }

    /**
     * Generate unique file name
     *
     * @return string
     */
    private function generateUniqueFileName(): string
    {

        return uniqid('', true);

    }

}

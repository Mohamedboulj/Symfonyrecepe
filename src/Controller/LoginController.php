<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'security.login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('pages/login/index.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    #[Route('/register', name: 'register')]
    public function register(EntityManagerInterface $em, Request $request)
    {
        $user = new User();
        $user->setRoles(['USER ROLE']);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $this->addFlash('success', 'Compte cree avec succes !');
            $em->persist($user);
            $em->flush();
            return $this->render('pages/login/index.html.twig', ['last_username' => $user->getEmail(), 'error' => '']);
        }
        return $this->render('pages/login/register.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/update/{id}', name: 'security.update')]
    public function update(int $id, UserRepository $repository, Request $request, EntityManagerInterface $em)
    {
        $user = $repository->find($id);
        if (!$this->getUser()) {
            return $this->redirectToRoute('pages/login/index.html.twig');
        }
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('security.update');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $this->addFlash('success', 'Compte modifie avec succes !');
            $em->flush();
            return $this->render('pages/login/index.html.twig', ['last_username' => $user->getEmail(), 'error' => '']);
        }
        return $this->render('pages/user/update.html.twig', ['form' => $form->createView()]);
    }
}

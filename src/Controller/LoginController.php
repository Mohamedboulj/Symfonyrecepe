<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
            $em->persist($user);
            $em->flush();
            return $this->render('pages/login/index.html.twig', ['last_username' => $user->getEmail(), 'error' => '']);
        }
        return $this->render('pages/login/register.html.twig', ['form' => $form->createView()]);
    }
}

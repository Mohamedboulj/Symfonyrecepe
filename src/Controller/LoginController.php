<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('user/update/{id}', name: 'security.update')]
    #[Security("is_granted('ROLE_USER') and user === user")]
    public function update(int                         $id,
                           UserRepository              $repository,
                           Request                     $request,
                           EntityManagerInterface      $em,
                           UserPasswordHasherInterface $hasher
    )
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
            if ($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())) {
                $user = $form->getData();
                $this->addFlash('success', 'Compte modifie avec succes !');
                $em->flush();
                return $this->redirectToRoute('recipe.index', ['user' => $user]);
            } else {
                $this->addFlash('failure', 'mot de passe erronee !');
            }
        }
        return $this->render('pages/user/update.html.twig', ['form' => $form->createView()]);
    }

    #[Route('user/reset-password/{id}', name: 'reset.password')]
    #[Security("is_granted('ROLE_USER') and user === user")]
    public function reset(User                        $user,
                          Request                     $request,
                          UserPasswordHasherInterface $hasher,
                          EntityManagerInterface      $em
    )
    {
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if (!$this->getUser()) {
            return $this->redirectToRoute('pages/login/index.html.twig');
        }
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('security.update');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()["plainPassword"])) {
                $user->setPlainPassword(
                    $form->getData()["newPassword"]
                );
                $user->setUpdatedAt(new \DateTimeImmutable());
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'le mot de passe est change');
                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash('failure', 'le mot de passe est incorrect');
            }
        }
        return $this->render('pages/user/pw-reset.html.twig', ['form' => $form->createView()]);
    }

    #[Route('logout', name: 'security.logout')]
    public function logout()
    {

    }
}

<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ingredient', name: 'ingredient.')]
class IngredientController extends AbstractController
{
    private Paginator $paginator;

    public function __construct(PaginatorInterface $paginator, readonly EntityManagerInterface $em)
    {
        $this->paginator = $paginator;
    }

    #[Route('/list', name: 'list')]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $ingredientRepository, Request $request): Response
    {
        $ingredients = $this->paginator->paginate(
            $ingredientRepository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10 /*limit per page*/
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): response
    {
        $ingredient = new Ingredient();
        $ingredient->setUser($this->getUser());
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $this->em->persist($ingredient);
            $this->em->flush();
            $this->addFlash('success', 'Ingredient has been created successfully');
            return $this->redirectToRoute('ingredient.list');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    #[Security("is_granted('ROLE_USER') and user === ingredient.GetUser()")]
    public function update(Request $request, Ingredient $ingredient)
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $this->em->persist($ingredient);
            $this->em->flush();
            $this->addFlash('success', 'Ingredient has been modified successfully');
            return $this->redirectToRoute('ingredient.list');
        }
        return $this->render('pages/ingredient/update.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/delete/{id}', name: 'delete')]
    #[Security("is_granted('ROLE_USER') and user === ingredient.GetUser()")]
    public function delete(?Ingredient $ingredient)
    {
        if (!$ingredient) {
            $this->addFlash('warning', 'There is no such ingredient !');
            return $this->redirectToRoute('ingredient.list');
        }
        $this->em->remove($ingredient);
        $this->em->flush();
        $this->addFlash('success', 'Ingredient has been deleted successfully');
        return $this->redirectToRoute('ingredient.list');
    }
}

<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recipe', name: 'recipe.')]
class RecipeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(RecipeRepository   $repository,
                          Request            $request,
                          PaginatorInterface $paginator): Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10 /*limit per page*/
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $recipe = new Recipe();
        $recipe->setUser($this->getUser());
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Recipe has been created successfully');
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(Request $request, ?Recipe $recipe, EntityManagerInterface $em)
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Recipe has been modified successfully');
            return $this->redirectToRoute('ingredient.index');
        }
        return $this->render('pages/recipe/update.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(?Recipe $recipe, EntityManagerInterface $em)
    {
        if (!$recipe) {
            $this->addFlash('warning', 'There is no such recipe !');
            return $this->redirectToRoute('recipe.index');
        }
        $em->remove($recipe);
        $em->flush();
        $this->addFlash('success', 'recipe has been deleted successfully');
        return $this->redirectToRoute('recipe.index');
    }


}

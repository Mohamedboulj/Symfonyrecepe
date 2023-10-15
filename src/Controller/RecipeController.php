<?php

namespace App\Controller;

use App\Entity\Rate;
use App\Entity\Recipe;
use App\Form\RateType;
use App\Form\RecipeType;
use App\Repository\RateRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recipe', name: 'recipe.')]
class RecipeController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[IsGranted('ROLE_USER')]
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

    #[Route('/show/{id}', name: 'show')]
    #[Security("is_granted('ROLE_USER') and (recipe.IsPublic() === true || user === recipe.GetUser()) ")]
    public function show(?Recipe                $recipe,
                         Request                $request,
                         EntityManagerInterface $em,
                         RateRepository         $rateRepository
    )
    {
        $rate = new Rate();
        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $existingRate = $rateRepository->findBy(['user' => $this->getUser(), 'recipe' => $recipe]);
            $rate->setRate($data->getRate())
                ->setUser($this->getUser())
                ->setRecipe($recipe);
            if ($recipe->getUser() === $this->getUser()) {
                $this->addFlash('failure', 'Vous ne pouvez pas évaluer votre propre recette ');
                return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
            }
            if ($existingRate) {
                $this->addFlash('failure', 'Vous avez déja laissé un avis ');
                return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
            }
            $this->addFlash('success', 'Votre note est prise en consideration');
            $em->persist($rate);
            $em->flush();
        }
        return $this->render('pages/recipe/show.html.twig', ['recipe' => $recipe, 'form' => $form->createView()]);
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
        dd($form->getData());
        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    #[Security("is_granted('ROLE_USER') and user === recipe.GetUser()")]
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
    #[Security("is_granted('ROLE_USER') and user === recipe.GetUser()")]
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

<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'index.public')]
    public function indexPublic(RecipeRepository   $repository,
                                Request            $request,
                                PaginatorInterface $paginator,
    ): Response
    {
        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(),
            $request->query->getInt('page', 1),
            10 /*limit per page*/
        );

        return $this->render('pages/home/publicIndex.html.twig', [
            'recipes' => $recipes
        ]);
    }
}

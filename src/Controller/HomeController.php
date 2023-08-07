<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Article;

class HomeController extends AbstractController
{
    /**
     * Page d'accueil
     * 
     * @return Response
     */
    #[Route(path: '/', name: 'homepage')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Toutes les donnees de l'entite ou classe Article en BDD
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}

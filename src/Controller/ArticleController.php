<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Article;

class ArticleController extends AbstractController
{
    /**
     * Visualiser un article
     *
     * @param int $id identifiant de l'article
     *
     * @return Response
     */
    public function index(EntityManagerInterface $entityManager, int $id): Response
    {
        // On recupere l'article qui correspond à l'id passe dans l'url
        $article = $entityManager->getRepository(Article::class)->findBy(['id' => $id]);

        return $this->render('article/index.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Modifier ou ajouter un article
     *
     * @param int $id Identifiant de l'article
     *
     * @return Response
     */
    public function edit(EntityManagerInterface $entityManager, Request $request, int $id = null): Response
    {
        // Si un identifiant est présent dans l'url alors il s'agit d'une modification
        // Dans le cas contraire il s'agit d'une création d'article
        if ($id) {
            $mode = 'update';
            // On récupère l'article qui correspond à l'id passé dans l'url
            $article = $entityManager->getRepository(Article::class)->findBy(['id' => $id]);
        } else {
            $mode = 'new';
            $article = new Article();
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveArticle($entityManager, $article, $mode);

            return $this->redirectToRoute('article_edit', array('id' => $article->getId()));
        }

        $parameters = array(
            'form' => $form->createView(),
            'article' => $article,
            'mode' => $mode
        );

        return $this->render('article/edit.html.twig', $parameters);
    }

    /**
     * Supprimer un article
     *
     * @param int $id Identifiant de l'article
     *
     * @return Response
     */
    public function remove(EntityManagerInterface $entityManager, int $id): Response
    {
        $article = $entityManager->getRepository(Article::class)->findBy(['id' => $id])[0];
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * Completer l'article avec des informations avant enregistrement
     *
     * @param Article $article
     * @param string $mode
     *
     * @return Article
     */
    private function completeArticleBeforeSave(Article $article, string $mode)
    {
        if ($article->isIsPublished()) {
            $article->setPublishedAt(new \DateTime());
        }
        $article->setAuthor($this->getUser());

        return $article;
    }

    /**
     * Enregistrer un article en BDD
     *
     * @param Article $article
     * @param string $mode
     */
    private function saveArticle(EntityManagerInterface $entityManager, Article $article, string $mode)
    {
        $article = $this->completeArticleBeforeSave($article, $mode);

        $entityManager->persist($article);
        $entityManager->flush();
        $this->addFlash('success', 'Enregistré avec succès');
    }
}

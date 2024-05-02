<?php
namespace App\Controller;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
Use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use App\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\ArticleType;
use App\Entity\Category;
use App\Entity\PropertySearch;
use App\Form\PropertySearchType;
use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchType;


class IndexController extends AbstractController
{
  #[Route('/', name: 'article_list')]
  // public function home(EntityManagerInterface  $entityManager): Response
  //   {
  //       $articles = $entityManager->getRepository(Article::class)->findAll();
  //       return $this->render('articles/index.html.twig',['articles'=> $articles]);
  //   }
  public function home(Request $request ,PersistenceManagerRegistry $managerRegistry)
  {
  $propertySearch = new PropertySearch();
  $form = $this->createForm(PropertySearchType::class,$propertySearch);
  $form->handleRequest($request);
  //initialement le tableau des articles est vide,
  //c.a.d on affiche les articles que lorsque l'utilisateur
  //clique sur le bouton rechercher
  $articles= [];
 
  if($form->isSubmitted() && $form->isValid()) {
  //on récupère le nom d'article tapé dans le formulaire
  $Nom = $propertySearch->getNom();
  if ($Nom!="")
  //si on a fourni un nom d'article on affiche tous les articles ayant ce nom
  $articles= $managerRegistry->getRepository(Article::class)->findBy(['Nom' => $Nom] );
  else
  //si si aucun nom n'est fourni on affiche tous les articles
  $articles= $managerRegistry->getRepository(Article::class)->findAll();
  }
  return $this->render('articles/index.html.twig',[ 'form' =>$form->createView(), 'articles' => $articles]);
  }


   #[Route('/new', name: 'new_article', methods:['GET','POST'])]
    public function new(PersistenceManagerRegistry $managerRegistry,Request $request)  {
      $article = new Article();
      $form = $this->createForm(ArticleType::class,$article);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()) 
      { 
        $article = $form->getData();
        $entityManager =$managerRegistry->getManager();
        $entityManager->persist($article);
        $entityManager->flush();
        return $this->redirectToRoute('article_list');
    }
    return $this->render('articles/new.html.twig',['form' => $form->createView()]);
  }
     
 

     #[Route('/save', name: 'save-article')]
 public function save(PersistenceManagerRegistry $doctrine){
    $entityManager = $doctrine->getManager();
    $article = new Article();
    $article->setNom('Article 3');
    $article->setPrix(2080);
   
    $entityManager->persist($article);
    $entityManager->flush();
    return new Response('Article enregisté avec id '.$article->getId());
 }


 #[Route('/article/{id}', name:"article_show")]
 public function show(PersistenceManagerRegistry $managerRegistry,$id)  {
   $article=$managerRegistry->getRepository(Article::class)->find($id);
   return $this->render('articles/show.html.twig', array('article' => $article)); 
 }

  //Modifier un article
  #[Route('/article/edit/{id}',name:"edit_article",methods:['GET','POST'])]
  public function edit(PersistenceManagerRegistry $managerRegistry,Request $request,$id)  {
    $article = new Article();
    $article=$managerRegistry->getRepository(Article::class)->find($id);
    $form = $this->createForm(ArticleType::class,$article);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) 
    { 
      $entityManager = $managerRegistry->getManager(); 
      $entityManager->flush(); 
      return $this->redirectToRoute('article_list');
  }
  return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
}

#[Route('/article/delete/{id}', name: "delete_article")]
public function delete(PersistenceManagerRegistry $managerRegistry, Request $request, int $id): Response
{
    $article = $managerRegistry->getRepository(Article::class)->find($id);

    if (!$article) {
      
    }
    $entityManager = $managerRegistry->getManager();
    $entityManager->remove($article);
    $entityManager->flush();
    return $this->redirectToRoute('article_list');
}

#[Route('/category/newCat', name: 'new_category', methods:['GET','POST'])]
public function newCategory(PersistenceManagerRegistry $managerRegistry,Request $request) {
  $category = new Category();
  $form = $this->createForm(CategoryType::class,$category);
  $form->handleRequest($request);
  if($form->isSubmitted() && $form->isValid()) {
    $category = $form->getData();
    $entityManager=$managerRegistry->getManager();
    $entityManager->persist($category);
    $entityManager->flush();

  }
  return $this->render('categ/newCategory.html.twig',['form'=> $form->createView()]);
}

#[Route('/art_cat/', name: 'article_par_cat', methods:['GET','POST'])]
public function articlesParCategorie(Request $request,PersistenceManagerRegistry $managerRegistry) {
  $categorySearch = new CategorySearch();
  $form = $this->createForm(CategorySearchType::class,$categorySearch);
  $form->handleRequest($request);
  $articles= [];
  if($form->isSubmitted() && $form->isValid()) {
    $category = $categorySearch->getCategory();
   
    if ($category!="")
   $articles= $category->getArticles();
    else
    $articles= $managerRegistry->getRepository(Article::class)->findAll();
    }
   
    return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),'articles' => $articles]);
    }
   

/**
 * @Route("/art_prix/", name="article_par_prix")
 * Method({"GET"})
 */
#[Route('/art_prix/', name: 'article_par_prix', methods:['GET','POST'])]
public function articlesParPrix(Request $request,PersistenceManagerRegistry $managerRegistry)
{

$priceSearch = new PriceSearch();
$form = $this->createForm(PriceSearchType::class,$priceSearch);
$form->handleRequest($request);
$articles= [];
if($form->isSubmitted() && $form->isValid()) {
$minPrice = $priceSearch->getMinPrice();
$maxPrice = $priceSearch->getMaxPrice();

$articles= $managerRegistry->
getRepository(Article::class)->findByPriceRange($minPrice,$maxPrice);
}
return $this->render('articles/articlesParPrix.html.twig',[ 'form' =>$form->createView(), 'articles' => $articles]);
}

}

<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{

    #[Route('/products', name: 'products')]
    public function index(ProductRepository $productsRepository): Response
    {
        $products = $productsRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/products/{slug}', name: 'products_show')]
    public function show($slug, ProductRepository $productRepository): Response
    {
        $products = $productRepository->findOneBy(['slug' => $slug]);
        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/admin/products', name: 'admin_products')]
    public function adminList(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('product/adminList.html.twig', [
            'products' => $products
        ]);
    }
    #[Route('/admin/product/create', name: 'product_create')]
    public function create(Request $request, ProductRepository $productRepository, ManagerRegistry $managerRegistry): Response
    {
        $product = new Product(); 
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) { 

            $products = $productRepository->findAll(); 
            $productNames = []; 
            foreach ($products as $product) { 
                $productNames[] = strtolower($product->getName()); 
            }
            if (in_array(strtolower($form['name']->getData()), $productNames)) { 
                $this->addFlash('danger', 'Le produit n\'a pas pu être créé : le nom de produit est déjà utilisé');
                return $this->redirectToRoute('admin_products');
            }

            $infoImg = $form['img']->getData(); 

            if (empty($infoImg)) { 
                $this->addFlash('danger', 'Le produit n\'a pas pu être créé : l\'image principale est obligatoire mais n\'a pas été renseignée');
                return $this->redirectToRoute('admin_products');
            }

            $extensionImg = $infoImg->guessExtension();
            $nomImg= time() . '-1.' . $extensionImg;
            $infoImg->move($this->getParameter('product_image_dir'), $nomImg); 
            $product->setImg($nomImg); 

            // $slugger = new AsciiSlugger();
            // $product->setSlug(strtolower($slugger->slug($form['name']->getData()))); 
            // $product->setCreatedAt(new DateTimeImmutable());

            $manager = $managerRegistry->getManager();
            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Le produit a bien été créé'); // message de succès
            return $this->redirectToRoute('admin_products');
        }

        return $this->render('product/form.html.twig', [
            'productForm' => $form->createView()
        ]);
    }

    #[Route('/admin/product/update/{id}', name: 'product_update')]
    public function update(Product $product, ProductRepository $productRepository, Request $request, ManagerRegistry $managerRegistry): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $products = $productRepository->findAll(); // récupère tous les produits en base de données
            // $productNames = []; // initialise un tableau pour les noms de produits
            // foreach ($products as $product) { // pour chaque produit récupéré
            //     $productNames[] = $product->getName(); // stocke le nom du produit dans le tableau
            // }
            // if (in_array($form['name']->getData(), $productNames)) { // vérifie si le nom du produit à créer n'est pas déjà utilisé en base de données
            //     $this->addFlash('danger', 'Le produit n\'a pas pu être modifié : le nom de produit est déjà utilisé');
            //     return $this->redirectToRoute('admin_products');
            // }

            $infoImg = $form['img']->getData(); // récupère les informations de l'image 1 dans le formulaire
            if ($infoImg !== null) { // s'il y a bien une image donnée dans le formulaire
                $oldImgName = $product->getImg(); // récupère le nom de l'ancienne image
                $oldImgPath = $this->getParameter('product_image_dir') . '/' . $oldImgName; // récupère le chemin de l'ancienne image 1
                if (file_exists($oldImgPath)) {
                    unlink($oldImgPath); // supprime l'ancienne image 1
                }
                $extensionImg = $infoImg->guessExtension(); // récupère l'extension de fichier de l'image 1
                $nomImg = time() . '-1.' . $extensionImg; // crée un nom de fichier unique pour l'image 1
                $infoImg->move($this->getParameter('product_image_dir'), $nomImg); // télécharge le fichier dans le dossier adéquat
                $product->setImg($nomImg); // définit le nom de l'image à mettre ne base de données
            }



            // $slugger = new AsciiSlugger();
            // $product->setSlug(strtolower($slugger->slug($form['name']->getData())));
            $manager = $managerRegistry->getManager();
            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Le produit a bien été modifié');
            return $this->redirectToRoute('admin_products');
        }

        return $this->render('product/form.html.twig', [
            'productForm' => $form->createView()
        ]);
    }

    #[Route('/admin/product/delete/{id}', name: 'product_delete')]
    public function delete(Product $product, ManagerRegistry $managerRegistry): Response
    {
        $imgpath = $this->getParameter('product_image_dir') . '/' . $product->getImg();
        if (file_exists($imgpath)) {
            unlink($imgpath);
        }

        $manager = $managerRegistry->getManager();
        $manager->remove($product);
        $manager->flush();

        $this->addFlash('success', 'Le produit a bein été supprimé');
        return $this->redirectToRoute('admin_products');
    }
}

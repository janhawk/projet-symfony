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
            foreach ($products as $productDb) { 
                $productNames[] = strtolower($productDb->getName()); 
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

    

            $infoImg = $form['img']->getData(); 
            if ($infoImg !== null) { 
                $oldImgName = $product->getImg(); 
                $oldImgPath = $this->getParameter('product_image_dir') . '/' . $oldImgName; 
                if (file_exists($oldImgPath)) {
                    unlink($oldImgPath); 
                }
                $extensionImg = $infoImg->guessExtension(); 
                $nomImg = time() . '-1.' . $extensionImg; 
                $infoImg->move($this->getParameter('product_image_dir'), $nomImg);
                $product->setImg($nomImg); 
            }



          
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

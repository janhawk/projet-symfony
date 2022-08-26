<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'categories', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/category/{slug}', name: 'category', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }

    #[Route('/admin/category', name: 'admin_categories')]
    public function adminIndex(CategoryRepository $categoryRepository)
    {
        return $this->render('category/adminList.html.twig', [
            'categories' => $categoryRepository->findAll()
        ]);
    }

    #[Route('/admin/category/create', name: 'category_create', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository, ManagerRegistry $managerRegistry): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categories = $categoryRepository->findAll();
            $categoryNames = [];
            foreach ($categories as $categoryRecup) {
                $categoryNames[] = strtolower($categoryRecup->getName());
            }
            if (in_array(strtolower($form['name']->getData()), $categoryNames)) {
                $this->addFlash('danger', 'La catégorie n\'a pas pu être créée : le nom de catégorie est déjà utilisé');
                return $this->redirectToRoute('admin_categories');
            }

            $infoImg = $form['img']->getData();
            if (!empty($infoImg)) {
                $extensionImg = $infoImg->guessExtension();
                $nomImg = time() . '.' . $extensionImg;
                $infoImg->move($this->getParameter('category_image_dir'), $nomImg);
                $category->setImg($nomImg);
            }

            // $slugger = new AsciiSlugger();
            // $category->setSlug(strtolower($slugger->slug($form['name']->getData())));

            $manager = $managerRegistry->getManager();
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', 'La catégorie a bien été créée');
            return $this->redirectToRoute('admin_categories', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/form.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }


    #[Route('/admin/category/update/{id}', name: 'category_update', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository, ManagerRegistry $managerRegistry): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $categories = $categoryRepository->findAll();
            // $categoryNames = [];
            // foreach ($categories as $categoryRecup) {
            //     $categoryNames[] = $categoryRecup->getName();
            // }
            // if (in_array($form['name']->getData(), $categoryNames)) {
            //     $this->addFlash('danger', 'La catégorie n\'a pas pu être modifiée : le nom de catégorie est déjà utilisé');
            //     return $this->redirectToRoute('admin_categories');
            // }

            $infoImg = $form['img']->getData();
            if ($infoImg !== null) {
                $oldImg = $this->getParameter('category_image_dir') . '/' . $category->getImg();
                if ($category->getImg() !== null && file_exists($oldImg)) {
                    unlink($oldImg);
                }
                $extensionImg = $infoImg->guessExtension(); 
                $nomImg = time() . '.' . $extensionImg;
                $infoImg->move($this->getParameter('category_image_dir'), $nomImg);
                $category->setImg($nomImg);
            }

            $manager = $managerRegistry->getManager();
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', 'La catégorie a bien été modifiée');

            return $this->redirectToRoute('admin_categories', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/form.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/admin/category/delete/{id}', name: 'category_delete', methods: ['GET', 'POST'])]
    public function delete(Category $category, ManagerRegistry $managerRegistry): Response
    {
        if ($category->getProducts()->isEmpty() === false){
            $this->addFlash('danger', 'La catégorie ne peut pas être supprimée car elle contient des produits. Veuillez supprimer ces produits avant de réessayer.');
            return $this->redirectToRoute('admin_categories');
        }
        $img = $this->getParameter('category_image_dir') . '/' . $category->getImg();
        if ($category->getImg() != null && file_exists($img)) {
            unlink($img);
        }
        $manager = $managerRegistry->getManager();
        $manager->remove($category);
        $manager->flush();
        return $this->redirectToRoute('admin_categories');
    }
}

<?php

namespace App\Controller;
use App\Entity\Fruits;
use DateTimeImmutable;
use App\Form\FruitsType;
use App\Repository\FruitsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FruitsController extends AbstractController
{

    #[Route('/fruitss', name: 'fruitss')]
    public function index(FruitsRepository $fruitsRepository): Response
    {
        $fruitss = $fruitsRepository->findAll();
        return $this->render('fruits/index.html.twig', [
            'fruitss' => $fruitss,
        ]);
    }

    #[Route('/fruits/{slug}', name: 'fruits_show')]
    public function show($slug, FruitsRepository $productRepository): Response
    {
        $fruits = $fruitsRepository->findOneBy(['slug' => $slug]);
        return $this->render('fruits/show.html.twig', [
            'fruits' => $product
        ]);
    }

    #[Route('/admin/fruitss', name: 'admin_fruitss')]
    public function adminList(FruitsRepository $fruitsRepository): Response
    {
        $fruitss = $fruitsRepository->findAll();
        return $this->render('fruits/adminList.html.twig', [
            'fruitss' => $fruitss
        ]);
    }
    #[Route('/admin/fruits/create', name: 'fruits_create')]
    public function create(Request $request, FruitsRepository $fruitsRepository, ManagerRegistry $managerRegistry): Response
    {
        $fruits = new Fruits(); 
        $form = $this->createForm(FruitsType::class, $fruits); 
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) { 

            // $fruitss = $fruitsRepository->findAll(); 
            // $fruitsNames = []; 
            // foreach ($fruitss as $fruits) { 
            //     $fruitsNames[] = strtolower($fruits->getName()); 
            // }
            // if (in_array(strtolower($form['name']->getData()), $fruitsNames)) { 
            //     $this->addFlash('danger', 'Le produit n\'a pas pu être créé : le nom de produit est déjà utilisé');
            //     return $this->redirectToRoute('admin_fruitss');
            // }

            $infoImg1 = $form['img1']->getData(); 

            if (empty($infoImg1)) { 
                $this->addFlash('danger', 'Le produit n\'a pas pu être créé : l\'image principale est obligatoire mais n\'a pas été renseignée');
                return $this->redirectToRoute('admin_fruitss');
            }

            $extensionImg1 = $infoImg1->guessExtension();
            $nomImg1 = time() . '-1.' . $extensionImg1;
            $infoImg1->move($this->getParameter('fruits_image_dir'), $nomImg1); 
            $fruits->setImg1($nomImg1); 
            $slugger = new AsciiSlugger();
            $fruits->setSlug(strtolower($slugger->slug($form['name']->getData()))); 
            $fruits->setCreatedAt(new DateTimeImmutable());

            $manager = $managerRegistry->getManager();
            $manager->persist($fruits);
            $manager->flush();

            $this->addFlash('success', 'Le produit a bien été créé'); // message de succès
            return $this->redirectToRoute('admin_fruitss');
        }

        return $this->render('fruits/form.html.twig', [
            'fruitsForm' => $form->createView()
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/afficheuser", name="afficheuser")
     */
    public function affiche()
    {
        $repository = $this->getDoctrine()->getrepository(User::Class);//recuperer repisotory
        $users = $repository->findAll();//affichage
        return $this->render('user\index.html.twig', [
            'users' => $users,
        ]);//liasion twig avec le controller
    }
//

    /**
     * @Route("/Ajouteuser", name="Ajouteuser")
     */
    public function ajouter(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $users = new User();//creation instance
        $form = $this->createForm(UserType::class, $users);//Récupération du formulaire dans le contrôleur:
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();//recupuration entity manager
            $users->setPassword(
                $passwordEncoder->encodePassword($users, $users->getPassword())
            );
            $em->persist($users);//l'ajout de la objet cree
            $em->flush();

            return $this->redirectToRoute('app_login');
            //redirecter la pagee la page dafichage
        }

        return $this->render('front\ajout.html.twig', [
            'form' => $form->createview()
        ]);

    }

    /**
     * @Route("user/supp/{id}", name="d")
     */
    public function supprimer ($id)
    {
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($user);//suprrimer lobjet dans le parametre
        $em->flush();
        return $this->redirectToRoute('afficheuser');

    }
    /**
     * @route ("user/modifier/{id}", name="u")
     */
    function modifier(UserRepository $repository,Request $request,$id)
    {
        $users = $repository->find($id);
        $form = $this->createForm(UserType::class, $users);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('afficheuser');
        }
        return $this->render('user/modifier.html.twig', [
            'form' => $form->createView()
        ]);

    }



    /**
     * @route ("user\search", name="search")
     */
    function searchTitle(UserRepository $repository,Request $request )
    {
        $data1=$request->get('find1');
        $data2=$request->get('find2');
        $users=$repository->findBy(array('nom'=>$data1,'prenom'=>$data2));
        return $this->render('user\index.html.twig', [
            'users' => $users,
        ]);



    }

    /**
     * @Route("/home", name="home")
     */
    public function index(): Response
    {
        return $this->render('front\home.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    /**
     * @Route("/back", name="back")
     */
    public function back(): Response
    {
        return $this->render('user\Global.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    /**
     * @route ("user\Order", name="orderD")
     */
    function Orderadresse(UserRepository $repository,Request $request )
    {

        $repository = $this->getDoctrine()->getrepository(User::Class);//recuperer repisotory
        $users = $repository->findBy(
            array(),
            array('id' => 'DESC')
        );
        return $this->render('user\index.html.twig',
            ['users' => $users]);

    }
    /**
     * @route ("user\Orders", name="orderA")
     */
    function Ordereadres(UserRepository $repository,Request $request )
    {

        $repository = $this->getDoctrine()->getrepository(User::Class);//recuperer repisotory
        $users = $repository->findBy(
            array(),
            array('id' => 'ASC')
        );
        return $this->render('user\index.html.twig',
            ['users' => $users]);

    }
    /**
     * @Route("/SearchUserx ", name="SearchUserx")
     */
    public function searchUserx(Request $request,NormalizerInterface $Normalizer)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $requestString=$request->get('searchValue');
        $students = $repository->findUsereByNom($requestString);
        $jsonContent = $Normalizer->normalize($students, 'json',['groups'=>'hamdi']);
        $retour=json_encode($jsonContent);
        return new Response($retour);

    }

}

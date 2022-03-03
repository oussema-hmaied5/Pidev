<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Form\UserType;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ReclamationController extends AbstractController
{
    /**
     * @Route("/Ajoutereclamation/add/{id}", name="Ajoutereclamation")
     */
    public function ajouter(Request $request, $id, UserRepository $repository)
    {

        $reclamations = new Reclamation();//creation instance
        $user=$repository->find($id);
        $reclamations->setUser($user);
        $form = $this->createForm(ReclamationType::class, $reclamations);//Récupération du formulaire dans le contrôleur:
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();//recupuration entity manager
            $em->persist($reclamations);//l'ajout de la objet cree
            $em->flush();
            return $this->redirectToRoute('home');//redirecter la pagee la page dafichage
        }

        return $this->render('reclamation\ajouter.html.twig', [
            'form' => $form->createview()
        ]);



        $offre=$repository->find($id);
        $demande->setRelatedOffre($offre);


    }




    /**
     * @Route("/affichereclamation", name="affichereclamation")
     */
    public function affiche()
    {
        $repository = $this->getDoctrine()->getrepository(Reclamation::Class);//recuperer repisotory
        $reclamation = $repository->findAll();//affichage
        return $this->render('reclamation\reclamation.html.twig', [
            'reclamation' => $reclamation,
        ]);//liasion twig avec le controller
    }
    /**
     * @Route("/supp/{id}", name="s")
     */
    public function supprimer ($id)
    {
        $reclamations=$this->getDoctrine()->getRepository(Reclamation::class)->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($reclamations);//suprrimer lobjet dans le parametre
        $em->flush();
        return $this->redirectToRoute('affichereclamation');

    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/afficher", name="afficher")
     */
    public function afficher()
    {
        $repository = $this->getDoctrine()->getrepository(Reclamation::Class);//recuperer repisotory
        $reclamation = $repository->findAll();//affichage
        return $this->render('front\afficher.html.twig', [
            'reclamation' => $reclamation,
        ]);//liasion twig avec le controller
    }
    /**
     * @Route("/pdf ", name="pdf")
     */
    public function pdf(ReclamationRepository $Repository)
    {
        $reclamation = $Repository->findall();
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('reclamation/pdf.html.twig', [
            'title' => "Welcome to our PDF Test",
            'reclamation' =>$reclamation
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("la liste de Reclamation.pdf", [
            "Attachment" => true
        ]);
    }
    /**
     *
     * @Route ("/mail/{id}", name="mail")
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function Mailing(\Swift_Mailer $mailer,$id,ReclamationRepository $Repository)
    {
        $repository = $this->getDoctrine()->getrepository(Reclamation::Class);
        $reclamations = $repository->findBy(
            ['id' => $id]
        );
        $message = (new \Swift_Message('Service Project E-esprit'))
            ->setFrom('Oussama.hamaied@gmail.com')
            ->setTo('Oussema.hmaied1@esprit.tn')
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'reclamation/mailo.html.twig',
                    ['reclamations' => $reclamations]
                ),
                'text/html'

            );

        $mailer->send($message);


        return $this->redirectToRoute('affichereclamation',[
            'id' => $id
        ]);
    }

    /**
     * @Route("/Reclamation/find/{id}", name="z")
     */
    public function listeRec($id)
    {
        $repository = $this->getDoctrine()->getrepository(Reclamation::Class);//recuperer repisotory
        $reclamation = $repository->findBy(
            ['user' => $id]
        );
        return $this->render('front\afficher.html.twig', [
            'reclamation' => $reclamation,
        ]);//liasion twig avec le controller
    }
}




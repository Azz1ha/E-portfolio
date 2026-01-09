<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

final class HerController extends AbstractController
{
    #[Route('/', name: 'app_Her')]
    public function index(): Response
    {
        return $this->render('her/index.html.twig', [
            'controller_name' => 'Her',
        ]);
    }

    #[Route('/competences/administrer', name: 'app_competence_administrer')]
    public function administrer(): Response
    {
        return $this->render('her/competences/administrer.html.twig', [
            'competence' => 'Administrer',
        ]);
    }

    #[Route('/competences/connecter', name: 'app_competence_connecter')]
    public function connecter(): Response
    {
        return $this->render('her/competences/connecter.html.twig', [
            'competence' => 'Connecter',
        ]);
    }

    #[Route('/competences/programmer', name: 'app_competence_programmer')]
    public function programmer(): Response
    {
        return $this->render('her/competences/programmer.html.twig', [
            'competence' => 'Programmer',
        ]);
    }

    #[Route('/projets/projet-1', name: 'app_projet_1')]
    public function projet1(): Response
    {
        return $this->render('her/projets/projet1.html.twig', [
            'projet' => 'Projet 1',
        ]);
    }

    #[Route('/projets/projet-2', name: 'app_projet_2')]
    public function projet2(): Response
    {
        return $this->render('her/projets/projet2.html.twig', [
            'projet' => 'Projet 2',
        ]);
    }

    #[Route('/cv', name: 'app_cv')]
    public function cv(): Response
    {
        return $this->render('her/cv.html.twig', [
            'controller_name' => 'CV',
        ]);
    }

    #[Route('/inscription-cv', name: 'app_inscription_cv')]
    public function inscriptionCv(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'required' => true,
                'attr' => ['placeholder' => 'Votre nom complet']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'attr' => ['placeholder' => 'votre.email@example.com']
            ])
            ->add('format', ChoiceType::class, [
                'label' => 'Format souhaité',
                'choices' => [
                    'PDF (HTML)' => 'pdf',
                    'DOCX (HTML)' => 'docx',
                    'Les deux' => 'both'
                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Télécharger le CV',
                'attr' => ['class' => 'btn btn-primary btn-lg']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Générer le document selon le format choisi
            if ($data['format'] === 'pdf') {
                return $this->generatePdf($data);
            } elseif ($data['format'] === 'docx') {
                return $this->generateDocx($data);
            } else {
                // Générer les deux formats
                $pdfResponse = $this->generatePdf($data);
                $docxResponse = $this->generateDocx($data);
                
                // Pour l'instant, retournons le PDF. On pourrait améliorer cela plus tard
                return $pdfResponse;
            }
        }

        return $this->render('her/inscription_cv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function generatePdf($data): Response
    {
        // Créer le HTML du CV
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>CV de Hala Azzi</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
                h1 { color: #ff69b4; text-align: center; margin-bottom: 30px; }
                h2 { color: #333; border-bottom: 2px solid #ff69b4; padding-bottom: 8px; margin-top: 25px; }
                .info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
                ul { margin: 10px 0; padding-left: 20px; }
                li { margin: 8px 0; }
                strong { color: #ff69b4; }
            </style>
        </head>
        <body>
            <h1>CV de Hala Azzi</h1>

            <div class='info'>
                <strong>Généré pour :</strong> " . htmlspecialchars($data['nom']) . "<br>
                <strong>Email :</strong> " . htmlspecialchars($data['email']) . "<br>
                <strong>Date de génération :</strong> " . date('d/m/Y H:i') . "
            </div>

            <h2>Informations Personnelles</h2>
            <p><strong>Nom complet :</strong> Hala Azzi</p>
            <p><strong>Titre professionnel :</strong> Ingénieure Réseaux et Télécommunications</p>
            <p><strong>Contact :</strong> hala.azzi@email.com | +33 6 XX XX XX XX | Ville, Pays</p>

            <h2>Formations</h2>
            <ul>
                <li><strong>Master Réseaux et Télécommunications</strong><br>
                    Université X, Ville<br>
                    2028 - 2030<br>
                    <em>Spécialisation en architectures réseau, sécurité informatique et technologies de communication.</em></li>
                <li><strong>BUT Réseaux et Télécommunications</strong><br>
                    Université Y, Ville<br>
                    2025 - 2028<br>
                    <em>Formation initiale en réseaux et télécommunications.</em></li>
            </ul>

            <h2>Expériences Professionnelles</h2>
            <ul>
                <li><strong>Stagiaire Ingénieure Réseaux</strong><br>
                    Entreprise A, Ville<br>
                    Mars 2025 - Août 2025<br>
                    <em>Stage en ingénierie réseaux avec mise en place d'infrastructures réseau.</em></li>
            </ul>

            <h2>Compétences Techniques</h2>
            <ul>
                <li><strong>Réseaux informatiques :</strong> Cisco, TCP/IP, VLAN, routage, switching</li>
                <li><strong>Programmation :</strong> Python, JavaScript, C</li>
                <li><strong>Systèmes d'exploitation :</strong> Linux, Windows Server</li>
                <li><strong>Outils :</strong> Wireshark, GNS3, VirtualBox</li>
            </ul>

            <h2>Langues</h2>
            <ul>
                <li><strong>Français :</strong> Langue maternelle</li>
                <li><strong>Anglais :</strong> Courant</li>
                <li><strong>Arabe :</strong> Courant</li>
            </ul>
        </body>
        </html>";

        // Configuration DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Arial');

        // Créer le PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner le PDF
        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="CV_Hala_Azzi_' . date('Y-m-d') . '.pdf"');

        return $response;
}
}
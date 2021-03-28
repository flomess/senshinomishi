<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class ProfilController extends AbstractController
{


    /**
     * @Route("/profil", name="profil")
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'user' => $user,
            'presEdit' => false,
            'dateInscription' => $user->getDateInscription()->format('d/m/Y')
        ]);
    }

    /**
     * @Route("/profil/edit", name="profil_presEdit")
     */
    public function presEdit(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'user' => $user,
            'presEdit' => 1,
            'dateInscription' => $user->getDateInscription()->format('d/m/Y')
        ]);
    }

    /**
     * @Route("/profil/presEditValid", name="profil_presEditValid")
     */
    public function presEditValid(EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $pres = $_POST['presentation'];
        $user->setPresentation($pres);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('profil');
    }

    /**
     * @Route("/profil/edit/infos", name="profil_EditInfos")
     */
    public function editInfos(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $mail = $_POST['mail'];
        $password = $_POST['password'];
        return $this->render('profil/edit_infos.html.twig',[
            'user' => $user,
            'mail' => $mail,
            'password' => $password,
            'error' => false
        ]);
    }

    private $passwordEncoder;
    /**
     * @Route("/profil/edit/infos_valid", name="profil_EditInfosValid")
     */
    public function editInfosValid(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {

        $user = $this->getUser();
        $mail = $_POST['mail'];
        $password = $_POST['password'];
        $password2 = $_POST['password2'];
        $password3 = $_POST['password3'];
        $this->passwordEncoder = $passwordEncoder;
        if($this->passwordEncoder->isPasswordValid($user, $password3)) {
            if ($password == $password2){
                $user->setMail($mail);
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $password
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('profil');
            } else {
                $erreur = 'Les mots de passe ne correspond pas';
            }
        } else {
            $erreur = 'Mot de passe incorrect';
        }
        return $this->render('profil/edit_infos.html.twig',[
            'user' => $user,
            'mail' => $mail,
            'password' => $password,
            'error' => $erreur
        ]);
    }
}

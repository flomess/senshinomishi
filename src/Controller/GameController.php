<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Round;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/game/new-game", name="new_game")
     */
    public function newGame(
        UserRepository $userRepository
    ): Response {
        $users = $userRepository->findAll();

        return $this->render('game/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/game/create-game", name="create_game")
     */
    public function createGame(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        CardRepository $cardRepository
    ): Response {
        $user1 = $this->getUser();
        $user2 = $userRepository->findOneByPseudo($request->request->get('user2'));

        if (($user1 !== $user2) && isset($user2)) {
            $game = new Game();
            $game->setUser1($user1);
            $game->setUser2($user2);
            $game->setDateCrated(new \DateTime('now'));
            $game->setStatut(true);

            $entityManager->persist($game);

            $round = new Round();
            $round->setGame($game);
            $round->setDateCreated(new \DateTime('now'));
            $round->setRoundNumber(1);

            $cards = $cardRepository->findAll();
            $tCards = [];
            foreach ($cards as $card) {
                $tCards[$card->getId()] = $card;
            }
            shuffle($tCards);
            $carte = array_pop($tCards);
            $round->setRemovedCard($carte->getId());

            $tMainJ1 = [];
            $tMainJ2 = [];
            for ($i = 0; $i < 6; $i++) {
                //on distribue 6 cartes aux deux joueurs
                $carte = array_pop($tCards);
                $tMainJ1[] = $carte->getId();
                $carte = array_pop($tCards);
                $tMainJ2[] = $carte->getId();
            }
            $round->setUser1Cards($tMainJ1);
            $round->setUser2Cards($tMainJ2);

            $tStack = [];

            foreach ($tCards as $card) {
                $carte = array_pop($tCards);
                $tStack[] = $carte->getId();
            }
            $round->setStack($tStack);
            $round->setUser1Action([
                'SECRET' => false,
                'DEPOT' => false,
                'OFFRE' => false,
                'ECHANGE' => false
            ]);

            $round->setUser2Action([
                'SECRET' => false,
                'DEPOT' => false,
                'OFFRE' => false,
                'ECHANGE' => false
            ]);

            $round->setBoard([
                'EMPL1' => ['N'],
                'EMPL2' => ['N'],
                'EMPL3' => ['N'],
                'EMPL4' => ['N'],
                'EMPL5' => ['N'],
                'EMPL6' => ['N'],
                'EMPL7' => ['N']
            ]);
            $entityManager->persist($round);
            $entityManager->flush();

            return $this->redirectToRoute('show_game', [
                'game' => $game->getId()
            ]);
        } else {
            return $this->redirectToRoute('new_game');
        }
    }

    /**
     * @Route("/game/show-game/{game}", name="show_game")
     */
    public function showGame(
        Game $game
    ): Response {

        return $this->render('game/show_game.html.twig', [
            'game' => $game
        ]);
    }

    /**
     * @Route("/game/get-tout-game/{game}", name="get_tour")
     */
    public function getTour(
        Game $game
    ): Response {
        if ($this->getUser()->getId() === $game->getUser1()->getId() && $game->getQuiJoue() === 1) {
            return $this->json(true);
        }

        if ($this->getUser()->getId() === $game->getUser2()->getId() && $game->getQuiJoue() === 2) {
            return $this->json(true);
        }

        return $this->json( false);
    }

    /**
     * @param Game $game
     * @route("/game/refresh/{game}", name="refresh_plateau_game")
     */
    public function refreshPlateauGame(CardRepository $cardRepository, Game $game)
    {
        $cards = $cardRepository->findAll();
        $tCards = [];
        foreach ($cards as $card) {
            $tCards[$card->getId()] = $card;
        }

        if ($this->getUser()->getId() === $game->getUser1()->getId()) {
            $moi['handCards'] = $game->getRounds()[0]->getUser1Cards();
            $moi['actions'] = $game->getRounds()[0]->getUser1Action();
            $moi['board'] = $game->getRounds()[0]->getUser1Board();
            $adversaire['handCards'] = $game->getRounds()[0]->getUser2Cards();
            $adversaire['actions'] = $game->getRounds()[0]->getUser2Action();
            $adversaire['board'] = $game->getRounds()[0]->getUser2Board();
        } elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
            $moi['handCards'] = $game->getRounds()[0]->getUser2Cards();
            $moi['actions'] = $game->getRounds()[0]->getUser2Action();
            $moi['board'] = $game->getRounds()[0]->getUser2Board();
            $adversaire['handCards'] = $game->getRounds()[0]->getUser1Cards();
            $adversaire['actions'] = $game->getRounds()[0]->getUser1Action();
            $adversaire['board'] = $game->getRounds()[0]->getUser1Board();
        } else {
            return $this->redirectToRoute('accueil');
        }

        return $this->render('game/plateau_game.html.twig', [
            'game' => $game,
            'set' => $game->getRounds()[0],
            'cards' => $tCards,
            'moi' => $moi,
            'adversaire' => $adversaire
        ]);
    }

    /**
     * @Route("/game/action-game/{game}", name="action_game")
     */
    public function actionGame(
        EntityManagerInterface $entityManager,
        Request $request, Game $game){


        $action = $request->request->get('action');
        $user = $this->getUser();
        $round = $game->getRounds()[0]; //a gérer selon le round en cours

        if ($game->getUser1()->getId() === $user->getId())
        {
            $joueur = 1;
        } elseif ($game->getUser2()->getId() === $user->getId()) {
            $joueur = 2;
        } else {
            return $this->redirectToRoute('accueil');
        }

        switch ($action) {
            case 'secret':
                $carte = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action(); //un tableau...
                    $actions['SECRET'] = [$carte]; //je sauvegarde la carte cachée dans mes actions
                    $round->setUser1Action($actions); //je mets à jour le tableau
                    $main = $round->getUser1Cards();
                    $indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    $main[] = $cartePiochee;
                    $round->setUser1Cards($main);
                    $round->setStack($stack);
                    $game->setQuiJoue(2);
                } else {
                    $actions = $round->getUser2Action(); //un tableau...
                    $actions['SECRET'] = [$carte]; //je sauvegarde la carte cachée dans mes actions
                    $round->setUser2Action($actions); //je mets à jour le tableau
                    $main = $round->getUser2Cards();
                    $indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    $main[] = $cartePiochee;
                    $round->setUser2Cards($main);
                    $round->setStack($stack);
                    $game->setQuiJoue(1);
                }
                break;
            case 'depot':
                $cartes = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action(); //un tableau...
                    $actions['DEPOT'] = true;
                    $round->setUser1Action($actions); //je mets à jour le tableau
                    $main = $round->getUser1Cards();
                    $indexCarte1 = array_search($cartes['card1'], $main);
                    $indexCarte2 = array_search($cartes['card2'], $main);
                    unset($main[$indexCarte1]); //je supprime la carte de ma main
                    unset($main[$indexCarte2]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    $main[] = $cartePiochee;
                    $round->setUser1Cards($main);
                    $round->setStack($stack);
                    $game->setQuiJoue(2);
                } else {
                    $actions = $round->getUser2Action(); //un tableau...
                    $actions['DEPOT'] = true;
                    $round->setUser2Action($actions); //je mets à jour le tableau
                    $main = $round->getUser2Cards();
                    $indexCarte1 = array_search($cartes['card1'], $main);
                    $indexCarte2 = array_search($cartes['card2'], $main);
                    unset($main[$indexCarte1]); //je supprime la carte de ma main
                    unset($main[$indexCarte2]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    $main[] = $cartePiochee;
                    $round->setUser2Cards($main);
                    $round->setStack($stack);
                    $game->setQuiJoue(1);
                }
                break;
        }

        $entityManager->flush();

        return $this->json(true);
    }
}
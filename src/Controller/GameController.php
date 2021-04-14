<?php

namespace App\Controller;

use App\Entity\Card;
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
            for ($i = 0; $i < 7; $i++) {
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
            $userboard = [];
            $round->setUser1Board($userboard);
            $round->setUser2Board($userboard);

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
            $moi['pseudo'] = $this->getUser()->getUsername();
            if ($game->getQuiJoue() === 1) {
                $tour = 'moi';
            } else {
                $tour = 'adversaire';
            }
            $adversaire['pseudo'] = $game->getUser2()->getUsername();
            $adversaire['handCards'] = $game->getRounds()[0]->getUser2Cards();
            $adversaire['actions'] = $game->getRounds()[0]->getUser2Action();
            $adversaire['board'] = $game->getRounds()[0]->getUser2Board();
        } elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
            $moi['handCards'] = $game->getRounds()[0]->getUser2Cards();
            $moi['actions'] = $game->getRounds()[0]->getUser2Action();
            $moi['board'] = $game->getRounds()[0]->getUser2Board();
            $moi['pseudo'] = $this->getUser()->getUsername();
            if ($game->getQuiJoue() === 2) {
                $tour = 'moi';
            } else {
                $tour = 'adversaire';
            }
            $adversaire['pseudo'] = $game->getUser1()->getUsername();
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
            'adversaire' => $adversaire,
            'tour' => $tour
        ]);
    }

    /**
     * @Route("/game/action-game/{game}", name="action_game")
     */
    public function actionGame(
        EntityManagerInterface $entityManager,
        Request $request, Game $game, CardRepository $cardRepository ){


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
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
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
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
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
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
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
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
                    $round->setUser2Cards($main);
                    $round->setStack($stack);
                    $game->setQuiJoue(1);
                }
                break;
            case 'offre':
                $cartes = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action(); //un tableau...
                    $actions['OFFRE'][] = $cartes['card1'];
                    $actions['OFFRE'][] = $cartes['card2'];
                    $actions['OFFRE'][] = $cartes['card3'];
                    $round->setUser1Action($actions); //je mets à jour le tableau
                    $main = $round->getUser1Cards();
                    $indexCarte1 = array_search($cartes['card1'], $main);
                    $indexCarte2 = array_search($cartes['card2'], $main);
                    $indexCarte3 = array_search($cartes['card3'], $main);
                    unset($main[$indexCarte1]); //je supprime la carte de ma main
                    unset($main[$indexCarte2]); //je supprime la carte de ma main
                    unset($main[$indexCarte3]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
                    $round->setUser1Cards($main);
                    $round->setStack($stack);
                    $game->setQuiJoue(2);
                } else {
                    $actions = $round->getUser2Action(); //un tableau...
                    $actions['OFFRE'][] = $cartes['card1'];
                    $actions['OFFRE'][] = $cartes['card2'];
                    $actions['OFFRE'][] = $cartes['card3'];
                    $round->setUser2Action($actions); //je mets à jour le tableau
                    $main = $round->getUser2Cards();
                    $indexCarte1 = array_search($cartes['card1'], $main);
                    $indexCarte2 = array_search($cartes['card2'], $main);
                    $indexCarte3 = array_search($cartes['card3'], $main);
                    unset($main[$indexCarte1]); //je supprime la carte de ma main
                    unset($main[$indexCarte2]); //je supprime la carte de ma main
                    unset($main[$indexCarte3]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
                    $round->setUser2Cards($main);
                    $round->setStack($stack);
                    $game->setQuiJoue(1);
                }
                break;
            case 'offre_valid':
                $carte = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser2Action(); //un tableau...
                    $board1 = $round->getUser1Board();
                    $board2 = $round->getUser2Board();
                    $carteChoisie = array_search($carte, $actions['OFFRE']);
                    array_splice($actions['OFFRE'], $carteChoisie, 1);
                    $board1[] = $carte;
                    $board2[] = $actions['OFFRE'][0];
                    $board2[] = $actions['OFFRE'][1];
                    $actions['OFFRE'] = 'done';
                    $round->setUser2Action($actions);
                    $round->setUser1Board($board1);
                    $round->setUser2Board($board2);
                    $round->setUser2Action($actions); //je mets à jour le tableau
                    $game->setQuiJoue(1);
                } else {
                    $actions = $round->getUser1Action(); //un tableau...
                    $board1 = $round->getUser1Board();
                    $board2 = $round->getUser2Board();
                    $carteChoisie = array_search($carte, $actions['OFFRE']);
                    array_splice($actions['OFFRE'], $carteChoisie, 1);
                    $board2[] = $carte;
                    $board1[] = $actions['OFFRE'][0];
                    $board1[] = $actions['OFFRE'][1];
                    $actions['OFFRE'] = 'done';
                    $round->setUser1Action($actions);
                    $round->setUser1Board($board1);
                    $round->setUser2Board($board2);
                    $round->setUser1Action($actions); //je mets à jour le tableau
                    $game->setQuiJoue(2);
                }
                break;
            case 'echange':
                $cartes = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action(); //un tableau...
                    $actions['ECHANGE'][] = $cartes['card1'];
                    $actions['ECHANGE'][] = $cartes['card2'];
                    $actions['ECHANGE'][] = $cartes['card3'];
                    $actions['ECHANGE'][] = $cartes['card4'];
                    $round->setUser1Action($actions); //je mets à jour le tableau
                    $main = $round->getUser1Cards();
                    $indexCarte1 = array_search($cartes['card1'], $main);
                    $indexCarte2 = array_search($cartes['card2'], $main);
                    $indexCarte3 = array_search($cartes['card3'], $main);
                    $indexCarte4 = array_search($cartes['card4'], $main);
                    unset($main[$indexCarte1]); //je supprime la carte de ma main
                    unset($main[$indexCarte2]); //je supprime la carte de ma main
                    unset($main[$indexCarte3]); //je supprime la carte de ma main
                    unset($main[$indexCarte4]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
                    $round->setUser1Cards($main);
                    $round->setStack($stack);
                } else {
                    $actions = $round->getUser2Action(); //un tableau...
                    $actions['ECHANGE'][] = $cartes['card1'];
                    $actions['ECHANGE'][] = $cartes['card2'];
                    $actions['ECHANGE'][] = $cartes['card3'];
                    $actions['ECHANGE'][] = $cartes['card4'];
                    $round->setUser2Action($actions); //je mets à jour le tableau
                    $main = $round->getUser2Cards();
                    $indexCarte1 = array_search($cartes['card1'], $main);
                    $indexCarte2 = array_search($cartes['card2'], $main);
                    $indexCarte3 = array_search($cartes['card3'], $main);
                    $indexCarte4 = array_search($cartes['card4'], $main);
                    unset($main[$indexCarte1]); //je supprime la carte de ma main
                    unset($main[$indexCarte2]); //je supprime la carte de ma main
                    unset($main[$indexCarte3]); //je supprime la carte de ma main
                    unset($main[$indexCarte4]); //je supprime la carte de ma main
                    $stack = $round->getStack();
                    $cartePiochee = array_shift($stack);
                    if ($cartePiochee == ''){
                        $main = [];
                    } else {
                        $main[] = $cartePiochee;
                    }
                    $round->setUser2Cards($main);
                    $round->setStack($stack);
                }
                break;
            case 'echange_group':
                $carte = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action(); //un tableau...
                    $echange = $actions['ECHANGE'];
                    $carteChoisie1 = array_search($carte['card1'], $echange);
                    array_splice($echange, $carteChoisie1, 1);
                    $carteChoisie2 = array_search($carte['card2'], $echange);
                    array_splice($echange, $carteChoisie2, 1);
                    $actions['ECHANGE'] = [];
                    $actions['ECHANGE']['group1'][] = $carte['card1'];
                    $actions['ECHANGE']['group1'][] = $carte['card2'];
                    $actions['ECHANGE']['group2'] = $echange;
                    $round->setUser1Action($actions);
                    $game->setQuiJoue(2);

                } else {
                    $actions = $round->getUser2Action(); //un tableau...
                    $echange = $actions['ECHANGE'];
                    $carteChoisie1 = array_search($carte['card1'], $echange);
                    array_splice($echange, $carteChoisie1, 1);
                    $carteChoisie2 = array_search($carte['card2'], $echange);
                    array_splice($echange, $carteChoisie2, 1);
                    $actions['ECHANGE'] = [];
                    $actions['ECHANGE']['group1'][] = $carte['card1'];
                    $actions['ECHANGE']['group1'][] = $carte['card2'];
                    $actions['ECHANGE']['group2'] = $echange;
                    $round->setUser2Action($actions);
                    $game->setQuiJoue(1);
                }
                break;
            case 'echange_valid':
                $groupChoisi = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser2Action(); //un tableau...
                    $board1 = $round->getUser1Board();
                    $board2 = $round->getUser2Board();
                    if($groupChoisi == 'group1'){
                        $board1[] = $actions['ECHANGE']['group1'][0];
                        $board1[] = $actions['ECHANGE']['group1'][1];
                        $board2[] = $actions['ECHANGE']['group2'][0];
                        $board2[] = $actions['ECHANGE']['group2'][1];
                    } else {
                        $board2[] = $actions['ECHANGE']['group1'][0];
                        $board2[] = $actions['ECHANGE']['group1'][1];
                        $board1[] = $actions['ECHANGE']['group2'][0];
                        $board1[] = $actions['ECHANGE']['group2'][1];
                    }
                    $actions['ECHANGE'] = 'done';
                    $round->setUser2Action($actions);
                    $round->setUser1Board($board1);
                    $round->setUser2Board($board2);
                    $game->setQuiJoue(1);
                } else {
                    $actions = $round->getUser1Action(); //un tableau...
                    $board1 = $round->getUser1Board();
                    $board2 = $round->getUser2Board();
                    if ($groupChoisi == 'group1') {
                        $board2[] = $actions['ECHANGE']['group1'][0];
                        $board2[] = $actions['ECHANGE']['group1'][1];
                        $board1[] = $actions['ECHANGE']['group2'][0];
                        $board1[] = $actions['ECHANGE']['group2'][1];
                    } else {
                        $board1[] = $actions['ECHANGE']['group1'][0];
                        $board1[] = $actions['ECHANGE']['group1'][1];
                        $board2[] = $actions['ECHANGE']['group2'][0];
                        $board2[] = $actions['ECHANGE']['group2'][1];
                    }
                    $actions['ECHANGE'] = 'done';
                    $round->setUser1Action($actions);
                    $round->setUser1Board($board1);
                    $round->setUser2Board($board2);
                    $game->setQuiJoue(2);
                }
                break;
        }

        $entityManager->flush();

        return $this->json(true);
    }
}
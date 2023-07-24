<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(RequestStack $rs ,ProductRepository $repo): Response
    {
        $session = $rs->getSession();
        $cart = $session->get('cart', []);
        $qt = 0;

        //je vais créer un nouveau tableau qui contiendra des objets Product et les quantités de chaque objets
        $cartWithData = [];

        //Pour chaque $id qui se trouve dans mon tableau $cart, j'ajoute une case au tableau $cartWithData, qui est multidimensionnel
        //chaque case est elle-même un tableau associatif contenant 2 cases : une case 'product' et une case 'quantity'
        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $repo->find($id),
                'quantity' => $quantity
            ];
            $qt += $quantity;
        }
        $session->set('qt', $qt);

        $total = 0;

        foreach ($cartWithData as $item) {
            $totalItem = $item['product']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
        
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add($id, RequestStack $rs): Response
    {
   
        // Nous allons récupérer oou créer une session grâce à la classe RequestStack
        $session = $rs->getSession();
        //je récupère l'attribut de session 'cart' s'il existe ou un tableau vide
        $cart = $session->get('cart', []);

        //si je produit existe déjà, j'incrémente sa quantité
        if (!empty($cart[$id])) {
            $cart[$id]++;
        }
            else {
                //dans mon tableau $cart à la case $id je donne la valeur 1
                $cart[$id] = 1;
            }
        
        // je sauvegarde l'état de mon panier en session à l'attribut de session 'cart'
        $session->set('cart', $cart);

        // dd($session->get('cart'));
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove($id, RequestStack $rs)
    {
        $session = $rs->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
        
    }

    #[Route('/cart/empty', name: 'cart_empty')]
    public function empty(RequestStack $rs)
    {
        $session = $rs->getSession();
        $session->set('cart', []);
        return $this->redirectToRoute('app_cart');
    }
    

    #[Route('/cart/quantity/moins/{id}', name: 'cart_moins')]
    public function delQuantity($id, RequestStack $rs)
    {
        $session = $rs->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])){
            if($cart[$id] > 1){
                $cart[$id]--;
            }
            else {
                unset($cart[$id]);
            }
            
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
        
    }
}

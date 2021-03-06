<?php

namespace App\Dashboard\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/", name="AdminDashboard")
     */
    public function index(): Response
    {

        return $this->render('@dashboard/admin/main.html.twig', []);
    }

}

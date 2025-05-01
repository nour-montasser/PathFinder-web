<?php

namespace App\Controller;

use App\Entity\Cv;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CVBackofficeController extends AbstractController
{
    #[Route('/backoffice/cv', name: 'backoffice_cv_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // Build a query that retrieves CVs and join fetch its associations.
        // Using distinct() avoids duplicate CV rows if there are multiple associations.
        $qb = $em->createQueryBuilder();
        $qb->select('c, l, e, cert, u')
            ->from(Cv::class, 'c')
            ->leftJoin('c.languages', 'l')
            ->leftJoin('c.experiences', 'e')
            ->leftJoin('c.certificates', 'cert')
            ->leftJoin('c.user', 'u')
            ->distinct();
        $cvs = $qb->getQuery()->getResult();

        return $this->render('cv_backoffice/index.html.twig', [
            'cvs' => $cvs,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Cv;
use App\Entity\Languages as EntityLanguages; // Alias for your custom Languages entity
use App\Entity\Experience;
use App\Entity\Certificates;
use App\Entity\App_user;
use App\Form\CvType;    
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Intl\Languages as IntlLanguages; 
use Symfony\Component\HttpFoundation\Session\SessionInterface;
 // Alias for Symfony's Languages

use App\Service\LightcastTokenService;

final class CvController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    #[Route('/cv', name: 'app_cv')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $cv   = new Cv();
        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cv);
            $em->flush();
            $this->addFlash('success', 'CV created successfully!');
            return $this->redirectToRoute('app_home');
        }

     
        try {
            $response = $this->httpClient->request('GET', 'https://restcountries.com/v2/all?fields=languages', [
                'http_version' => '1.1',
                'timeout'      => 10,
            ]);
        
            $countries = $response->toArray(false);
            $set = [];
            foreach ($countries as $c) {
                if (!empty($c['languages']) && is_array($c['languages'])) {
                    foreach ($c['languages'] as $langObj) {
                        $set[$langObj['name']] = true;
                    }
                }
            }
        
            $languages = array_keys($set);
            sort($languages, SORT_FLAG_CASE | SORT_STRING);
        } catch (\Exception $e) {
            $languages = [];
        }

        if (empty($languages)) {
            // Fallback to Symfony Intl
            // Languages::getNames() returns ['en' => 'English', 'fr' => 'French', …]
            $languages = array_values(IntlLanguages::getNames());
        }

        return $this->render('cv/index.html.twig', [
            'cvForm'    => $form->createView(),
            'languages' => $languages,
        ]);
    }
    #[Route('/cv/skills/search', name: 'app_cv_skills_search', methods: ['GET'])]
    public function searchSkills(Request $request, LightcastTokenService $tokenService): Response
    {
        $query = $request->query->get('q');
        if (!$query) {
            return $this->json([]);
        }
    
        try {
            $accessToken = $tokenService->getAccessToken();
    
            $apiUrl = 'https://emsiservices.com/skills/versions/latest/skills?q=' . urlencode($query);
            $skillResponse = $this->httpClient->request('GET', $apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);
    
            $skills = $skillResponse->toArray()['data'] ?? [];
            return $this->json($skills);
    
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Error fetching skills',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    #[Route('/cv/create', name: 'cv_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        var_dump($request->request->all());
        // Create a new CV object
        $cv = new Cv();
    
        // Create the form for CV
        $form = $this->createForm(CvType::class, $cv);
    
        // Handle form submission
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Copy the user_title to the title before persisting
            // Get the repository and count titles for CVs with user null
            $originalTitle = $cv->getUserTitle();
        $repository = $em->getRepository(Cv::class);
        $qb = $repository->createQueryBuilder('c');
        $qb->select('COUNT(c)')
           ->where('c.user IS NULL')
           ->andWhere('c.title LIKE :pattern')
           ->setParameter('pattern', $originalTitle . '%');
        $existingCount = (int)$qb->getQuery()->getSingleScalarResult();

        // If there are existing CVs (with user null) that match, append a numerical suffix.
        if ($existingCount > 0) {
            // For instance, if one exists then the new title will become "Original Title (2)"
            $cv->setTitle($originalTitle . ' (' . ($existingCount + 1) . ')');
        } else {
            $cv->setTitle($originalTitle);
        }
            $cv->setLastViewed(new \DateTime());
            $cv->setFavorite(false);
            $cv->setDateCreation(new \DateTime());
    
              // Save the skills field as a comma-separated string
        $skills = $form->get('skills')->getData(); // This gets the skills input as a string
       
        $cv->setSkills($skills ?: ''); // Use empty string if no skills provided


          
        // Loop through selected languages and add them to the CV
        $languagesData = $request->get('languages');
   
        if ($languagesData) {
            foreach ($languagesData as $languageData) {
                $language = new EntityLanguages();
                $language->setCv($cv);
                $language->setLanguagename($languageData['name']);
                $language->setLevel($languageData['level']);
                $em->persist($language);
            }
        }
    
            // Loop through selected experiences and add them to the CV
            $experiencesData = $request->get('experiences');
         
        foreach ($experiencesData as $experienceData) {
            $experience = new Experience();
            $experience->setCv($cv);
            $experience->setType($experienceData['type']);
            $experience->setPosition($experienceData['position']);
            $experience->setLocationname($experienceData['location']);
            // Use the keys 'startDate' and 'endDate' (matching your front-end)
            $experience->setStartdate(new \DateTime($experienceData['startDate']));
            $experience->setEnddate(new \DateTime($experienceData['endDate']));
            $experience->setDescription($experienceData['description']);
            $em->persist($experience);
        }
    
            // Loop through selected certificates and add them to the CV
            $certificatesData = $request->get('certificates');
            if($certificatesData){
            foreach ($certificatesData as $certificateData) {
                $certificate = new Certificates();
                $certificate->setCv($cv);
                // Use 'name' as the certificate title.
                $certificate->setTitle($certificateData['name']);
                $certificate->setDescription($certificateData['description']);
                $certificate->setMedia($certificateData['media']);
                // The key 'date' maps to the issue date.
                $certificate->setIssueDate(new \DateTime($certificateData['date']));
                // Use 'association' for the issuing organization.
                $certificate->setIssuedBy($certificateData['association']);
                $em->persist($certificate);
            }
        }
    
            // Persist the CV object
            $em->persist($cv);
            $em->flush();
    
            // Redirect to the home page with a success message
            $this->addFlash('success', 'CV created successfully!');
            return $this->redirectToRoute('cv_show');
          
        }
    
        return $this->render('cv/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/cv/show', name: 'cv_show')]
public function show(EntityManagerInterface $em): Response
{
    
    // Retrieve all CV entities (you may want to add ordering)
    $cvs = $em->getRepository(Cv::class)->findAll();

    return $this->render('cv/cvshow.html.twig', [
        'cvs' => $cvs,
    ]);
}
#[Route('/cv/{id}/edit', name: 'cv_edit')]
public function edit(Request $request, Cv $cv, EntityManagerInterface $em, HttpClientInterface $httpClient): Response
{
    // Reset specific fields so the form appears blank (like in create mode)
   // $cv->setUserTitle('');
    //$cv->setIntroduction('');
    //$cv->setSkills('');

    // Create the form using the updated entity.
    $form = $this->createForm(CvType::class, $cv);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $cv->setLastViewed(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'CV updated successfully!');
        return $this->redirectToRoute('cv_show');
    }

    // Retrieve available languages from the external API.
    try {
        $response = $httpClient->request('GET', 'https://restcountries.com/v2/all?fields=languages', [
            'http_version' => '1.1',
            'timeout'      => 10,
        ]);
        $countries = $response->toArray(false);
        $set = [];
        foreach ($countries as $c) {
            if (!empty($c['languages']) && is_array($c['languages'])) {
                foreach ($c['languages'] as $langObj) {
                    $set[$langObj['name']] = true;
                }
            }
        }
        $availableLanguages = array_keys($set);
        sort($availableLanguages, SORT_FLAG_CASE | SORT_STRING);
    } catch (\Exception $e) {
        $availableLanguages = [];
    }

    if (empty($availableLanguages)) {
        // Fallback to Symfony Intl Languages.
        $availableLanguages = array_values(IntlLanguages::getNames());
    }

    return $this->render('cv/edit.html.twig', [
        'cvForm'            => $form->createView(),
        'cv'                => $cv,   // Pass the CV so you can access cv.id in Twig.
        'languages'=> $availableLanguages,
        'cvLanguages'       => $cv->getLanguages(),
        'experiences'       => $cv->getExperiences(),
        'certificates'      => $cv->getCertificates(),
    ]);
}

#[Route('/cv/{id}/delete', name: 'cv_delete', methods: ['POST'])]
public function delete(Request $request, Cv $cv, EntityManagerInterface $em): Response
{
    // Check that the CSRF token is valid.
    if ($this->isCsrfTokenValid('delete' . $cv->getId(), $request->request->get('_token'))) {
        $em->remove($cv);
        $em->flush();

        $this->addFlash('success', 'CV deleted successfully!');
    } else {
        $this->addFlash('error', 'Invalid delete token.');
    }

    return $this->redirectToRoute('cv_show');
}
#[Route('/cv/{id}/update', name: 'cv_update', methods: ['POST'])]
public function update(Request $request, Cv $cv, EntityManagerInterface $em, HttpClientInterface $httpClient): Response
{
    // Create the form using the existing CV entity.
    $form = $this->createForm(CvType::class, $cv);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the user-submitted title
        $originalTitle = $cv->getUserTitle();
        
        // Check for duplicate titles among CVs where user IS NULL (excluding current)
        $repository = $em->getRepository(Cv::class);
        $qb = $repository->createQueryBuilder('c');
        $qb->select('COUNT(c)')
           ->where('c.user IS NULL')
           ->andWhere('c.id_cv != :currentId')
           ->andWhere('c.title LIKE :pattern')
           ->setParameter('currentId', $cv->getId())
           ->setParameter('pattern', $originalTitle . '%');
        $existingCount = (int)$qb->getQuery()->getSingleScalarResult();

        // Append numeric suffix if duplicate exists.
        if ($existingCount > 0) {
            $cv->setTitle($originalTitle . ' (' . ($existingCount + 1) . ')');
        } else {
            $cv->setTitle($originalTitle);
        }

        $cv->setLastViewed(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'CV updated successfully!');
        return $this->redirectToRoute('cv_show');
    }

    // If form is not valid, retrieve available languages from the external API.
    try {
        $response = $httpClient->request('GET', 'https://restcountries.com/v2/all?fields=languages', [
            'http_version' => '1.1',
            'timeout'      => 10,
        ]);
        $countries = $response->toArray(false);
        $set = [];
        foreach ($countries as $c) {
            if (!empty($c['languages']) && is_array($c['languages'])) {
                foreach ($c['languages'] as $langObj) {
                    $set[$langObj['name']] = true;
                }
            }
        }
        $availableLanguages = array_keys($set);
        sort($availableLanguages, SORT_FLAG_CASE | SORT_STRING);
    } catch (\Exception $e) {
        $availableLanguages = [];
    }
    if (empty($availableLanguages)) {
        $availableLanguages = array_values(IntlLanguages::getNames());
    }

    // Re-render the edit form (with any errors) along with additional data.
    return $this->render('cv/edit.html.twig', [
        'cvForm'            => $form->createView(),
        'cv'                => $cv,
        'languages'         => $availableLanguages,
        'cvLanguages'       => $cv->getLanguages(),
        'experiences'       => $cv->getExperiences(),
        'certificates'      => $cv->getCertificates(),
    ]);
}




    

}
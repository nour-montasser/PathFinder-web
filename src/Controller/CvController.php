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
        // Create a new CV object
        $cv = new Cv();
    
        // Create the form for CV
        $form = $this->createForm(CvType::class, $cv);
    
        // Handle form submission
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Copy the user_title to the title before persisting
            $cv->setTitle($cv->getUserTitle());
            $cv->setLastViewed(new \DateTime());
            $cv->setFavorite(false);
            $cv->setDateCreation(new \DateTime());
    
              // Save the skills field as a comma-separated string
        $skills = $form->get('skills')->getData(); // This gets the skills input as a string
       
        $cv->setSkills($skills ?: ''); // Use empty string if no skills provided


            // Loop through selected languages and add them to the CV
            $languagesData = $form->get('languages')->getData();
            foreach ($languagesData as $languageData) {
                $language = new EntityLanguages();
                $language->setCv($cv);
                $language->setLanguage_name($languageData['name']);
                $language->setLevel($languageData['level']);
                $em->persist($language);
            }
    
            // Loop through selected experiences and add them to the CV
            $experiencesData = $form->get('experiences')->getData();
            foreach ($experiencesData as $experienceData) {
                $experience = new Experience();
                $experience->setCv($cv);
                $experience->setType($experienceData['type']);
                $experience->setPosition($experienceData['position']);
                $experience->setLocation_name($experienceData['location']);
                $experience->setStart_date(new \DateTime($experienceData['start_date']));
                $experience->setEnd_date(new \DateTime($experienceData['end_date']));
                $experience->setDescription($experienceData['description']);
                $em->persist($experience);
            }
    
            // Loop through selected certificates and add them to the CV
            $certificatesData = $form->get('certificates')->getData();
            foreach ($certificatesData as $certificateData) {
                $certificate = new Certificates();
                $certificate->setCv($cv);
                $certificate->setTitle($certificateData['title']);
                $certificate->setDescription($certificateData['description']);
                $certificate->setMedia($certificateData['media']);
                $certificate->setIssue_date(new \DateTime($certificateData['issue_date']));
                $certificate->setIssued_by($certificateData['issued_by']);
                $em->persist($certificate);
            }
    
            // Persist the CV object
            $em->persist($cv);
            $em->flush();
    
            // Redirect to the home page with a success message
            $this->addFlash('success', 'CV created successfully!');
            return $this->redirectToRoute('app_cv');
        }
    
        return $this->render('cv/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
     // New route to add an example CV to the database
     #[Route('/cv/add-example', name: 'cv_add_example')]
     public function addExample(EntityManagerInterface $em): Response
     {
        // First, check if the user with id_user = 0 exists, or create a new one if not
  
         // Create an example CV
         $cv = new Cv();
         $cv->setUserTitle('Sample CV Title');
         $cv->setIntroduction('This is an introduction to the sample CV.');
         $cv->setSkills('HTML, CSS, JavaScript');
         $cv->setDateCreation(new \DateTime());
         $cv->setLastViewed(new \DateTime());
         $cv->setFavorite(true);
         $cv->setTitle($cv->getUserTitle()); 
        //ually setting an ID temporarily // Copy the user_title value to the title
         
         // Example of adding Languages
         $language1 = new EntityLanguages();
         $language1->setCv($cv);
         $language1->setLanguage_name('English');
         $language1->setLevel('Advanced');
         $em->persist($language1);
 
         $language2 = new EntityLanguages();
         $language2->setCv($cv);
         $language2->setLanguage_name('French');
         $language2->setLevel('Intermediate');
         $em->persist($language2);
 
         // Example of adding Experiences
         $experience = new Experience();
         $experience->setCv($cv);
         $experience->setType('Internship');
         $experience->setPosition('Front-end Developer');
         $experience->setLocation_name('New York');
         $experience->setStart_date(new \DateTime('2022-01-01'));
         $experience->setEnd_date(new \DateTime('2022-06-01'));
         $experience->setDescription('Developed and maintained websites using HTML, CSS, and JavaScript.');
         $em->persist($experience);
 
         // Example of adding Certificates
         $certificate = new Certificates();
         $certificate->setCv($cv);
         $certificate->setTitle('Web Development Certification');
         $certificate->setDescription('Certificate awarded for completing a web development course.');
         $certificate->setMedia('web_dev_cert.pdf');
         $certificate->setIssue_date(new \DateTime('2022-07-01'));
         $certificate->setIssued_by('Udemy');
         $em->persist($certificate);
 
         // Persist the CV object with its associated entities
         $em->persist($cv);
         $em->flush();
       
         // Provide feedback to the user
         $this->addFlash('success', 'Example CV has been added successfully.');
 
         return $this->redirectToRoute('app_cv'); // Redirect to a page that lists CVs or show confirmation
     }

}
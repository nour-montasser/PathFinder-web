<?php

namespace App\Controller;

use App\Entity\Cv;
use Knp\Snappy\Pdf;
use App\Form\CvType;
use App\Entity\App_user;
use App\Entity\Experience;
use App\Entity\Certificates;
use Symfony\Component\Form\FormError;
use App\Service\LightcastTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Intl\Languages as IntlLanguages;
// Alias for Symfony's Languages

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Languages as EntityLanguages; // Alias for your custom Languages entity


final class CvController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private Pdf $snappyPdf;
    

    public function __construct(HttpClientInterface $httpClient,Pdf $snappyPdf)
    {
        $this->httpClient = $httpClient;
        $this->snappyPdf = $snappyPdf;
    }
    #[Route('/cv', name: 'app_cv')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $cv = new Cv();
        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);
        // --- pull the raw skills string back out of the form ---
        $skillsString = $form->get('skills')->getData();    
        // if the user submitted something (even invalid), this will be their value:
        $cv->setSkills($skillsString ?: '');

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cv);
            $em->flush();
            $this->addFlash('success', 'CV created successfully!');
            return $this->redirectToRoute('app_home');
        }


        try {
            $response = $this->httpClient->request('GET', 'https://restcountries.com/v2/all?fields=languages', [
                'http_version' => '1.1',
                'timeout' => 10,
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
        $languageError = null;
        $experienceError = null;
        return $this->render('cv/index.html.twig', [
            'cvForm' => $form->createView(),
            'languages' => $languages,
            'languageError' => $languageError,
            'experienceError' => $experienceError,
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
        $cv = new Cv();

        // Create the form for CV
        $form = $this->createForm(CvType::class, $cv);

        // Handle form submission
        $languageError = null;
        $experienceError = null;
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $langs = $request->get('languages', []);
            $exps = $request->get('experiences', []);

            if (count($langs) < 1) {
                $languageError = 'At least one language is required.';
            }
            if (count($exps) < 1) {
                $experienceError = 'At least one experience is required.';
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                // $error->getOrigin()->getName() is the form field name (e.g. "_token" or "skills")
                // $error->getMessage() is the human-readable violation
                var_dump($error->getOrigin()->getName(), $error->getMessage());
            }
        }

        if (
            $form->isSubmitted() && $form->isValid() && !$languageError
            && !$experienceError
        ) {
            // Copy the user_title to the title before persisting
            // Get the repository and count titles for CVs with user null

            $originalTitle = $cv->getUserTitle();
            $repository = $em->getRepository(Cv::class);
            $qb = $repository->createQueryBuilder('c');
            $qb->select('COUNT(c)')
                ->where('c.user IS NULL')
                ->andWhere('c.title LIKE :pattern')
                ->setParameter('pattern', $originalTitle . '%');
            $existingCount = (int) $qb->getQuery()->getSingleScalarResult();

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

            $skills = $form->get('skills')->getData();  // "HTML,CSS,JavaScript"
            $cv->setSkills($skills);



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
            if ($certificatesData) {
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

        } else
            if ($form->isSubmitted()) {
                $this->addFlash('error', 'Please fix the errors below.');
            }
        // 2) fetch the language list (exactly like in index())
        try {
            $response = $this->httpClient->request('GET', 'https://restcountries.com/v2/all?fields=languages', [
                'http_version' => '1.1',
                'timeout' => 10,
            ]);
            $countries = $response->toArray(false);
            $set = [];
            foreach ($countries as $c) {
                foreach ($c['languages'] ?? [] as $langObj) {
                    $set[$langObj['name']] = true;
                }
            }
            $languages = array_keys($set);
            sort($languages, SORT_FLAG_CASE | SORT_STRING);
        } catch (\Exception $e) {
            $languages = array_values(\Symfony\Component\Intl\Languages::getNames());
        }

        // 4) render using index.html.twig with exactly the same vars
        return $this->render('cv/index.html.twig', [
            'cvForm' => $form->createView(),
            'languages' => $languages,
            'languageError' => $languageError,
            'experienceError' => $experienceError,
            'oldLanguages' => $langs,
            'oldExperiences' => $exps,
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
                'timeout' => 10,
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
            'cvForm' => $form->createView(),
            'cv' => $cv,   // Pass the CV so you can access cv.id in Twig.
            'languages' => $availableLanguages,
            'cvLanguages' => $cv->getLanguages(),
            'experiences' => $cv->getExperiences(),
            'certificates' => $cv->getCertificates(),
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
    public function update(
        Request $request,
        Cv $cv,
        EntityManagerInterface $em,
        HttpClientInterface $httpClient
    ): Response {
        // 1) build form & handle request
        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);
    
        // 2) Prepare inline validation flags
      
        $languageError     = null;
        $experienceError   = null;
        $titleError        = null;
        $introductionError = null;
        $skillsError       = null;

    
        // Only check languages/experiences once the form is submitted
        if ($form->isSubmitted()) {
               // custom presence checks
               $langs = $request->get('languages', []);
               $exps  = $request->get('experiences', []);
               if (count($langs) < 1) {
                   $languageError = 'At least one language is required.';
               }
               if (count($exps) < 1) {
                   $experienceError = 'At least one experience is required.';
               }
   
               // pull any form‐validation errors on individual fields
               if ($form->get('user_title')->getErrors()->count()) {
                   $titleError = $form->get('user_title')->getErrors()[0]->getMessage();
               }
               if ($form->get('introduction')->getErrors()->count()) {
                   $introductionError = $form->get('introduction')->getErrors()[0]->getMessage();
               }
               if ($form->get('skills')->getErrors()->count()) {
                   $skillsError = $form->get('skills')->getErrors()[0]->getMessage();
               }
           }
   
        // 3) If submitted & form valid & no custom errors, persist
        if (
            $form->isSubmitted()
            && $form->isValid()
            && !$languageError
            && !$experienceError
            && !$titleError
            && !$introductionError
            && !$skillsError
        ){
            //–– 3a) Sync skills string back onto the Cv entity
            $skills = $form->get('skills')->getData();
            $cv->setSkills($skills);
    
            //–– 3b) Title / LastViewed logic
            $originalTitle = $cv->getUserTitle();
            $count = (int) $em->getRepository(Cv::class)
                ->createQueryBuilder('c')
                ->select('COUNT(c)')
                ->where('c.user IS NULL')
                ->andWhere('c.id_cv != :id')
                ->andWhere('c.title LIKE :pattern')
                ->setParameter('id', $cv->getId())
                ->setParameter('pattern', $originalTitle.'%')
                ->getQuery()
                ->getSingleScalarResult();
    
            $cv->setTitle($count
                ? sprintf('%s (%d)', $originalTitle, $count+1)
                : $originalTitle
            );
            $cv->setLastViewed(new \DateTime());
    
            //–– 3c) Synchronize Languages
            $origLangs = [];
            foreach ($cv->getLanguages() as $lang) {
                $origLangs[$lang->getIdLanguage()] = $lang;
            }
            foreach ($request->get('languages', []) as $entry) {
                if (!empty($entry['id']) && isset($origLangs[$entry['id']])) {
                    $lang = $origLangs[$entry['id']];
                    $lang->setLanguageName($entry['name'])
                         ->setLevel($entry['level']);
                    unset($origLangs[$entry['id']]);
                } else {
                    $lang = new EntityLanguages();
                    $lang->setCv($cv)
                         ->setLanguageName($entry['name'])
                         ->setLevel($entry['level']);
                    $em->persist($lang);
                }
            }
            foreach ($origLangs as $toRemove) {
                $cv->getLanguages()->removeElement($toRemove);
                $em->remove($toRemove);
            }
    
            //–– 3d) Synchronize Experiences
            $origExps = [];
            foreach ($cv->getExperiences() as $exp) {
                $origExps[$exp->getIdExperience()] = $exp;
            }
            foreach ($request->get('experiences', []) as $entry) {
                if (!empty($entry['id']) && isset($origExps[$entry['id']])) {
                    $exp = $origExps[$entry['id']];
                    $exp->setType($entry['type'])
                        ->setPosition($entry['position'])
                        ->setLocationName($entry['location'])
                        ->setStartDate(new \DateTime($entry['startDate']))
                        ->setEndDate(new \DateTime($entry['endDate']))
                        ->setDescription($entry['description']);
                    unset($origExps[$entry['id']]);
                } else {
                    $exp = new Experience();
                    $exp->setCv($cv)
                        ->setType($entry['type'])
                        ->setPosition($entry['position'])
                        ->setLocationName($entry['location'])
                        ->setStartDate(new \DateTime($entry['startDate']))
                        ->setEndDate(new \DateTime($entry['endDate']))
                        ->setDescription($entry['description']);
                    $em->persist($exp);
                    $cv->addExperience($exp);
                }
            }
            foreach ($origExps as $toRemove) {
                $cv->getExperiences()->removeElement($toRemove);
                $em->remove($toRemove);
            }
    
            //–– 3e) Synchronize Certificates
            $origCerts = [];
            foreach ($cv->getCertificates() as $cert) {
                $origCerts[$cert->getIdCertificate()] = $cert;
            }
            foreach ($request->get('certificates', []) as $entry) {
                if (!empty($entry['id']) && isset($origCerts[$entry['id']])) {
                    $cert = $origCerts[$entry['id']];
                    $cert->setTitle($entry['name'])
                         ->setIssuedBy($entry['association'])
                         ->setIssueDate(new \DateTime($entry['date']))
                         ->setDescription($entry['description'])
                         ->setMedia($entry['media']);
                    unset($origCerts[$entry['id']]);
                } else {
                    $cert = new Certificates();
                    $cert->setCv($cv)
                         ->setTitle($entry['name'])
                         ->setIssuedBy($entry['association'])
                         ->setIssueDate(new \DateTime($entry['date']))
                         ->setDescription($entry['description'])
                         ->setMedia($entry['media']);
                    $em->persist($cert);
                    $cv->addCertificate($cert);
                }
            }
            foreach ($origCerts as $toRemove) {
                $cv->getCertificates()->removeElement($toRemove);
                $em->remove($toRemove);
            }
    
            //–– 3f) Flush & redirect
            $em->flush();
            $this->addFlash('success', 'CV updated successfully!');
            return $this->redirectToRoute('cv_show');
        }
    
        // 4) If we reach here, either form not submitted, not valid, or we had custom errors
        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Please fix the errors below.');
        }
    
        // 5) Rebuild your languages list for the dropdown
        try {
            $response = $httpClient->request('GET', 'https://restcountries.com/v2/all?fields=languages', [
                'http_version' => '1.1',
                'timeout'      => 10,
            ]);
            $countries = $response->toArray(false);
            $set = [];
            foreach ($countries as $c) {
                foreach ($c['languages'] ?? [] as $langObj) {
                    $set[$langObj['name']] = true;
                }
            }
            $availableLanguages = array_keys($set);
            sort($availableLanguages, SORT_FLAG_CASE | SORT_STRING);
        } catch (\Exception $e) {
            $availableLanguages = array_values(\Symfony\Component\Intl\Languages::getNames());
        }
    
        // 6) Re-render the edit form with your error flags
        return $this->render('cv/edit.html.twig', [
            'cvForm'           => $form->createView(),
            'cv'               => $cv,
            'languages'        => $availableLanguages,
            'cvLanguages'      => $cv->getLanguages(),
            'experiences'      => $cv->getExperiences(),
            'certificates'     => $cv->getCertificates(),
            'languageError'    => $languageError,
            'experienceError'  => $experienceError,
            'titleError'       => $titleError,
            'introductionError'=> $introductionError,
            'skillsError'      => $skillsError,
        ]);
    }
    

    #[Route('/cv/grammar-check', name: 'cv_grammar_check', methods: ['POST'])]
    public function checkGrammar(Request $request): JsonResponse
    {
        // Get the text from the frontend (assumed to be sent as a JSON body)
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['text']) || empty($data['text'])) {
            return $this->json(['error' => 'No text provided or text is empty.'], 400); // Return an error if no text is provided
        }

        $text = $data['text'];

        try {
            // Send the request to LanguageTool API for grammar checking
            $response = $this->httpClient->request('POST', 'https://api.languagetool.org/v2/check', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'text' => $text,
                    'language' => 'en-US',
                ]
            ]);

            // Parse the response from the API
            $jsonResponse = $response->toArray();

            // If there are no matches (no errors), return an empty array
            if (empty($jsonResponse['matches'])) {
                return $this->json([]);
            }

            $matches = $jsonResponse['matches'];
            $corrections = [];

            foreach ($matches as $match) {
                $offset = $match['offset'];
                $length = $match['length'];
                $incorrectWord = substr($text, $offset, $length);
                $replacements = $match['replacements'];

                // If there are suggested replacements, add the best suggestion
                if (!empty($replacements)) {
                    $bestSuggestion = $replacements[0]['value'];
                    $corrections[] = [
                        'incorrect' => $incorrectWord,
                        'suggestion' => $bestSuggestion,
                        'offset' => $offset,
                        'length' => $length,
                    ];
                }
            }

            // Return the corrections to the frontend
            return $this->json($corrections);

        } catch (\Exception $e) {
            // Handle any exceptions (e.g., connection issues)
            return $this->json(['error' => 'Failed to check grammar. Please try again later.'], 500);
        }
    }
    #[Route('/cv/export-pdf', name: 'cv_export_pdf', methods: ['POST'])]
    public function exportPdf(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $html = $data['html'] ?? '';
        $raw = $request->getContent();
        VarDumper::dump([
            'raw_body'      => $raw,
            'raw_type'      => gettype($raw),
            'decoded_json'  => json_decode($raw, true),
            'decoded_type'  => gettype(json_decode($raw, true)),
        ]);
    
        if (!trim($html)) {
            return $this->json(['error' => 'No HTML received'], 400);
        }

        // Optional: tweak PDF options
      // in your controller, before getOutputFromHtml():
$this->snappyPdf
->setOption('disable-smart-shrinking', true)
->setOption('zoom', 1)
->setOption('page-size', 'A4')
->setOption('margin-top', '0mm')
->setOption('margin-bottom', '0mm')
->setOption('margin-left', '0mm')
->setOption('margin-right', '0mm');


        $pdf = $this->snappyPdf->getOutputFromHtml($html);

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="cv.pdf"',
        ]);
    }





}
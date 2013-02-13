<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\InternJumpBundle\Controller\ObjectsController;
use Symfony\Component\Security\Core\SecurityContext;
use Objects\APIBundle\Controller\FacebookController;
use Objects\APIBundle\Controller\TwitterController;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Email;

require_once __DIR__ . '/../../../../vendor/FacebookSDK/src/facebook.php';

class InternjumpController extends ObjectsController {

    /**
     * This action to send email from 404 page
     * @author Ola
     */
    public function errorPage404Mailaction(){
       
        
        //get url from request param
        $url= $this->getRequest()->get('url');
        //get browser name from request param
        $browser= $this->getRequest()->get('browser');
        //get broswer version from request param
        $version= $this->getRequest()->get('version');
        //get Os name from request param
        $os= $this->getRequest()->get('os');
        
        //get Current logged in user if exist 
        if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //get logedin user objects
            $user = $this->get('security.context')->getToken()->getUser();
            $uname = "Student: ".$user->getLoginName()." - ".$user->getEmail(); 
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE_COMPANY')) {
            //get logedin company objects
            $company = $this->get('security.context')->getToken()->getUser();
            $uname = "Company: ".$company->getLoginName()." - ".$user->getEmail(); 
        }
        else{
            $uname="Anonymous";
        }
        //prepare message for email
                $message = \Swift_Message::newInstance()
                ->setSubject("404 error User Report Issue")
                ->setFrom($this->container->getParameter('contact_us_email'))
                ->setTo('olaobjects@yahoo.com')//$this->container->getParameter('contact_us_email'))
                ->setBody($this->container->get('templating')->render('ObjectsInternJumpBundle:Internjump:404Report.html.twig', array(
                                    'username' => $uname,
                                    'errorUrl' => $url,
                                    'userBrowser' => $browser,
                                    'browserVersion' => $version,
                                    'userOs' => $os,
                                    
                                )));
        //send the mail
        $this->container->get('mailer')->send($message);
        return $this->render('ObjectsInternJumpBundle:Internjump:mail404.html.twig');
    }


    /**
     * Action to get part of campus reps page text , will be rendered from base to be displayed in footer
     * @author Ola
     * 
     */
    public function campusRepsBaseAction() {
        $pageText = file_get_contents(__DIR__ . "/../../../../web/sitePages/CampusReps.txt");

        $pageText = strip_tags($pageText);

        if (strlen($pageText) > 200) {
            $pageText = substr($pageText, 0, 200) . '.........';
        }

        return new Response($pageText);
    }

    /**
     * Action that gets twitter page latest posts, will be rendered in base footer
     * @return type
     */
    public function getLatestTwittsAction() {
//        $url = "https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=internjump&count=3";
//
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
//        $c = curl_exec($ch);
//        $results = json_decode($c, true);
//
//        foreach ($results as &$result) {
//            $twittDate = new \DateTime($result['created_at']);
//            $result['date'] = $twittDate->format('d F Y h:i A');
//        }
        $container = $this->container;
        $count = 3;
        $results = TwitterController::getLastTweets($container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $container->getParameter('oauth_token'), $container->getParameter('oauth_token_secret'),'internjump', $count);
        return $this->render('ObjectsInternJumpBundle:Internjump:getLatestTwitts.html.twig', array(
                    'results' => $results
                ));
    }

    /**
     * this function used to display privacy page
     * @author ahmed
     * @return type
     */
    public function privacyAction() {
        $pageText = file_get_contents(__DIR__ . "/../../../../web/sitePages/PrivacyPolicy.txt");

        return $this->render('ObjectsInternJumpBundle:Internjump:text.html.twig', array(
                    'title' => 'Privacy',
                    'flag' => '',
                    'content' => $pageText
                ));
    }

    /**
     * this function used to display privacy page
     * @author ahmed
     * @return type
     */
    public function termsOfUseAction() {
        $pageText = file_get_contents(__DIR__ . "/../../../../web/sitePages/TermsOfService.txt");

        return $this->render('ObjectsInternJumpBundle:Internjump:text.html.twig', array(
                    'title' => 'TermsOfUse',
                    'flag' => '',
                    'content' => $pageText
                ));
    }

    /**
     * this function used to display privacy page
     * @author ahmed
     * @return type
     */
    public function aboutUsAction() {
        $pageText = file_get_contents(__DIR__ . "/../../../../web/sitePages/aboutUs.txt");

        $em = $this->getDoctrine()->getEntityManager();
        //get Founder repo
        $founderRepo = $em->getRepository('ObjectsInternJumpBundle:Founder');
        //get all founders
        $founders = $founderRepo->findAll();

        return $this->render('ObjectsInternJumpBundle:Internjump:text.html.twig', array(
                    'title' => 'AboutUs',
                    'flag' => 'yes',
                    'content' => $pageText,
                    'founders' => $founders,
                ));
    }

    /**
     * this function used to display campusReps page
     * @author Ola
     * @return type
     */
    public function campusRepsAction() {
        $flag = 0;
        $request = $request = $this->getRequest();
        
        $pageText = file_get_contents(__DIR__ . "/../../../../web/sitePages/CampusReps.txt");

        $em = $this->getDoctrine()->getEntityManager();
        //get Founder repo
        $founderRepo = $em->getRepository('ObjectsInternJumpBundle:Founder');
        //get all founders
        $founders = $founderRepo->findAll();

        $data=array();
        //prepare the validation constrains
        $collectionConstraint = new Collection(array(
            'Name' => new NotNull(),
            'Email' => new NotNull(),
            'Email' => new Email(),
            'Phone' => new NotNull(),
            'Message' => new NotNull()
                ));
        //create the contact form
        
           $form = $this->createFormBuilder($data, array(
                    'validation_constraint' => $collectionConstraint,
                ))
                        ->add('Name', 'text', array('required' => true, 'invalid_message' => 'please enter username'))
                        ->add('Email', 'email', array('required' => true, 'invalid_message' => 'please enter email'))
                        ->add('Phone', 'text', array('required' => true))
                        ->add('Message', 'textarea', array('required' => true, 'invalid_message' => 'please enter your messege'))
                   ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {

                $sendtoemail = $this->container->getParameter('info_email');
                $data = $form->getData();
                $body = $data['Message'];
                $subject = "Tell us about yourself";
                $from = $data['Email'];
                $message = \Swift_Message::newInstance()
                        ->setFrom($from)
                        ->setTo($sendtoemail)
                        ->setSubject($subject)
                        ->setBody('<html>' .
                        ' <head></head>' .
                        ' <body>' .
                        ' <b>Name:</b>'.$data['Name'].'<br />'.
                        ' <b>Phone:</b>'.$data['Phone'].'<br />'.
                        $body .
                        ' </body>' .
                        '</html>', 'text/html');
                $this->get('mailer')->send($message);
                $flag = 1;
            }
        }
        return $this->render('ObjectsInternJumpBundle:Internjump:CampusReps.html.twig', array(
                    'title' => 'Campus Reps',
                    'content' => $pageText,
                    'founders' => $founders,
                    'form' => $form->createView(),
                    'flag' => $flag,
                ));
    }

    /**
     * this function will show text page contain employers data
     * @author Ahmed
     */
    public function studentsDataAction() {
        $studentsData = file_get_contents(__DIR__ . '/../../../../web/sitePages/studentsData.txt');
        return $this->render('ObjectsInternJumpBundle:Internjump:studentsData.html.twig', array(
                    'studentsData' => $studentsData
                ));
    }

    /**
     * this function will show text page contain employers data
     * @author Ahmed
     */
    public function employersDataAction() {
        $employersData = file_get_contents(__DIR__ . '/../../../../web/sitePages/employersData.txt');
        return $this->render('ObjectsInternJumpBundle:Internjump:employersData.html.twig', array(
                    'employersData' => $employersData
                ));
    }

    /**
     * this function will send email to company after every notification
     * Example : InternjumpController::companyNotificationMail($this->container, user object, company object, notificationType, notificationTypeId);
     * @author Ahmed
     * @param object $container
     * @param object $company
     * @param object $user
     * @param string $notificationType
     * @param int $notificationId
     */
    static function companyNotificationMail($container, $user, $company, $notificationType, $notificationId) {
        if ($notificationType == 'user_answer_question') {
            $subject = 'user answered your question';
            $texat1 = 'has answered your question';
            $texat2 = $container->get('router')->generate('question_show', array('questionId' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_job_apply') {
            $subject = 'user applied on your job';
            $texat1 = 'has applied on your job';
            $texat2 = $container->get('router')->generate('internship_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_accept_job') {
            $subject = 'user has accepted your offer';
            $texat1 = 'has accepted your offer';
            $texat2 = $container->get('router')->generate('internship_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_reject_job') {
            $subject = 'user has rejected your offer';
            $texat1 = 'has rejected your offer';
            $texat2 = $container->get('router')->generate('internship_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_accept_interest') {
            $subject = 'user has accepted your interest';
            $texat1 = 'has accepted your interest';
            $texat2 = $container->get('router')->generate('company_see_user_data', array('userLoginName' => $user->getLoginName(), 'cvId' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_reject_interest') {
            $subject = 'user has rejected your interest';
            $texat1 = 'has rejected your interest';
            $texat2 = $container->get('router')->generate('company_see_user_data', array('userLoginName' => $user->getLoginName(), 'cvId' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_accept_interview') {
            $subject = 'user has accepted your interview';
            $texat1 = 'has accepted your interview';
            $texat2 = $container->get('router')->generate('interview_show', array('interviewId' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_reject_interview') {
            $subject = 'user has rejected your interview';
            $texat1 = 'has rejected your interview';
            $texat2 = $container->get('router')->generate('interview_show', array('interviewId' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_edit_task') {
            $subject = 'user has updated their assigned task';
            $texat1 = 'has updated their assigned task';
            $texat2 = $container->get('router')->generate('company_task_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_add_note') {
            $subject = 'user has added a note to assigned task';
            $texat1 = 'has added a note to assigned task';
            $texat2 = $container->get('router')->generate('company_task_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_edit_note') {
            $subject = 'user has edit a note to assigned task';
            $texat1 = 'has edit a note to assigned task';
            $texat2 = $container->get('router')->generate('company_task_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'user_message') {
            $subject = 'You have a new message';
            $texat1 = 'sent you new message';
            $texat2 = $container->get('router')->generate('show_company_message', array('id' => $notificationId), TRUE);
        }


        $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($container->getParameter('contact_us_email'))
                ->setTo($company->getEmail())
                ->setBody($container->get('templating')->render('ObjectsInternJumpBundle:Internjump:companyNotificationMail.html.twig', array(
                    'user' => $user,
                    'text1' => $texat1,
                    'texat2' => $texat2
                )))
        ;
        //send the mail
        $container->get('mailer')->send($message);

        return new Response('done');
    }

    /**
     * this function will send email to user after every notification
     * Example : InternjumpController::userNotificationMail($this->container,user object, company object, notificationType, notificationTypeId);
     * @author Ahmed
     * @param object $container
     * @param object $company
     * @param object $user
     * @param string $notificationType
     * @param int $notificationId
     */
    static function userNotificationMail($container, $user, $company, $notificationType, $notificationId) {
        if ($notificationType == 'company_question') {
            $subject = 'company has added a question for you';
            $texat1 = 'has added a question for you';
            $texat2 = $container->get('router')->generate('question_show', array('questionId' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_interest') {
            $subject = 'company has indicated interest! Great Work!';
            $texat1 = 'has indicated interest! Great Work!';
            $texat2 = $container->get('router')->generate('user_interest', array('interestId' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_job_hire') {
            $subject = 'company offer';
            $texat1 = 'has extended you an offer Congratulations!';
            $texat2 = $container->get('router')->generate('user_hire', array('userInternshipId' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_interview') {
            $subject = 'company interview';
            $texat1 = 'has extended you an interview request Congratulations!';
            $texat2 = $container->get('router')->generate('user_interview', array('interviewId' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_edit_task') {
            $subject = 'company edit your task';
            $texat1 = 'has edited your task. Check it out it might be important!';
            $texat2 = $container->get('router')->generate('student_task_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_add_note') {
            $subject = 'company add task note';
            $texat1 = 'has added a note to your task. Check it out it might be important!';
            $texat2 = $container->get('router')->generate('student_task_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_assign_task') {
            $subject = 'company has assigned you a task';
            $texat1 = 'has assigned you a task. Do not put it off too long!';
            $texat2 = $container->get('router')->generate('student_task_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_edit_note') {
            $subject = 'company edit task note';
            $texat1 = 'has edit a note to your task. Check it out it might be important!';
            $texat2 = $container->get('router')->generate('student_task_show', array('id' => $notificationId), TRUE);
        } elseif ($notificationType == 'company_message') {
            $subject = 'You have a new message';
            $texat1 = 'sent you new message';
            $texat2 = $container->get('router')->generate('show_user_message', array('id' => $notificationId), TRUE);
        }

        $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($container->getParameter('contact_us_email'))
                ->setTo($user->getEmail())
                ->setBody($container->get('templating')->render('ObjectsInternJumpBundle:Internjump:userNotificationMail.html.twig', array(
                    'company' => $company,
                    'text1' => $texat1,
                    'texat2' => $texat2
                )))
        ;
        //send the mail
        $container->get('mailer')->send($message);

        return new Response('done');
    }

    /**
     * this action used to logout the user and REMEMBERME cookie before facebook login action
     * @author Ahmed
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function executeLogoutAction() {
        $this->get('security.context')->setToken(null);
        //invalidate the current user session
        $this->getRequest()->getSession()->invalidate();
        $response = new Response();
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie('REMEMBERME', null, new \DateTime('-1 day')));
        $response->sendHeaders();
        return $response;
    }

    /**
     * @author Ola
     * Home page action 
     */
    public function homePageAction() {

        //get the request object
        $request = $this->getRequest();
        //get the session object
        $session = $request->getSession();

        //variables for auth
        $redirectFlag = "";
        $Url = "";


        $em = $this->getDoctrine()->getEntityManager();
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        //get home page companies logo
        $homeCompanies = $companyRepo->findBy(array('isHome' => TRUE));

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        if (isset($url)) {
            $url = $this->getRequest()->get('access_method');
        }

        if ($url == 'facebook') {


            $flag = "facebook";
            //Yes, inside facebook
            $facebook = new \Facebook(array(
                        'appId' => '282137608565990',
                        'secret' => 'c0e978a64bc520a8b677dfa9e57f4746',
                    ));
            // Get User ID
            $user = $facebook->getUser();


            if ($user) {
                
                if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE') || TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE_COMPANY')) {//->getEmail() != $user->
                    $this->executeLogoutAction();
                    return $this->redirect($this->generateUrl('site_homepage'));
                };


                try {
                    // Proceed knowing you have a logged in user who's authenticated.
                    $graph_url = 'https://graph.facebook.com/me?access_token=' . $facebook->getAccessToken();
                    $faceUser = json_decode(file_get_contents($graph_url));
                    $session->set('facebook_user', $faceUser);
                    $session->set('facebook_short_live_access_token', $facebook->getAccessToken());
                    $session->set('currentURL', 'http://apps.facebook.com/internjump/');
                    return $this->redirect($this->generateUrl('facebook_logging', array(), True));


//                    /****************************/
//                    //Get the user then Login now
//                    /****************************/
//                    $user1 = $em->getRepository('ObjectsUserBundle:User')->findOneBy(array('email' => $user_profile['email']));
//                    
                } catch (FacebookApiException $e) {
                    error_log($e);
                    $user = null;
                }
            } else {
                //not logged in FB user, then GO to fb login;
                $params = array(
                    //'scope' => 'read_stream, friends_likes',
                    'redirect_uri' => 'http://apps.facebook.com/internjump/' // 'http://internjump.com/app_dev.php/'
                );

                $loginUrl = $facebook->getLoginUrl($params);
                return $this->redirect($loginUrl);
            }
        }



        $flag = "normal";
        //echo "Normal";exit;
        //check for logedin user or company
        if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //get logedin user objects
            $user = $this->get('security.context')->getToken()->getUser();
            return $this->redirect($this->generateUrl('student_task', array('loginName' => $user->getLoginName()), TRUE));
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE_COMPANY')) {
            //get logedin company objects
            $company = $this->get('security.context')->getToken()->getUser();
            return $this->redirect($this->generateUrl('internship', array('loginName' => $company->getLoginName()), TRUE));
        }






        /* NOT LOGGED in */
        return $this->render('ObjectsInternJumpBundle:Internjump:homePage.html.twig', array(
                    'flag' => $flag,
                    'reFlag' => $redirectFlag,
                    'url' => $Url,
                    'homeCompanies' => $homeCompanies,
                    'home_page_video_id' => $this->container->getParameter('home_page_video_id')
                ));
//        return $this->redirect($this->generateUrl('login', array(), 'true'));
    }

    /**
     * @author Ola
     * Test invite friends action
     */
    public function inviteFriendsAction() {
        $app_id = "282137608565990";
        $canvas_page = "http://apps.facebook.com/282137608565990/";

        $message = "Would you like to join me in this great app?";

        $requests_url = "http://www.facebook.com/dialog/apprequests?app_id="
                . $app_id . "&redirect_uri=" . urlencode($canvas_page)
                . "&message=" . $message;

        if (empty($_REQUEST["request_ids"])) {
            echo("<script> top.location.href='" . $requests_url . "'</script>");
        } else {
            echo "Request Ids: ";
            print_r($_REQUEST["request_ids"]);
        }
    }

    /**
     * @author Ola
     * Action to render and show content of Contact us page from text file 
     */
    public function ContactUsAction() {
        $flag = 0;
        $request = $this->getRequest();

        $str = $this->container->getParameter('contact_detinations');
        $subjects = explode(',', $str);
        $allSubjects = array();
        foreach ($subjects as $subject) {
            $allSubjects[$subject] = $subject;
        }
        // print_r($allSubjects);
        
                $data=array();
        //prepare the validation constrains
        $collectionConstraint = new Collection(array(
            'Name' => new NotNull(),
            'Email' => new NotNull(),
            'Email' => new Email(),
            'Message' => new NotNull()
                ));
        //create the contact form
        $form = $this->createFormBuilder($data, array(
                    'validation_constraint' => $collectionConstraint,
                ))
                        ->add('Name', 'text', array('required' => true, 'invalid_message' => 'please enter username'))
                        ->add('Email', 'email', array('required' => true, 'invalid_message' => 'please enter email'))
                        ->add('Subject', 'choice', array('choices' => $allSubjects, 'required' => true))
                        ->add('Message', 'textarea', array('required' => true, 'invalid_message' => 'please enter your messege'))->getForm();

//        $cachetime=$this->container->getParameter('Time_With_Seconds_To_Cache_ContactUs_Page');
//        $response=new Response();
//        $response->setSharedMaxAge($cachetime);
//        $flag=1;

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {

                $sendtoemail = $this->container->getParameter('contact_us_email');
                $data = $form->getData();
                $body = $data['Message'];
                $subject = $data['Subject'];
                $from = $data['Email'];
                $message = \Swift_Message::newInstance()
                        ->setFrom($from)
                        ->setTo($sendtoemail)
                        ->setSubject($subject)
                        ->setBody('<html>' .
                        ' <head></head>' .
                        ' <body>' .
                        $body .
                        ' </body>' .
                        '</html>', 'text/html'
                );
                $test = $this->get('mailer')->send($message);
                $flag = 1;
            }
        }

        return $this->render('ObjectsInternJumpBundle:Internjump:ContactUs.html.twig', array(
                    'form' => $form->createView(),
                    'flag' => $flag,
                )); //,$response
    }

    /**
     * This action for frequently asked question page
     * @author Ola
     */
    public function FAQAction() {

        $em = $this->getDoctrine()->getEntityManager();
        //get AskedQuestion repo
        $AskedQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:AskedQuestion');
        //get all AskedQuestions
        $AskedQuestions = $AskedQuestionRepo->findAll();

        return $this->render('ObjectsInternJumpBundle:Internjump:FAQ.html.twig', array(
                    'AskedQuestions' => $AskedQuestions,
                ));
    }

    /**
     * This action for Schools page
     * @author Ola
     */
    public function schoolsAction() {
        $schoolsDataUpper = file_get_contents(__DIR__ . '/../../../../web/sitePages/schools_p1.txt');
        $schoolsDataLower = file_get_contents(__DIR__ . '/../../../../web/sitePages/schools_p2.txt');
        return $this->render('ObjectsInternJumpBundle:Internjump:school.html.twig', array(
                    'schoolsDataUpper' => $schoolsDataUpper,
                    'schoolsDataLower' => $schoolsDataLower
                ));
    }

    /**
     * This action for Resourses main page
     * @author Ola
     */
    public function AllResourcesAction($page) {
        $em = $this->getDoctrine()->getEntityManager();
        //get post repo
        $postRepo = $em->getRepository('ObjectsInternJumpBundle:Post');

        //get  posts per page constant from config file
        $itemsPerPage = $this->container->getParameter('resource_per_page');

        //Get all published posts
        $allPosts = $postRepo->getAllPosts($page, $itemsPerPage);

        foreach ($allPosts as &$post) {
            if (strlen($post->getPostBody()) > 200) {
                $post->setPostBody(substr($post->getPostBody(), 0, 200) . '....');
            }
        }
        //Get number of published posts
        $postsCount = $postRepo->countAllPosts();

        //for pagenation
        $lastPageNumber = (int) ($postsCount / $itemsPerPage);
        if (($postsCount % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }

        return $this->render('ObjectsInternJumpBundle:Internjump:AllPosts.html.twig', array(
                    'page' => $page,
                    'allPosts' => $allPosts,
                    'postsCount' => $postsCount,
                    'lastPageNumber' => $lastPageNumber
                ));
    }

    /**
     * This action to display one resource
     * @author Ola
     */
    public function showOneResourceAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        //get post repo
        $postRepo = $em->getRepository('ObjectsInternJumpBundle:Post');

        //Get post 
        $post = $postRepo->findOneBy(array('id' => $id));


        return $this->render('ObjectsInternJumpBundle:Internjump:showPost.html.twig', array(
                    'post' => $post,
                ));
    }

}

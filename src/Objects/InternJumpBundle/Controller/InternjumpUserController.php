<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Objects\APIBundle\Controller\TwitterController;
use Objects\APIBundle\Controller\FacebookController;
use Objects\APIBundle\Controller\LinkedinController;
use Objects\InternJumpBundle\Entity\UserLanguage;
use Objects\InternJumpBundle\Form\UserLanguageType;

class InternjumpUserController extends ObjectsController {

    /**
     * signup fourth step
     * @author Mahmoud
     */
    public function signupLanguageAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        if (count($user->getLanguages()) == 0) {
            //add one education entity to the user
            $user->addUserLanguage(new UserLanguage());
        }
        //create an education form
        $formBuilder = $this->createFormBuilder($user, array(
                    'validation_groups' => 'language'
                ))
                ->add('languages', 'collection', array('type' => new UserLanguageType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false));
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                $user = $form->getData();
                foreach ($user->getLanguages() as $language) {
                    $language->setUser($user);
                }
                //save the user data
                $this->getDoctrine()->getEntityManager()->flush();
                return $this->redirect($this->generateUrl('signup_cv'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:InternjumpUser:signup_language.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpLanguage_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUpLanguage_FormDesc'),
                ));
    }

    /**
     * this function used to edit langauge
     * @author ahmed
     * @param integer $id
     */
    public function userEditLanguageAction($id) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        //get request object
        $request = $this->getRequest();

        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $languageRepo = $em->getRepository('ObjectsInternJumpBundle:UserLanguage');
        //check if this language exist
        $language = $languageRepo->find($id);
        if (!$language) {
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => "This language dosn't exist."));
        }

        //check if this user the owner
        if ($user->getId() == $language->getUser()->getId()) {
            $form = $this->createForm(new UserLanguageType(), $language);

            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {
                    //save the new language
                    $em->flush();


                    return $this->redirect($this->generateUrl('user_edit_language', array('id' => $id)));
                }
            }

            return $this->render('ObjectsInternJumpBundle:InternjumpUser:editLanguage.html.twig', array(
                        'form' => $form->createView(),
                        'id' => $id,
                        'formName' => $this->container->getParameter('studentEditLanguage_FormName'),
                        'formDesc' => $this->container->getParameter('studentEditLanguage_FormDesc'),
                    ));
        } else {
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => "You can't edit this language."));
        }
    }

    /**
     * this function used to add languages for active users
     * @author ahmed
     */
    public function userLanguagesAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        //get request object
        $request = $this->getRequest();

        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        //create new user language
        $newUserLanguage = new UserLanguage();
        $newUserLanguage->setUser($user);
        $form = $this->createForm(new UserLanguageType(), $newUserLanguage);

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                //save the new language
                $em->persist($newUserLanguage);
                $em->flush();


                return $this->redirect($this->generateUrl('student_task', array('loginName' => $user->getLoginName())));
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:newLanguage.html.twig', array(
                    'newUserLanguage' => $newUserLanguage,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddLanguage_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddLanguage_FormDesc'),
                ));
    }

    /**
     * this function used to upload users cv file
     * @author ahmed
     */
    public function uploadCvAction() {

        //read the cv file
        $document_file = '/opt/lampp/htdocs/internjump/src/Objects/InternJumpBundle/Entity/../../../../web/cvFiles/2nd.doc';
        $text_from_doc = shell_exec('antiword ' . $document_file);
//        $text_from_doc = shell_exec('abiword --to=html ' . $document_file);

        echo $text_from_doc;
        exit;
        //explode it for each new line
        $lines = explode(PHP_EOL, $text_from_doc);

        //prepare the result cv data array
        $cvDataArray = array();
        //try to get the objective
        $cvDataArray ['objective'] = '';
        $objectiveFlag = FALSE;
        foreach ($lines as $line) {
            if ($objectiveFlag) {
                $cvDataArray ['objective'] .= $line . ' ';
                //check for end of objective
                if (preg_match('/[.]/', $line))
                    break;
            } elseif (preg_match('/objective/i', $line)) {
                $objectiveFlag = TRUE;
            }
        }

        //try to get the skills
        $cvDataArray ['skills'] = '';
        $skillsFlag = FALSE;
        foreach ($lines as $line) {
            if ($skillsFlag) {
                $cvDataArray ['skills'] .= $line . ' ';
                //check for end of objective
                if (preg_match('/[.]/', $line))
                    break;
            } elseif (preg_match('/skills/i', $line)) {
                $skillsFlag = TRUE;
            }
        }


        print_r($cvDataArray);
        exit;




        //check for logrdin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return new Response('You are not authenticated');
        }
        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //create new cv
        $newCV = new \Objects\InternJumpBundle\Entity\CV();
        $newCV->setUser($user);

        // creating the upload form
        $formValidationGroups = array('cvFile');
        $form = $this->createFormBuilder($newCV, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('file', 'file')
                ->getForm();
        // checking the validation of the form
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                //set cv name
                $nowDate = new \DateTime();
                $newCV->setName('auto ' . $nowDate->format('Y-m-d h:i:s A'));


                //read the cv file
                $document_file = '/opt/lampp/htdocs/internjump/src/Objects/InternJumpBundle/Entity/../../../../web/cvFiles/50fe5490b55dc-old.doc';
                $text_from_doc = shell_exec('/usr/bin/antiword ' . $document_file);
                //explode it for each new line
                $lines = explode(PHP_EOL, $text_from_doc);

                //prepare the result cv data array
                $cvDataArray = array();
                //this array will hold the words which will be used to find cv data in the cv file
                $cvSearchWordsArray = array(
                    'objective', 'achievments', 'experience', 'work', 'education'
                );
                $cvDataArray ['objective'] = '';
                $objectiveFlag = FALSE;
                foreach ($lines as $line) {

                    foreach ($cvSearchWordsArray as $cvSearchWord) {
                        if ($cvSearchWord != 'objective') {
                            //check if found another searchable word to stop objective
                            if (preg_match("/$cvSearchWord/i", $line)) {
                                $objectiveFlag = FALSE;
                            }
                        }
                    }

                    //try to get the objective
                    if ($objectiveFlag) {
                        $cvDataArray ['objective'] .= $line . ' ';
                    } elseif (preg_match('/objective/i', $line)) {
                        $cvDataArray ['objective'] .= $line;
                        $objectiveFlag = TRUE;
                    }
                }

                if (isset($cvDataArray ['objective']) && $cvDataArray ['objective'] != '') {
                    $newCV->setObjective($cvDataArray ['objective']);
                } else {
                    $newCV->setObjective('aaaa');
                }

                $em->persist($newCV);
                $em->flush();
                echo $newCV->getAbsolutePath();
                return new Response('done');
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:uploadCv.html.twig', array(
                    'form' => $form->createView()
                ));
    }

    /**
     * this function used to add/edit personal question for the logedin user
     * @author Ahmed
     * @param int $questionId
     * @param string $answerText
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEditPersonalQuestionAnswerAction($questionId, $answerText) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $personalQuestionAnswerRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestionAnswer');
        $PersonalQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestion');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //get the question
        $question = $PersonalQuestionRepo->find($questionId);

        //check if this user answer this question before
        $answerFlag = $personalQuestionAnswerRepo->findOneBy(array('user' => $user->getId(), 'question' => $questionId));
        if ($answerFlag) {
            $answerFlag->setAnswer($answerText);
        } else {
            //create new answer
            $newAnswer = new \Objects\InternJumpBundle\Entity\PersonalQuestionAnswer();
            $newAnswer->setAnswer($answerText);
            $newAnswer->setUser($user);
            $newAnswer->setQuestion($question);

            $em->persist($newAnswer);
        }

        $em->flush();

        return new Response('done');
    }

    /**
     * this action used for answer internjump personal quiz
     * @author Ahmed
     * @return type
     */
    public function personalQuestionsAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $PersonalQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestion');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //get all personal questiond
        $questions = $PersonalQuestionRepo->getUserPersonalQuestions($user->getId());


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:personalQuestions.html.twig', array(
                    'questions' => $questions
                ));
    }

    /**
     * this function will update the user internjump quiz score
     * @author Ahmed
     * @param int $score
     */
    public function updateUserQuizScoreAction($score) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$this->getRequest()->isXmlHttpRequest()) {
            return new Response('faild');
        }
        $em = $this->getDoctrine()->getEntityManager();
        $quizRepo = $em->getRepository('ObjectsInternJumpBundle:Quiz');
        $quizResultRepo = $em->getRepository('ObjectsInternJumpBundle:QuizResult');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //check if the user have score
        if (!$user->getScore()) {
            $user->setScore($score);
            $em->flush();
        }

        //find all quiz results
        $quizResults = $quizResultRepo->findAll();
        $resultObject = null;
        foreach ($quizResults as $result) {
            if ($result->getScore() >= $score) {
                $resultObject = $result;
                break;
            }
            $resultObject = $result;
        }

        //check if user have social acounts
        $userSocialAccounts = $user->getSocialAccounts();
        $container = $this->container;
        if ($userSocialAccounts) {
            $status = 'I passed internJump personality quiz and my result is ' . $resultObject->getMessage();
            $picture = $this->generateNormalUrl('site_homepage', array(), TRUE) . $resultObject->getTimThumbUrl(95, 95);
            $link = $this->generateNormalUrl('site_homepage', array(), TRUE);

            // check if have facebook
            if ($userSocialAccounts->isFacebookLinked()) {
                FacebookController::postOnUserWallAndFeedAction($userSocialAccounts->getFacebookId(), $userSocialAccounts->getAccessToken(), $status, null, null, $link, $picture);
            }

            // check if have twitter
            if ($userSocialAccounts->isTwitterLinked()) {
                TwitterController::twitt($status . ' ' . $link, $container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $userSocialAccounts->getOauthToken(), $userSocialAccounts->getOauthTokenSecret());
            }

            // check if have linkedin
            if ($userSocialAccounts->isLinkedInLinked()) {
                LinkedinController::linkedInShare($container->getParameter('linkedin_api_key'), $container->getParameter('linkedin_secret_key'), $userSocialAccounts->getLinkedinOauthToken(), $userSocialAccounts->getLinkedinOauthTokenSecret(), $status, $status, $status, $link, $picture);
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:QuizResult.html.twig', array(
                    'resultObject' => $resultObject
                ));
    }

    /**
     * this action for the logedin users to answer know you quiz link
     * @author Ahmed
     */
    public function internjumbQuizPageAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $quizRepo = $em->getRepository('ObjectsInternJumpBundle:Quiz');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        $quiz = $quizRepo->findAll();



        return $this->render('ObjectsInternJumpBundle:InternjumpUser:internjumbQuizPage.html.twig', array(
                    'quiz' => $quiz, 'user' => $user
                ));
    }

    /**
     * this function will show user notifications
     * @author Ahmed
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUserNotificationsAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return new Response('faild');
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');
        $companyMessageRepo = $em->getRepository('ObjectsInternJumpBundle:Message');

        //count all user notifications
        $allUserNotificationsCount = $userNotificationRepo->countAllUserNotifications($user->getId());
        //get the user new notifications by type
        $userNotificationsCountByType = $userNotificationRepo->countUserNotifications($user->getId());
        //count new company message
        $newMessagesCount = $companyMessageRepo->getUserNewMessagesCount($user->getId());

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userNotifications.html.twig', array(
                    'allUserNotificationsCount' => $allUserNotificationsCount,
                    'user' => $user,
                    'userNotificationsCountByType' => $userNotificationsCountByType,
                    'newMessagesCount' => $newMessagesCount
                ));
    }

    /**
     * this function will used to accept/reject company hire from the owner logedin user
     * @author Ahmed
     * @param int $userInternshipId
     * @param string $status
     */
    public function userAcceptRejectHireAction($userInternshipId, $status) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');

        //get the userInternship object
        $userInternship = $userInternshipRepo->find($userInternshipId);

        if ($userInternship->getUser()->getId() == $user->getId() && $userInternship->getStatus() == 'pending') {
            $userInternship->setStatus($status);
            $userInternship->setCreatedAt(new \DateTime());

            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($userInternship->getInternship()->getCompany());
            $companyNotification->setUser($user);
            if ($status == 'rejected') {
                $companyNotification->setType('user_reject_job');
                InternjumpController::companyNotificationMail($this->container, $user, $userInternship->getInternship()->getCompany(), 'user_reject_job', $userInternship->getInternship()->getId());
            } else {
                $companyNotification->setType('user_accept_job');

                //check if user have social acounts
                $userSocialAccounts = $user->getSocialAccounts();
                $container = $this->container;
                if ($userSocialAccounts) {
                    $status = 'I accept internjump hire request';
                    $link = $this->generateNormalUrl('site_homepage', array(), TRUE);

                    // check if have facebook
                    if ($userSocialAccounts->isFacebookLinked()) {
                        FacebookController::postOnUserWallAndFeedAction($userSocialAccounts->getFacebookId(), $userSocialAccounts->getAccessToken(), $status, null, null, $link, null);
                    }

                    // check if have twitter
                    if ($userSocialAccounts->isTwitterLinked()) {
                        TwitterController::twitt($status . ' ' . $link, $container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $userSocialAccounts->getOauthToken(), $userSocialAccounts->getOauthTokenSecret());
                    }

                    // check if have linkedin
                    if ($userSocialAccounts->isLinkedInLinked()) {
                        LinkedinController::linkedInShare($container->getParameter('linkedin_api_key'), $container->getParameter('linkedin_secret_key'), $userSocialAccounts->getLinkedinOauthToken(), $userSocialAccounts->getLinkedinOauthTokenSecret(), $status, $status, $status, $link, null);
                    }
                }

                InternjumpController::companyNotificationMail($this->container, $user, $userInternship->getInternship()->getCompany(), 'user_accept_job', $userInternship->getInternship()->getId());
            }

            $companyNotification->setTypeId($userInternship->getInternship()->getId());
            $em->persist($companyNotification);

            $em->flush();
        }

        return new Response('done');
    }

    /**
     * this function will show an job hire request for the logedin owner user
     * @author Ahmed
     * @param int $userInternshipId
     */
    public function userHireAction($userInternshipId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');

        //get the userInternship object
        $userInternship = $userInternshipRepo->find($userInternshipId);
        if (!$userInternship || $userInternship->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('request_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }


        //get the company
        $company = $userInternship->getInternship()->getCompany();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userHire.html.twig', array(
                    'user' => $user,
                    'userInternship' => $userInternship,
                    'company' => $company
                ));
    }

    /**
     * this function will used to accept/reject company interview from the owner logedin user
     * @author Ahmed
     * @param int $interviewId
     * @param string $status
     */
    public function userAcceptRejectInterviewAction($interviewId, $status) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get the interview object
        $interview = $interviewRepo->find($interviewId);

        if ($interview->getUser()->getId() == $user->getId() && $interview->getAccepted() == 'pending') {
            $interview->setAccepted($status);


            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($interview->getCompany());
            $companyNotification->setUser($user);
            if ($status == 'rejected') {
                $companyNotification->setType('user_reject_interview');
                InternjumpController::companyNotificationMail($this->container, $user, $interview->getCompany(), 'user_reject_interview', $interviewId);
            } else {
                $companyNotification->setType('user_accept_interview');
                InternjumpController::companyNotificationMail($this->container, $user, $interview->getCompany(), 'user_accept_interview', $interviewId);
            }

            $companyNotification->setTypeId($interviewId);
            $em->persist($companyNotification);

            $em->flush();
        }

        return new Response('done');
    }

    /**
     * this function will show an interview for the logedin owner user
     * @author Ahmed
     * @param int $interviewId
     */
    public function userInterviewAction($interviewId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get the interview object
        $interview = $interviewRepo->find($interviewId);
        if (!$interview || $interview->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('interview_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get the company
        $company = $interview->getCompany();

        //check for the valid to date
        $validToFalg = 1;
        if ($interview->getInterviewDate() < new \DateTime('today')) {
            $validToFalg = 0;
        }


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userInterview.html.twig', array(
                    'user' => $user,
                    'interview' => $interview,
                    'validToFalg' => $validToFalg,
                    'company' => $company
                ));
    }

    /**
     * this function will used to accept/reject company interest from the owner logedin user
     * @author Ahmed
     * @param int $interestId
     * @param string $status
     */
    public function userAcceptRejectinterestAction($interestId, $status) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');

        //get the interest object
        $interest = $interestRepo->find($interestId);

        if ($interest->getUser()->getId() == $user->getId() && $interest->getAccepted() == 'pending') {
            $interest->setAccepted($status);
            $interest->setRespondedAt(new \DateTime());


            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($interest->getCompany());
            $companyNotification->setUser($user);
            if ($status == 'rejected') {
                $companyNotification->setType('user_reject_interest');
                InternjumpController::companyNotificationMail($this->container, $user, $interest->getCompany(), 'user_reject_interest', $interest->getCvId());
            } else {
                $companyNotification->setType('user_accept_interest');
                InternjumpController::companyNotificationMail($this->container, $user, $interest->getCompany(), 'user_accept_interest', $interest->getCvId());
            }

            $companyNotification->setTypeId($interest->getCvId());
            $em->persist($companyNotification);

            $em->flush();
        }

        return new Response('done');
    }

    /**
     * this function will show company interest to the owner user
     * @author Ahmed
     * @param int $interestId
     */
    public function userInterestAction($interestId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get the interest object
        $interest = $interestRepo->find($interestId);
        if (!$interest || $interest->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('interest_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get the interest cv
        $cv = $cvRepo->find($interest->getCvId());

        //get the interest company
        $company = $interest->getCompany();

        //check for the valid to date
        $validToFalg = 1;
        if ($interest->getValidTo() < new \DateTime('today')) {
            $validToFalg = 0;
        }


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userInterest.html.twig', array(
                    'user' => $user,
                    'interest' => $interest,
                    'validToFalg' => $validToFalg,
                    'cv' => $cv,
                    'company' => $company
                ));
    }

    /**
     * this function will mark all notifications as read for the logedin user
     * @author Ahmed
     */
    public function userMarkAllNotificationsAsReadAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin company objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');

        //get all unread notifications
        $unReadNotifications = $userNotificationRepo->findBy(array('user' => $user->getId(), 'isNew' => TRUE));
        foreach ($unReadNotifications as $unReadNotification) {
            $unReadNotification->setIsNew(FALSE);
        }

        $em->flush();
        return new Response('done');
    }

    /**
     * this function will mark notification as read for the logedin user
     * @author Ahmed
     * @param int $notificationId
     */
    public function userNotificationMardAsReadAction($notificationId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin company objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');

        //get the notification
        $notification = $userNotificationRepo->find($notificationId);

        //check if this company is the owner
        if ($notification->getUser()->getId() == $user->getId()) {
            $notification->setIsNew(FALSE);
            $em->flush();
        }
        return new Response('done');
    }

    /**
     * this function will show all user notification
     * @author Ahmed
     */
    public function UserNotificationAction($type, $page, $itemsPerPage) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');

        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('user_notifications_per_page_' . $user->getId(), 10);
        }

        //get all user notifications
        if ($type == 'all') {
            $userNotifications = $userNotificationRepo->getUserNotifications($user->getId(), null, $page, $itemsPerPage);
            $count = sizeof($userNotificationRepo->getUserNotifications($user->getId(), null, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        } else {
            $userNotifications = $userNotificationRepo->getUserNotifications($user->getId(), $type, $page, $itemsPerPage);
            $count = sizeof($userNotificationRepo->getUserNotifications($user->getId(), $type, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        }
        //check if there is unread notifications
        $unreadNotifications = $userNotificationRepo->findBy(array('user' => $user->getId(), 'isNew' => TRUE));

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userNotification.html.twig', array(
                    'user' => $user,
                    'type' => $type,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'userNotifications' => $userNotifications,
                    'unreadNotifications' => $unreadNotifications
                ));
    }

    /**
     * this function will add/edit company question answer for the logedin user
     * @author Ahmed
     * @param int $questionId
     */
    public function userAnswerCompanyQuestionAction($questionId, $answerText) {
        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');

        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //get the question
        $question = $companyQuestionRepo->find($questionId);

        //check if the logedin user is the owner
        if ($question->getUser()->getId() == $user->getId()) {
            $question->setAnswer($answerText);
            $em->flush();

            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($question->getCompany());
            $companyNotification->setUser($question->getUser());
            $companyNotification->setType('user_answer_question');
            $companyNotification->setTypeId($question->getId());
            $em->persist($companyNotification);
            $em->flush();

            //send email to company
            InternjumpController::companyNotificationMail($this->container, $question->getUser(), $question->getCompany(), 'user_answer_question', $question->getId());

            return new Response('done');
        } else {
            return new Response('faild');
        }
    }

    /**
     * this function will used the question status for the loged in user
     * @author Ahmed
     * @param type $questionId
     * @param string $status
     */
    public function changeQuestionShowOnCvAction($questionId, $status) {
        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');

        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //get the question
        $question = $companyQuestionRepo->find($questionId);

        //check if the logedin user is the owner
        if ($question->getUser()->getId() == $user->getId()) {
            $question->setShowOnCV($status);
            $em->flush();
            return new Response('done');
        } else {
            return new Response('faild');
        }
    }

    /**
     * this function will used to change cv status for the loged in user
     * @author Ahmed
     * @param int $cvId
     * @param string $status
     */
    public function changeUserCvStatusAction($cvId, $status) {
        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //get user cv
        $userCv = $cvRepo->find($cvId);
        //check if the logedin user is the owner
        if ($userCv->getUser()->getId() == $user->getId()) {
            $userCv->setIsActive($status);
            $em->flush();
            return new Response('done');
        } else {
            return new Response('faild');
        }
    }

    /**
     * this function will show the user cv for the owner user
     * @author Ahmed
     * @param string $loginName
     * @param int $cvId
     */
    public function userPortalHomeAction($loginName, $cvId) {
        if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //get the user
            $user = $this->get('security.context')->getToken()->getUser();
            if ($user->getLoginName() != $loginName) {
                return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            }
        } else {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');
        $personalQuestionAnswerRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestionAnswer');

        //get user cv
        $userCv = $cvRepo->find($cvId);

        if (!$userCv || $userCv->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get user companies questions
        $companiesQuestions = $companyQuestionRepo->findBy(array('user' => $user->getId()), array('createdAt' => 'desc'));

        //calculate birth date
        $age = null;
        if ($user->getDateOfBirth()) {
            $date = $user->getDateOfBirth();
            $now = new \DateTime();
            $age = $now->diff($date);
            $age = $age->y;
        }


        //get user personal question answers
        $userPersonalQuestionAnswers = $personalQuestionAnswerRepo->findBy(array('user' => $user->getId()));

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userPortalHome.html.twig', array(
                    'user' => $user,
                    'userCv' => $userCv,
                    'cvId' => $cvId,
                    'age' => $age,
                    'companiesQuestions' => $companiesQuestions,
                    'userPersonalQuestionAnswers' => $userPersonalQuestionAnswers
                ));
    }

    /**
     * this function will show user which apply for a job data for a company
     * @author Ahmed
     * @param string $userLoginName
     */
    public function companySeeUserDataAction($userLoginName, $cvId) {
        //check for loggedin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }


        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        $request = $this->getRequest();


        //get user bject
        $userObject = $userRepo->findOneBy(array('loginName' => $userLoginName));

        if (!$userObject) {
            $message = $this->container->getParameter('user_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get user cv
        $userCv = $cvRepo->find($cvId);

        if (!$userCv || $userCv->getUser()->getId() != $userObject->getId() || $userCv->getIsActive() == FALSE) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //increment cv views number
        $userCv->setViewsCount($userCv->getViewsCount() + 1);
        $em->flush();

        //get user companies questions
        $companiesQuestions = $companyQuestionRepo->findBy(array('user' => $userObject->getId(), 'showOnCV' => TRUE), array('createdAt' => 'desc'));

        //check if this user accept company interest
        $companyUserInterestFlag = 0;
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //get logedin company objects
            $company = $this->get('security.context')->getToken()->getUser();
            $interestObject = $interestRepo->findOneBy(array('company' => $company->getId(), 'user' => $userObject->getId()));
            if ($interestObject) {
                if ($interestObject->getAccepted() == 'accepted') {
                    //user accept interest
                    $companyUserInterestFlag = 1;
                } elseif ($interestObject->getAccepted() == 'rejected') {
                    $companyUserInterestFlag = 2;
                } elseif ($interestObject->getAccepted() == 'pending') {
                    //user didn't accept the company interest
                    if ($interestObject->getValidTo() > new \DateTime()) {
                        $companyUserInterestFlag = 3;
                    } else {
                        //the company interest invalide
                        $companyUserInterestFlag = 4;
                    }
                }
            }
        }

        //check if there is error message
        $errorMessage = $request->get('errorMessage');

        //calculate birth date
        $age = null;
        if ($userObject->getDateOfBirth()) {
            $date = $userObject->getDateOfBirth();
            $now = new \DateTime();
            $age = $now->diff($date);
            $age = $age->y;
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:companySeeUserData.html.twig', array(
                    'userObject' => $userObject,
                    'userCv' => $userCv,
                    'cvId' => $cvId,
                    'age' => $age,
                    'errorMessage' => $errorMessage,
                    'companyUserInterestFlag' => $companyUserInterestFlag,
                    'companiesQuestions' => $companiesQuestions,
                    'user_interset_waiting_message' => $this->container->getParameter('company_user_interset_waiting_message_user_data_page'),
                    'company_user_interset_reject_message' => $this->container->getParameter('company_user_interset_reject_message_user_data_page'),
                    'company_user_hire_pending_message' => $this->container->getParameter('company_user_hire_pending_message_user_data_page'),
                    'company_user_hire_accept_message' => $this->container->getParameter('company_user_hire_accept_message_user_data_page'),
                    'company_user_hire_reject_message' => $this->container->getParameter('company_user_hire_reject_message_user_data_page'),
                    'company_user_interview_pending_message' => $this->container->getParameter('company_user_interview_pending_message_user_data_page'),
                    'company_user_interview_accept_message' => $this->container->getParameter('company_user_interview_accept_message_user_data_page'),
                    'company_user_interview_reject_message' => $this->container->getParameter('company_user_interview_reject_message_user_data_page')
                ));
    }

    /**
     * This function for displaying user search page and fill drop down lists with data
     * @author  Ola
     */
    public function StudentInternshipsSearchAction() {
        //check for loggedin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }

        //All categories
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');
        //get countries array
        $allCategories = $categoryRepo->getAllCategories();
        $allCategoriesArray = array();
        foreach ($allCategories as $value) {
            $allCategoriesArray [$value['id']] = $value['name'];
        }

        //All companys
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        //get countries array
        $allCompanys = $companyRepo->getAllCompanys();
        $allCompanysArray = array();
        foreach ($allCompanys as $value) {
            $allCompanysArray [$value['id']] = $value['name'];
        }

        //All Languages
        $allLanguagesArray = array('class' => 'ObjectsInternJumpBundle:Language', 'property' => 'name', 'empty_value' => '--- choose Language ---');//, 'expanded' => true, 'multiple' => true, 'required' => false);

        //get the request object
        $request = $this->getRequest();


        //create a search form
        $formBuilder = $this->createFormBuilder()
                ->add('country', 'choice', array('choices' => $allCountriesArray, 'preferred_choices' => array('US')
                ))
                //->add('city', 'choice', array('empty_value' => '--- choose city ---'))
                ->add('city', 'text')
                ->add('state', 'choice', array('empty_value' => '--- choose state ---'))
                ->add('category', 'choice', array('empty_value' => '--- choose Industry ---',
                    'choices' => $allCategoriesArray
                ))
                ->add('company', 'choice', array('empty_value' => '--- choose company ---',
            'choices' => $allCompanysArray
                ))
                ->add('language', 'entity', $allLanguagesArray);
        //create the form
        $form = $formBuilder->getForm();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userSearchPage.html.twig', array(
                    'form' => $form->createView()));
    }

    /**
     * This function for search ajax action
     * @author Ola
     */
    public function searchAction($title, $country, $city, $state, $category, $company, $lang, $page) {
        $request = $request = $this->getRequest();

        //to check if Ajax Request
//        if (!$request->isXmlHttpRequest()) {
//            return new Response("Faild");
//        }

        /****to get array of keywords from search text ****/
        $keywordsArray = explode(" ", $title);

//        print_r($keywordsArray);exit;
        //get number of jobs per page
        $jobsPerPage = $this->container->getParameter('jobs_per_search_results_page');

        $em = $this->getDoctrine()->getEntityManager();
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        //get jobs search results array
        $userSearchResults = $internshipRepo->getJobsSearchResult($title, $country, $city, $state, $category, $company, $lang, $keywordsArray, $page, $jobsPerPage);

        //Limit the details to only 200 character
        foreach ($userSearchResults as &$job) {
            $jobDesc = strip_tags($job->getDescription());
            if (strlen($jobDesc) > 200) {
                $job->setDescription(substr($jobDesc, 0, 200) . '...');
            } else {
                $job->setDescription($jobDesc);
            }
        }
        /* pagenation part */
        //get count of all search result jobs
        $userSearchResultsCount = sizeof($internshipRepo->getJobsSearchResult($title, $country, $city, $state, $category, $company,$lang, $keywordsArray, 1, null));

        $lastPageNumber = (int) ($userSearchResultsCount / $jobsPerPage);
        if (($userSearchResultsCount % $jobsPerPage) > 0) {
            $lastPageNumber++;
        }

//        print_r($userSearchResults);
        //return new Response("Done");
        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userSearchResultPage.html.twig', array(
                    'jobs' => $userSearchResults,
                    'page' => $page,
                    'jobsPerPage' => $jobsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'title' => $title,
                    'country' => $country,
                    'city' => $city,
                    'state' => $state, 'category' => $category, 'company' => $company, 'lang' => $lang,
                ));
    }

    /**
     * @author Ola
     * to get all categories which start match with first 3 letters of entered text
     * @return json array of jobs
     */
    public function autocompleteAction() {

        $request = $this->getRequest();
        $text = $request->get('term');
        $em = $this->getDoctrine()->getEntityManager();

        //get cv repo
        $cvCategoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');
        //get autocomplete results
        $autoresult = $cvCategoryRepo->autocompletecategories($text);

        $response = new Response(json_encode($autoresult));
        return $response;
    }

    /**
     * @author Ola
     * to get all cities which start match with first 3 letters of entered text
     * @return json array of cities
     */
    public function citiesAutocompleteAction() {

        $request = $this->getRequest();
        $text = $request->get('city');
        $country = $request->get('country');
        $em = $this->getDoctrine()->getEntityManager();

        //get City repo
        $cvCityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        //get autocomplete results
        $autoresult = $cvCityRepo->autocompletecities($text, $country);

        $response = new Response(json_encode($autoresult));
        return $response;
    }

    /**
     * this function will used to ChangeStatus/Delete one or more CV (published/unpublished/delete)
     * @author Ola
     * @param Array of int $cvsId
     * @param string $status
     * @param string $method  define the action to perform delete/publish/unpublish
     */
    public function changeCvsStatusAction($cvsId, $status, $method) {

        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get loggedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //user cv(s) ids
        $cvsIdArr = explode(",", $cvsId);

        if ($cvsIdArr) {
            $Cvs = $cvRepo->PerformActionsOnUserCvs($cvsIdArr);
        }

        //if cvs found
        if ($Cvs) {
            //check if the loggedin user is owner for each cv
            foreach ($Cvs as $cv) {
                if ($cv->getUser()->getId() == $user->getId()) {
                    //in case publish/unpublish Buttons
                    if ($method == "pubUnpub") {
                        $cv->setIsActive($status);
                        $em->flush();
                    }
                    //in case Delete Button
                    elseif ($method == "delete") {
                        $em->remove($cv);
                        $em->flush();
                    }
                }
            }
        } else { //no cvs
            return new Response('faild');
        }
        //Action succeed
        return new Response('done');
    }

}

<?php

namespace Objects\InternJumpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Ahmed
 */
class NewJobsToSuitableCvsCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();
        //Defining the name of command, its description and its argument
        $this->setName('Send:NewJobsToSuitableCvs')
                ->setDescription('Send New Jobs To Suitable Cvs.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $userRepo = $em->getRepository('ObjectsUserBundle:User');


        $todayDate = new \DateTime();

        //select active users
        $activeUsers

        echo 'aaaaaaa';exit;
    }

}

?>

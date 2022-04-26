<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\EmailTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;

class SendMailController extends AbstractController
{
    #[Route('/send/mail', name: 'app_send_mail')]
    public function index(MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject('Time for Symfony Mailer!')
            ->html('<p>See Twig integration for better HTML integration!</p>');
        $reflectionClass = $em->getClassMetadata(Article::class);
        $fields = $reflectionClass->getFieldNames();
        $emailE = $em->getRepository(EmailTemplate::class)->findAll();
        /** @var EmailTemplate $email */
        $emailE = current($emailE);
        $content = $emailE->getContent();
        foreach ($fields as $k => $field) {
            $content = str_replace("[$field]", "{{ article.$field }}", $content);
        }
        foreach ($reflectionClass->getAssociationMappings() as $fieldName => $associationMapping) {
            $reflectionAssociationClass = $em->getClassMetadata($associationMapping['targetEntity']);
            $assocFields = $reflectionAssociationClass->getFieldNames();
            foreach ($assocFields as $k => $field) {
                $content = str_replace("[$fieldName.$field]", "{{ article.$fieldName.$field }}", $content);
            }
            $fields[$fieldName] = $assocFields;
        }
        $tmp = $this->container->get('twig')->createTemplate($content);


        $content = $tmp->render(['article' => $em->getRepository(Article::class)->find(1)]);
        $email->html($content);
        $mailer->send($email);
        return new Response($content);
    }
}

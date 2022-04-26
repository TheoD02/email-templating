<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\EmailTemplate;
use App\Form\EmailTemplateType;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\CKEditorBundle\Builder\JsonBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/email/template')]
class EmailTemplateController extends AbstractController
{
    #[Route('/', name: 'app_email_template_index', methods: ['GET'])]
    public function index(EmailTemplateRepository $emailTemplateRepository): Response
    {
        return $this->render('email_template/index.html.twig', [
            'email_templates' => $emailTemplateRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_email_template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EmailTemplateRepository $emailTemplateRepository, EntityManagerInterface $em): Response
    {
        $emailTemplate = new EmailTemplate();
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailTemplateRepository->add($emailTemplate);
            return $this->redirectToRoute('app_email_template_index', [], Response::HTTP_SEE_OTHER);
        }

        $reflectionClass = $em->getClassMetadata(Article::class);
        $fields = $reflectionClass->getFieldNames();
        foreach ($fields as $k => $field) {
            $fields["self.$field"] = "[$field]";
            unset($fields[$k]);
        }
        foreach ($reflectionClass->getAssociationMappings() as $fieldName => $associationMapping) {
            $reflectionAssociationClass = $em->getClassMetadata($associationMapping['targetEntity']);
            $assocFields = $reflectionAssociationClass->getFieldNames();
            foreach ($assocFields as $k => $field) {
                $assocFields["$fieldName.$field"] = "[$fieldName.$field]";
                unset($assocFields[$k]);
            }
            $fields[$fieldName] = $assocFields;
        }
        $builder = new JsonBuilder(PropertyAccess::createPropertyAccessor());
        $builder->setValues($fields);
        $vars = $builder->build();

        return $this->renderForm('email_template/new.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form,
            'vars' => $vars,
            'fields' => $fields
        ]);
    }

    #[Route('/{id}', name: 'app_email_template_show', methods: ['GET'])]
    public function show(EmailTemplate $emailTemplate): Response
    {
        return $this->render('email_template/show.html.twig', [
            'email_template' => $emailTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_email_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmailTemplate $emailTemplate, EmailTemplateRepository $emailTemplateRepository): Response
    {
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailTemplateRepository->add($emailTemplate);
            return $this->redirectToRoute('app_email_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('email_template/edit.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_email_template_delete', methods: ['POST'])]
    public function delete(Request $request, EmailTemplate $emailTemplate, EmailTemplateRepository $emailTemplateRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $emailTemplate->getId(), $request->request->get('_token'))) {
            $emailTemplateRepository->remove($emailTemplate);
        }

        return $this->redirectToRoute('app_email_template_index', [], Response::HTTP_SEE_OTHER);
    }
}

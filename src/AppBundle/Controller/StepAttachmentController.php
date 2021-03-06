<?php
/**
 * Created by PhpStorm.
 * User: quentinmachard
 * Date: 06/11/2017
 * Time: 18:23
 */

namespace AppBundle\Controller;


use AppBundle\Entity\AbstractStep;
use AppBundle\Entity\StepAttachment;
use AppBundle\Entity\Travel;
use AppBundle\Entity\User;
use AppBundle\Form\StepAttachmentType;
use CoreBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StepAttachmentController extends BaseController
{
    /**
     * List all StepAttachments
     *
     * @param Travel $travel
     * @param AbstractStep $step
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Travel $travel, AbstractStep $step, Request $request)
    {
        $this->checkAuthorization($travel);
        $this->checkNotFound($travel, $step);

        $qb = $this->getDoctrine()
            ->getRepository(StepAttachment::class)
            ->findAllByStepQueryBuilder($step);


        $paginatedCollection = $this->get('core.factory.pagination')->createCollection($qb, $request);

        $response = $this->createApiResponse($paginatedCollection, 200);

        return $response;
    }

    /**
     * Create a StepAttachment
     *
     * @param Travel $travel
     * @param AbstractStep $step
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Travel $travel, AbstractStep $step, Request $request)
    {
        $this->checkAuthorization($travel);
        $this->checkNotFound($travel, $step);

        $stepAttachment = new StepAttachment();

        // Create and process form
        $form = $this->createForm(StepAttachmentType::class, $stepAttachment);
        $this->processForm($form, $request);

        $stepAttachment->setStep($step);

        $em = $this->getDoctrine()->getManager();
        $em->persist($stepAttachment);
        $em->flush();

        return $this->createApiResponse($stepAttachment, 201);
    }

    /**
     * Get information of a StepAttachment
     *
     * @param Travel $travel
     * @param AbstractStep $step
     * @param StepAttachment $attachment
     *
     * @return Response
     */
    public function getAction(Travel $travel, AbstractStep $step, StepAttachment $attachment)
    {
        $this->checkAuthorization($travel);
        $this->checkNotFound($travel, $step, $attachment);

        return $this->createApiResponse($attachment, 200, array('details'));
    }

    /**
     * Update a StepAttachment
     *
     * @param Travel $travel
     * @param AbstractStep $step
     * @param StepAttachment $attachment
     * @param Request $request
     *
     * @return Response
     */
    public function updateAction(Travel $travel, AbstractStep $step, StepAttachment $attachment, Request $request)
    {
        $this->checkAuthorization($travel);
        $this->checkNotFound($travel, $step, $attachment);

        $form = $this->createForm(StepAttachmentType::class, $attachment);
        $this->processForm($form, $request, FALSE);

        $em = $this->getDoctrine()->getManager();
        $em->persist($attachment);
        $em->flush();

        return $this->createApiResponse($attachment, 200, array('details'));
    }

    /**
     * Delete a StepAttachment
     *
     * @param Travel $travel
     * @param AbstractStep $step
     * @param StepAttachment $attachment
     *
     * @return Response
     */
    public function deleteAction(Travel $travel, AbstractStep $step, StepAttachment $attachment)
    {
        $this->checkAuthorization($travel);
        $this->checkNotFound($travel, $step, $attachment);

        $em = $this->getDoctrine()->getManager();
        $em->remove($attachment);
        $em->flush();

        return $this->createApiResponse(null, 204);
    }

    /**
     * Download a StepAttachment
     *
     * @param Travel $travel
     * @param AbstractStep $step
     * @param StepAttachment $attachment
     *
     * @return Response
     */
    public function downloadAction(Travel $travel, AbstractStep $step, StepAttachment $attachment)
    {
        $this->checkAuthorization($travel);
        $this->checkNotFound($travel, $step, $attachment);

        $file = $attachment->getFile();

        if(!isset($file)) {
            throw new NotFoundHttpException();
        }

        $filename = $file->getFile()->getPathname();

        // Generate response
        $response = new Response();

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachment->getName() . '.' . $file->getExtension() . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(file_get_contents($filename));

        return $response;
    }

    /**
     * Check not found
     *
     * @param Travel $travel
     * @param AbstractStep $step
     * @param StepAttachment|null $stepAttachment
     *
     * @throws NotFoundHttpException
     */
    private function checkNotFound(Travel $travel, AbstractStep $step, StepAttachment $stepAttachment = null)
    {
        if ($step->getTravel()->getId() !== $travel->getId()) {
            throw new NotFoundHttpException();
        }

        if (isset($stepAttachment)) {
            if ($stepAttachment->getStep()->getId() !== $step->getId()) {
                throw new NotFoundHttpException();
            }
        }
    }

    /**
     * Check authorization of Travel for current User
     *
     * @param Travel $travel
     *
     * @throws AccessDeniedHttpException
     */
    private function checkAuthorization(Travel $travel)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($travel->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('You d\'ont have access to this Travel');
        }
    }
}
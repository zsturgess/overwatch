<?php

namespace Overwatch\UserBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Overwatch\UserBundle\Entity\User;
use Overwatch\UserBundle\Enum\AlertSetting;

/**
 * ApiController
 * Handles API requests made for Users
 * @Route("/api")
 */
class ApiController extends Controller
{
    private $em;
    
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->em = $this->getDoctrine()->getManager();
    }
    
    /**
     * Returns the list of possible alert settings
     * 
     * @Route("/alertSettings")
     * @Method({"GET"})
     * @ApiDoc(
     *     resource=true,
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function getAlertSettings()
    {
        return new JsonResponse(AlertSetting::getAll());
    }
    
    /**
     * Returns a list of all users
     * 
     * @Route("/users")
     * @Method({"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     resource=true,
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function getAllUsers()
    {
        $users = $this->em->getRepository("OverwatchUserBundle:User")->findAll();
        return new JsonResponse($users);
    }
    
    /**
     * Creates a new user with the given e-mail address
     * 
     * @Route("/users/{email}")
     * @Method({"POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     requirements={
     *         {"name"="email", "description"="The e-mail address of the user to create", "dataType"="email", "requirement"="Valid e-mail address"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function createUser($email)
    {
        $password = substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(openssl_random_pseudo_bytes(9))), 0, 8);
        $user = $this->get('fos_user.util.user_manipulator')->create($email, $password, $email, true, false);
        
        //send user e-mail with their pass
        $message = \Swift_Message::newInstance()
        ->setSubject('You have been invited to Overwatch')
        ->setFrom($this->getUser()->getEmail())
        ->setTo($email)
        ->setBody(
            $this->renderView(
                'OverwatchUserBundle:Email:invited.txt.twig',
                [
                    'inviter' => $this->getUser()->getEmail(),
                    'email' => $email,
                    'password' => $password
                ]
            )
        );
        $this->get('mailer')->send($message);
        
        return new JsonResponse($user, JsonResponse::HTTP_CREATED);
    }
    
    /**
     * Returns the user associated with the given e-mail address
     * 
     * @Route("/users/{email}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("user", class="OverwatchUserBundle:User")
     * @ApiDoc(
     *     requirements={
     *         {"name"="email", "description"="The e-mail address to search by", "dataType"="email", "requirement"="Valid e-mail address"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function findUser(User $user)
    {
        return new JsonResponse($user);
    }
    
    /**
     * Updates the current user's alert settings to the given setting
     * 
     * @Route("/users/alertSetting/{setting}")
     * @Method({"PUT","POST"})
     * @ApiDoc(
     *     requirements={
     *         {"name"="setting", "description"="The new alert setting for the user", "dataType"="integer", "requirement"="[0-4]"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     * @deprecated use updateUser() instead.
     * @todo Remove this once the frontend doesn't use it.
     */
    public function setAlertSetting($setting)
    {
        $this->getUser()->setAlertSetting($setting);
        $this->em->flush();
        
        return new JsonResponse($this->getUser());
    }
    
    /**
     * 
     * @Route("/users")
     * @Method({"PUT"})
     * @ApiDoc(
     *     parameters={
     *         {"name"="alertSetting", "description"="The new alert setting for the user", "required"=true, "dataType"="integer", "requirement"="[0-4]"},
     *         {"name"="telephoneNumber", "description"="The new telephone number for the user", "required"=true, "dataType"="string"},
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function updateUser(Request $request) {
        $user = $this->getUser();
        $user
            ->setAlertSetting($request->request->get('alertSetting', $user->getAlertSetting()))
            ->setTelephoneNumber($request->request->get('telephoneNumber', $user->getTelephoneNumber()));

        $this->em->flush();
        return new JsonResponse($this->getUser());
    }
    
    /**
     * Locks or unlocks the given user
     * 
     * @Route("/users/{id}/lock")
     * @Method({"PUT","POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the user to lock", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function toggleLockUser(User $user)
    {
        if ($user->getId() === $this->getUser()->getId()) {
            throw new AccessDeniedHttpException("You may not toggle locks on yourself.");
        }
        
        $user->setLocked(!$user->isLocked());
        $this->em->flush();
        
        return new JsonResponse($user);
    }
    
    /**
     * Updates the given user to the given role
     * 
     * @Route("/users/{id}/role/{role}")
     * @Method({"PUT","POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the user to update", "dataType"="integer", "requirement"="\d+"},
     *         {"name"="role", "description"="The new role for the user", "dataType"="role", "requirement"="ROLE_USER|ROLE_ADMIN|ROLE_SUPER_ADMIN"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function setUserRole(User $user, $role)
    {
        if ($user->getId() === $this->getUser()->getId()) {
            throw new AccessDeniedHttpException("You may not set roles on yourself.");
        }
        
        if (in_array($role, ["ROLE_USER", "ROLE_ADMIN", "ROLE_SUPER_ADMIN"])) {
            $user->setRoles([$role]);
        }
        
        $this->em->flush();
        
        return new JsonResponse($user);
    }
    
    /**
     * Deletes the given user
     * 
     * @Route("/users/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the user to delete", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function deleteUser(User $user)
    {
        if ($user->getId() === $this->getUser()->getId()) {
            throw new AccessDeniedHttpException("You may not delete yourself.");
        }
        
        $this->em->remove($user);
        $this->em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}

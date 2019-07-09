<?php
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 09/07/2019
 * Time: 21:27
 */

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api")
     */
    public function api(Request $request, UserPasswordEncoderInterface $encoder) : Response
    {

        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $entityManager = $this->getDoctrine()->getManager();

            switch ($data['method']) {
                case "getUserByUsername": {
                    if (isset($data['username'])) {
                        $user = $entityManager
                            ->getRepository(User::class)
                            ->findOneBy(['username' => $data['username']]);

                        $responseData = $this->get('serializer')->serialize($user, 'json');
                        $response = new Response($responseData, 200);
                    }
                }
                    break;
                case "deleteUserByUsername": {
                    if (isset($data['username'])) {
                        $user = $entityManager
                            ->getRepository(User::class)
                            ->findOneBy(['username' => $data['username']]);

                        $entityManager->remove($user);
                        $entityManager->flush();
                        $response = new JsonResponse(['message' => "Removed: " . $data['username']], 200);
                    }
                }
                    break;
                case "createUser": {
                    if (isset($data['username']) && isset($data['password'])) {
                        $user = new User($data['username']);
                        $user->setPassword($encoder->encodePassword($user, $data['password']));

                        $entityManager->persist($user);
                        $entityManager->flush();
                        $response = new JsonResponse(['message' => "Created new user: " . $data['username']], 200);
                    }
                }
                    break;
                case "updateUserByUsername": {
                    if (isset($data['username'])) {
                        $user = $entityManager
                            ->getRepository(User::class)
                            ->findOneBy(['username' => $data['username']]);

                        if (isset($data['password'])) {
                            $user->setPassword($encoder->encodePassword($user, $data['password']));
                        }

                        if (isset($data['newUsername'])) {
                            $user->setUsername($data['newUsername']);
                        }

                        $entityManager->flush();
                        $response = new JsonResponse(['message' => "Updated: " . $data['username']], 200);
                    }
                }
                    break;
                default: {
                    $response = new JsonResponse(['error' => "Method not found"], 500);
                }
                    break;
            }
        } else {
            $response = new JsonResponse(['error' => "Accept only json request"], 500);
        }

        return $response;
    }
}
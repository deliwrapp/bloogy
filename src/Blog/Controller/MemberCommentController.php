<?php

namespace App\Blog\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Blog\Repository\CommentRepository;
use App\Blog\Entity\Post;
use App\Blog\Entity\Comment;
use App\Blog\Form\CommentFormType;

/**
 * @Route("/comments")
 */
class MemberCommentController extends AbstractController
{
    /**
     * @Route("/", name="member_comment_list")
     */
    public function index(CommentRepository $commentRepo): Response
    {
        $comments = $commentRepo->findAll();

        return $this->render('@blog/comment/member/comment-list.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/post/{id}/update/{commentId}", name="member_comment_update")
     */
    public function update(int $id, int $commentId = null, ManagerRegistry $doctrine, Request $request): Response
    {
        $post = $doctrine->getRepository(Post::class);
        $post = $post->find($id);

        if (!$post) {
            $this->addFlash(
                'warning',
                'There is no post  with id ' . $id
            );
            return $this->redirect($this->generateUrl('member_post_list'));
        }

        if ($commentId) {
            $comment = $doctrine->getRepository(Comment::class);
            $comment = $comment->find($commentId);
            $submitBtn = 'Edit';

            if (!$comment) {
                $this->addFlash(
                    'warning',
                    'There is no comment  with id ' . $id
                );
                return $this->redirect($this->generateUrl('member_post_show', [
                    'id' => $id
                ]));
            }
        } else { 
            $comment = new Comment;
            $submitBtn = 'Create';
        }
        // COMMENT FORM BUILDER and CONFIGURE THE ACTION ROUTE
        $form = $this->createForm(
            CommentFormType::class,
            $comment,
            [
                'submitBtn' => $submitBtn,
                'action' => $this->generateUrl('member_comment_update', [
                    'id' => $post->getId(),
                    'commentId' => $commentId
                ]),
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $em = $doctrine->getManager();
            if (!$commentId) {
                $comment->setPost($post);
                $comment->setAuthor($this->getUser());
                // Enregistre dans le cache de Doctrine
                $em->persist($comment);
            }
            // INSERT ... IN post -- Enregistre en base de donnée
            $em->flush();
            // Add comment to inform user of the post creation
            $this->addFlash(
                'info',
                'Comment update with id '.$comment->getId()
            );
        }
        return $this->redirect($this->generateUrl('member_post_show', [
            'id' => $id
        ]));
    }

     /**
     * @Route("/delete/{id}/post/{postId}", name="member_comment_delete")
     */
    public function delete(int $id, int $postId, ManagerRegistry $doctrine, Request $request): Response
    {
        $post = $doctrine->getRepository(Post::class);
        $post = $post->find($postId);

        if (!$post) {
            $this->addFlash(
                'warning',
                'There is no post  with id ' . $id
            );
            return $this->redirect($this->generateUrl('member_post_list'));
        }

        $submittedToken = $request->request->get('token');

        // 'delete-comment' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-comment', $submittedToken)) {
            $em = $doctrine->getManager();
            $comment = $doctrine->getRepository(Comment::class);
            $comment = $comment->find($id);
            if (!$comment) {
                $this->addFlash(
                    'warning',
                    'There is no comment  with id ' . $id
                );
            } else {
                $em->remove($comment);
                $em->flush();
                $this->addFlash(
                    'success',
                    'The comment with ' . $id . ' have been deleted '
                );
            } 
        } else {
            $this->addFlash(
                'warning',
                'Your CSRF token is not valid ! '
            );
        }
        
        return $this->redirect($this->generateUrl('member_post_show', [
            'id' => $postId
        ]));
    }

}

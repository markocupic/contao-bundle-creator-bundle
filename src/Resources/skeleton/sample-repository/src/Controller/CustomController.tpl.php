<?php

###phpdoc###

declare(strict_types=1);

namespace ###toplevelnamespace###\###sublevelnamespace###\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

/**
 * Class CustomController
 * @package Symfony
 *
 * @Route("/my_custom",
 *     name="###vendornametolower###.###repositorynametolower###.mycustom",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */
class CustomController extends AbstractController
{
    /** @var TwigEnvironment */
    private $twig;

    /**
     * CustomController constructor.
     * @param TwigEnvironment $twig
     */
    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __invoke()
    {
        $animals = [

            [
                'species' => 'dogs',
                'color'   => 'white'
            ],
            [
                'species' => 'birds',
                'color'   => 'black'
            ], [
                'species' => 'cats',
                'color'   => 'pink'
            ], [
                'species' => 'cows',
                'color'   => 'yellow'
            ],
        ];

        return new Response($this->twig->render(
            '###toplevelnamespacetwig###/my_custom_route.html.twig',
            ['animals' => $animals]
        ));
    }
}

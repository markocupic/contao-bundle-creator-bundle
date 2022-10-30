<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
<?php if ($this->useattributes) { ?>
use Symfony\Component\Routing\Annotation\Route;
<?php } else { ?>
use Symfony\Component\Routing\Annotation\Route;
<?php } ?>
use Twig\Environment as TwigEnvironment;

<?php if ($this->useattributes) { ?>
#[Route('/my_custom', name: '<?= $this->routeid; ?>_my_custom', defaults: ['_scope' => 'frontend', '_token_check' => true])]
<?php } else { ?>
/**
 * @Route("/my_custom", name="<?= $this->routeid; ?>_my_custom", defaults={"_scope" = "frontend", "_token_check" = true})
 */
<?php } ?>
class MyCustomController extends AbstractController
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(): Response
    {
        $animals = [
            [
                'species' => 'dogs',
                'color' => 'white',
            ],
            [
                'species' => 'birds',
                'color' => 'black',
            ], [
                'species' => 'cats',
                'color' => 'pink',
            ], [
                'species' => 'cows',
                'color' => 'yellow',
            ],
        ];

        return new Response($this->twig->render(
            '<?= $this->twignamespace; ?>/MyCustom/my_custom.html.twig',
            [
                'animals' => $animals,
            ]
        ));
    }
}

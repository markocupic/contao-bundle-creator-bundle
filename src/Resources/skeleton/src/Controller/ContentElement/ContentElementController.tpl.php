<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class <?= $this->contentelementclassname; ?><?= "\n"; ?>
 *
 * @ContentElement(<?= $this->contentelementclassname; ?>::TYPE, category="<?= $this->contentelementcategory; ?>", template="<?= $this->contentelementtemplate; ?>")
 */
class <?= $this->contentelementclassname; ?> extends AbstractContentElementController
{
    public const TYPE = '<?= $this->contentelementtype; ?>';

    /**
     * Generate the content element
     */
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $template->text = $model->text;

        return $template->getResponse();
    }
}

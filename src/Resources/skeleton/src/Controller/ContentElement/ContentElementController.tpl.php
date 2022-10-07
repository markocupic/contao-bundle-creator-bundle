<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
<?php if ($this->useattributes): ?>
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
<?php else: ?>
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
<?php endif; ?>
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

<?php if ($this->useattributes): ?>
#[AsContentElement(category: '<?= $this->contentelementcategory; ?>')]
<?php else: ?>
/**
 * @ContentElement(category="<?= $this->contentelementcategory; ?>")
 */
<?php endif; ?>
class <?= $this->contentelementclassname; ?> extends AbstractContentElementController
{
    public const TYPE = '<?= $this->contentelementtype; ?>';

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $template->text = $model->text;

        return $template->getResponse();
    }
}

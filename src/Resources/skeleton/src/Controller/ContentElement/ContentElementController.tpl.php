<?= "<?php\n" ?>

<?= $this->phpdoc ?>
declare(strict_types=1);

namespace <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class <?= $this->contentelementclassname ?><?= "\n" ?>
 *
 * @package <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Controller\ContentElement
 */
class <?= $this->contentelementclassname ?> extends AbstractContentElementController
{

    /**
     * @param Template $template
     * @param ContentModel $model
     * @param Request $request
     * @return Response|null
     */
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {

        $template->text = $model->text;

        return $template->getResponse();
    }
}

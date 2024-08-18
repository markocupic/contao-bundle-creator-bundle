<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\BundleMaker;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ContaoBundleCreator
{
    public function __construct(
        private readonly BundleMaker $bundleMaker,
        private readonly RequestStack $requestStack,
        private readonly string $projectDir,
    ) {
    }

    /**
     * Launch bundle creator.
     *
     * @throws \Exception
     */
    #[AsCallback(table: 'tl_contao_bundle_creator', target: 'config.onsubmit', priority: 100)]
    public function runCreator(DataContainer $dc): void
    {
        if ('' !== Input::get('id') && '' === Input::post('createBundle') && 'tl_contao_bundle_creator' === Input::post('FORM_SUBMIT') && 'auto' !== Input::post('SUBMIT_TYPE')) {
            if (null !== ($objModel = ContaoBundleCreatorModel::findByPk(Input::get('id')))) {
                $this->bundleMaker->run($objModel);
            }
        }
    }

    /**
     * Download extension as a zip file when clicking on the download button.
     */
    #[AsCallback(table: 'tl_contao_bundle_creator', target: 'config.onload', priority: 100)]
    public function downloadZipFile(DC_Table $dc): void
    {
        if ('' !== Input::get('id') && '' === Input::post('downloadBundle') && 'tl_contao_bundle_creator' === Input::post('FORM_SUBMIT') && 'auto' !== Input::post('SUBMIT_TYPE')) {
            $session = $this->requestStack->getCurrentRequest()->getSession();

            if ($session->has('CONTAO-BUNDLE-CREATOR.LAST-ZIP')) {
                $zipSrc = $session->get('CONTAO-BUNDLE-CREATOR.LAST-ZIP');
                $session->remove('CONTAO-BUNDLE-CREATOR.LAST-ZIP');

                $filepath = $this->projectDir.'/'.$zipSrc;
                $filename = basename($filepath);

                $response = new Response();
                $response->headers->set('Cache-Control', 'private');
                $response->headers->set('Content-type', 'application/zip');
                $response->headers->set('Content-disposition', 'attachment;filename="'.$filename.'"');
                $response->headers->set('Content-length', (string) filesize($filepath));

                // Send headers before outputting anything.
                $response->sendHeaders();
                $response->setContent((string) readfile($filepath));

                throw new ResponseException($response);
            }
        }
    }

    #[AsCallback(table: 'tl_contao_bundle_creator', target: 'edit.buttons', priority: 100)]
    public function buttonsCallback(array $arrButtons, DC_Table $dc): array
    {
        if ('edit' === Input::get('act')) {
            $arrButtons['createBundle'] = '<button type="submit" name="createBundle" id="createBundle" class="tl_submit createBundle" accesskey="x">'.$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['createBundleButton'].'</button>';

            $session = $this->requestStack->getCurrentRequest()->getSession();

            if ($session->has('CONTAO-BUNDLE-CREATOR.LAST-ZIP')) {
                $arrButtons['downloadBundle'] = '<button type="submit" name="downloadBundle" id="downloadBundle" class="tl_submit downloadBundle" accesskey="d" onclick="this.style.display = \'none\'">'.$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['downloadBundleButton'].'</button>';
            }
        }

        return $arrButtons;
    }

    #[AsCallback(table: 'tl_contao_bundle_creator', target: 'fields.composerlicense.options', priority: 100)]
    public function getLicenses(): array
    {
        $arrLicenses = [];

        if (isset($GLOBALS['contao_bundle_creator']['licenses']) && \is_array($GLOBALS['contao_bundle_creator']['licenses'])) {
            foreach ($GLOBALS['contao_bundle_creator']['licenses'] as $k => $v) {
                $arrLicenses[$k] = "$k   ($v)";
            }
        }

        return $arrLicenses;
    }
}

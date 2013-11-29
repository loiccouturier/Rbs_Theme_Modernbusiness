<?php
namespace Theme\Rbs\Modernbusiness\Setup;

use Zend\Json\Json;

/**
 * @name \Theme\Rbs\Modernbusiness\Setup
 */
class Install extends \Change\Plugins\InstallBase
{
	/**
	 * @param \Change\Plugins\Plugin $plugin
	 * @param \Change\Services\ApplicationServices $applicationServices
	 * @throws \Exception
	 */
	public function executeServices($plugin, $applicationServices)
	{
		$themeModel = $applicationServices->getModelManager()->getModelByName('Rbs_Theme_Theme');
		$query = $applicationServices->getDocumentManager()->getNewQuery($themeModel);
		$query->andPredicates($query->eq('name', 'Rbs_Modernbusiness'));
		$theme = $query->getFirstDocument();
		$themeManager = $applicationServices->getThemeManager();

		$transactionManager = $applicationServices->getTransactionManager();
		try
		{
			$transactionManager->begin();

			/* @var $theme \Rbs\Theme\Documents\Theme */
			if ($theme == null)
			{
				$theme = $applicationServices->getDocumentManager()->getNewDocumentInstanceByModel($themeModel);
			}
			$theme->setLabel('Modernbusiness');
			$theme->setName('Rbs_Modernbusiness');
			$theme->setActive(true);
			$theme->save();

			$themeManager->installPluginTemplates($plugin, $theme);
			$themeManager->installPluginAssets($plugin, $theme);
			$this->writeAssetic($theme, $themeManager);

			$pageTemplateModel = $applicationServices->getModelManager()->getModelByName('Rbs_Theme_PageTemplate');

			$query = $applicationServices->getDocumentManager()->getNewQuery($pageTemplateModel);
			$query->andPredicates($query->eq('label', 'Home'));
			$pageTemplate = $query->getFirstDocument();

			/* @var $pageTemplate \Rbs\Theme\Documents\PageTemplate */
			if ($pageTemplate == null)
			{
				$pageTemplate = $applicationServices->getDocumentManager()->getNewDocumentInstanceByModel($pageTemplateModel);
			}
			$pageTemplate->setTheme($theme);
			$pageTemplate->setLabel('Home');
			$html = file_get_contents(__DIR__ . '/Assets/home.twig');
			$pageTemplate->setHtml($html);
			$json = file_get_contents(__DIR__ . '/Assets/home.json');
			$pageTemplate->setEditableContent(Json::decode($json, Json::TYPE_ARRAY));
			$boHtml = file_get_contents(__DIR__ . '/Assets/home-bo.twig');
			$pageTemplate->setHtmlForBackoffice($boHtml);
			$pageTemplate->setActive(true);
			$pageTemplate->save();

			$pageTemplate = null;
			$query = $applicationServices->getDocumentManager()->getNewQuery($pageTemplateModel);
			$query->andPredicates($query->eq('label', 'Full Width'));
			$pageTemplate = $query->getFirstDocument();

			/* @var $pageTemplate \Rbs\Theme\Documents\PageTemplate */
			if ($pageTemplate == null)
			{
				$pageTemplate = $applicationServices->getDocumentManager()->getNewDocumentInstanceByModel($pageTemplateModel);
			}
			$pageTemplate->setTheme($theme);
			$pageTemplate->setLabel('Full Width');
			$html = file_get_contents(__DIR__ . '/Assets/fullwidth.twig');
			$pageTemplate->setHtml($html);
			$json = file_get_contents(__DIR__ . '/Assets/fullwidth.json');
			$pageTemplate->setEditableContent(Json::decode($json, Json::TYPE_ARRAY));
			$boHtml = file_get_contents(__DIR__ . '/Assets/fullwidth-bo.twig');
			$pageTemplate->setHtmlForBackoffice($boHtml);
			$pageTemplate->setActive(true);
			$pageTemplate->save();

			$transactionManager->commit();
		}
		catch (\Exception $e)
		{
			throw $transactionManager->rollBack($e);
		}
	}

	/**
	 * @param \Rbs\Theme\Documents\Theme $theme
	 * @param \Change\Presentation\Themes\ThemeManager $themeManager
	 */
	protected function writeAssetic($theme, $themeManager)
	{
		$configuration = $theme->getAssetConfiguration();
		$am = $themeManager->getAsseticManager($configuration);
		$writer = new \Assetic\AssetWriter($themeManager->getAssetRootPath());
		$writer->writeManagerAssets($am);
	}
}
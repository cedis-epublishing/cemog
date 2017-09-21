<?php
/**
 * @file plugins/generic/primaryNavigation/PrimaryNavigationPlugin.inc.php
 *
 * Copyright (c) 2017 FU Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PrimaryNavigationPlugin
 *
 */
import('lib.pkp.classes.plugins.GenericPlugin');
class CemPlugin extends GenericPlugin {
	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				HookRegistry::register('catalogentrysubmissionreviewform::initdata', array($this, 'metadataInitData'));
				HookRegistry::register ('TemplateManager::display', array(&$this, 'handleDisplayTemplate'));
				HookRegistry::register ('TemplateManager::include', array(&$this, 'handleIncludeTemplate'));
				HookRegistry::register('PluginRegistry::loadCategory', array($this, 'callbackLoadCategory'));				

				// Additional submission metadata form fields
				HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldsEdit'));
				HookRegistry::register('catalogentrysubmissionreviewform::initdata', array($this, 'metadataInitData'));
				HookRegistry::register('catalogentrysubmissionreviewform::readuservars', array($this, 'metadataFieldsReadUserVars'));
 				HookRegistry::register('catalogentrysubmissionreviewform::execute', array($this, 'metadataFieldsSave'));
 				HookRegistry::register('submissionsubmitstep3form::readuservars', array($this, 'metadataFieldsReadUserVars'));
 				HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'metadataFieldsSave'));

 				// Consider the new submission metadata form fields in MonographDAO for storage
 				HookRegistry::register('monographdao::getLocaleFieldNames', array($this, 'submissionGetFieldNames'));

				// Delete the Bookreader directory when deleting the ZIP-file
				HookRegistry::register('FileManager::deleteFile', array($this, 'handleDeleteFile'));

				
				}
			return true;
		}
		return false;
	}
	
	/**
	 * Handle templates that are called with the display hook.
	 * @param $hookName string
	 * @param $args array
	 */
	function handleDisplayTemplate($hookName, $args) {
		$request = $this->getRequest();
		$templateMgr =& $args[0];
		$template =& $args[1];
		
		switch ($template) {
			
			case 'frontend/pages/book.tpl':	
				$templateMgr->display($this->getTemplatePath() . 'bookModified.tpl', 'text/html', 'TemplateManager::display');
				return true;
			case 'frontend/pages/viewFile.tpl':	
				$templateMgr->display($this->getTemplatePath() . 'viewFileModified.tpl', 'text/html', 'TemplateManager::display');
				return true;	
			case 'frontend/pages/index.tpl':	
				$templateMgr->display($this->getTemplatePath() . 'indexModified.tpl', 'text/html', 'TemplateManager::display');
				return true;				
		}
		return false;
	}
	
	function handleIncludeTemplate($hookName, $args) {
		$templateMgr =& $args[0];
		$params =& $args[1];
		if (!isset($params['smarty_include_tpl_file'])) {
			return false;
		}
		switch ($params['smarty_include_tpl_file']) {
			case 'frontend/components/primaryNavMenu.tpl':
				$templateMgr->display($this->getTemplatePath() . 
				'primaryNavMenuModified.tpl', 'text/html', 'TemplateManager::include');
				return true;
			case 'frontend/components/header.tpl':
				$templateMgr->display($this->getTemplatePath() . 
				'headerModified.tpl', 'text/html', 'TemplateManager::include');
				return true;				
		}
		return false;
	}
	
	/**
	 * Register as a viewableFiles plugin, even though this is a generic plugin.
	 * This will allow the plugin to behave as a viewableFiles plugin
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	*/
	function callbackLoadCategory($hookName, $args) {
		$category =& $args[0];
		$plugins =& $args[1];
		switch ($category) {
			case 'viewableFiles':
				$this->import('BookReaderPlugin');
				$viewableFilesPlugin = new BookReaderPlugin($this->getName());
				$plugins[$viewableFilesPlugin->getSeq()][$viewableFilesPlugin->getPluginPath()] =& $viewableFilesPlugin;
				break;
		}
		return false;
	}
	
	/**
	 * Insert new submission metadata form fields (print-on-demand and order ebook link)
	 */
	function metadataFieldsEdit($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
		$output .= $smarty->fetch($this->getTemplatePath() . 'additionalSubmissionMetadataFormFields.tpl');
		return false;
	}	
	
	/**
	 * Init new submission metadata
	 */
	function metadataInitData($hookName, $params) {
		$form =& $params[0];
		$submission =& $form->getSubmission();
		$cemogPrintOnDemandUrl = $submission->getData('cemogPrintOnDemandUrl');
		$cemogBuyBookUrl = $submission->getData('cemogOrderEbookUrl');
		$cemogBookReviews = $submission->getData('cemogBookReviews');
		$cemogBookPressMaterial = $submission->getData('cemogBookPressMaterial');
		$form->setData('cemogPrintOnDemandUrl', $cemogPrintOnDemandUrl);
		$form->setData('cemogOrderEbookUrl', $cemogBuyBookUrl);
		$form->setData('cemogBookReviews', $cemogBookReviews);
		$form->setData('cemogBookPressMaterial', $cemogBookPressMaterial);
		return false;
	}


	/**
	 * Read new submission metadata fields from the form
	 */
	function metadataFieldsReadUserVars($hookName, $params) {
		$userVars =& $params[1];
		$userVars[] = 'cemogPrintOnDemandUrl';
		$userVars[] = 'cemogOrderEbookUrl';
		$userVars[] = 'cemogBookReviews';
		$userVars[] = 'cemogBookPressMaterial';
		return false;
	}

	/**
	 * Save new submission metadata form fields (print-on-demand and buy link)
	 */
	function metadataFieldsSave($hookName, $params) {
		$submissionForm =& $params[0];
		$submission =& $params[1];
		$cemogPrintOnDemandUrl = $submissionForm->getData('cemogPrintOnDemandUrl');
		$cemogBuyBookUrl = $submissionForm->getData('cemogOrderEbookUrl');
		$cemogBookReviews = $submissionForm->getData('cemogBookReviews');
		$cemogBookPressMaterial = $submissionForm->getData('cemogBookPressMaterial');
		$submission->setData('cemogPrintOnDemandUrl', $cemogPrintOnDemandUrl);
		$submission->setData('cemogOrderEbookUrl', $cemogBuyBookUrl);
		$submission->setData('cemogBookReviews', $cemogBookReviews);
		$submission->setData('cemogBookPressMaterial', $cemogBookPressMaterial);
		return false;
	}

	/**
	 * Add new metadata to the submission
	 */
	function submissionGetFieldNames($hookName, $params) {
		$fields =& $params[1];
		$fields[] = 'cemogPrintOnDemandUrl';
		$fields[] = 'cemogOrderEbookUrl';
		$fields[] = 'cemogBookReviews';
		$fields[] = 'cemogBookPressMaterial';
		return false;
	}
	
	/**
	 * Hook callback: Handle requests.
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function handleDeleteFile($hookName, $args) {
		
$myfile = 'test.txt';
$newContentCF5344 = print_r($args, true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n handleDeleteFile,args: " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );		
		
		$filePath =& $args[0];
		$path_parts = pathinfo($filePath);
		$fileMimeType = mime_content_type($filePath);
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		if ($fileManager->getDocumentType($fileMimeType) == DOCUMENT_TYPE_ZIP) {
			$zipDirName = $path_parts['dirname'].'/'.$path_parts['filename'];
			// remove directory
			$fileManager->rmtree($zipDirName);
		}
		return false;
	}	
	
	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.cem.displayName');
	}
	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.cem.description');
	}
	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}
}
?>

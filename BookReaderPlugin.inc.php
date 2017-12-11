<?php

/**
 * @file plugins/generic/cemog/BookReaderPlugin.inc.php
 *
 * Copyright (c) 2017 FU Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookreaderPlugin
 * @ingroup plugins_generic_cemog
 *
 * @brief Class for BookReaderPlugin plugin
 */

import('classes.plugins.ViewableFilePlugin');

class BookReaderPlugin extends ViewableFilePlugin {
	var $parentPluginName;

	/*
	 * Constructor
	 */
	function BookReaderPlugin($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.cemog.bookreader.name');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.cemog.bookreader.description');
	}

	/**
	 * @copydoc Plugin::getHideManagement()
	 */
	function getHideManagement() {
		return true;
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				// Bookreader images download/view
				HookRegistry::register('CatalogBookHandler::download', array($this, 'downloadCallback'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the CeMoG plugin
	 * @return object
	 */
	function &getCeMoGPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * @copydoc Plugin::getPluginPath()
	 */
	function getPluginPath() {
		$plugin =& $this->getCeMoGPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * @copydoc Plugin::getTemplatePath()
	 */
	function getTemplatePath() {
		$plugin =& $this->getCeMoGPlugin();
		return $plugin->getTemplatePath();
	}

	/**
	 * @copydoc LazyLoadPlugin::getTemplatePath()
	 */
	function getEnabled() {
		return true;
	}

	/**
	 * Callback for download function
	 * @param $hookName string
	 * @param $params array
	 * @return boolean
	 */
	function downloadCallback($hookName, $params) {
		$publishedMonograph =& $params[1];
		$publicationFormat =& $params[2];
		$submissionFile =& $params[3];


		$fileId = $submissionFile->getFileId();
		$revision = $submissionFile->getRevision();
		
		$request = $this->getRequest();
		$isImg = $request->getUserVar('img');
		if ($this->canHandle($publishedMonograph, $publicationFormat, $submissionFile) && $isImg) {		
			import('plugins.generic.cemog.classes.ImgFileManager');
			$monographFileManager = new ImgFileManager($publishedMonograph->getContextId(), $publishedMonograph->getId());
			$monographFileManager->downloadImgFile($fileId, $revision);
			exit();
		}
		// Return to regular handling
		return false;
	}	

	/**
	 * @copydoc ViewableFilePlugin::canHandle
	 */
	function canHandle($publishedMonograph, $publicationFormat, $submissionFile) {
		return ($submissionFile->getFileType() == 'application/zip');
	}

	/**
	 * @copydoc ViewableFilePlugin::displaySubmissionFile
	 */
	function displaySubmissionFile($publishedMonograph, $publicationFormat, $submissionFile) {
		$request = $this->getRequest();
		$router = $request->getRouter();
		$dispatcher = $request->getDispatcher();
		
		// unzip the file, if not yet
		$filePath = $submissionFile->getFilePath(); //  filePath: /data/omp/omp-test-files/presses/1//monographs/4/submission/proof/4-3-44-1-10-20141112.zip
		$path_parts = pathinfo($filePath);
		$zipDirName = $path_parts['dirname'].'/'.$path_parts['filename'];
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		// is it already extracted -- does the directory exist
		if (!$fileManager->fileExists($zipDirName, 'dir')) {
			// make directory
			$fileManager->mkdir($zipDirName);
			// extract the zip
			// temporär ersetzt durch den Code unten
			$zip = new ZipArchive();
  			$res = $zip->open($filePath);
  			if ($res === TRUE) {
  				$zip->extractTo($zipDirName);
  				$zip->close();
  			}			
		}
		
		// count the file in the directory
		$fileCount = count(scandir($zipDirName));
		
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'pluginUrl' => $request->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath(),
			'fileCount' => $fileCount-2,
			'fileName' => $submissionFile->getOriginalFileName()
		));		
		return parent::displaySubmissionFile($publishedMonograph, $publicationFormat, $submissionFile);
	}	

	/**
	 * Get the plugin base URL.
	 * @param $request PKPRequest
	 * @return string
	 */
	private function _getPluginUrl($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath();
	}
	
}

?>

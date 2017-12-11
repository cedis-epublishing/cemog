<?php
/**
 * @file plugins/generic/cemog/CeMoGPlugin.inc.php
 *
 * Copyright (c) 2017 FU Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CeMoGPlugin
 *
 */
import('lib.pkp.classes.plugins.GenericPlugin');
class CeMoGPlugin extends GenericPlugin {
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

				HookRegistry::register('publicprofileform::Constructor', array($this, 'handleFormConstructor'));
				HookRegistry::register('contactform::Constructor', array($this, 'handleFormConstructor'));
				HookRegistry::register('registrationform::Constructor', array($this, 'handleFormConstructor'));

				HookRegistry::register('registrationform::validate', array($this, 'handleRegistrationFormValidation'));

				// Additional registration form fields
				HookRegistry::register('registrationform::readuservars', array($this, 'registrationFormReadUserVars'));
 				HookRegistry::register('registrationform::execute', array($this, 'registrationFormSave'));
				// Consider the new registration form fields in UserDAO for storage
				HookRegistry::register('userdao::getAdditionalFieldNames', array($this, 'registrationGetFieldNames'));
		
			}
			return true;
		}
		return false;
	}
	
	

	/**
	 * Read new registration fields (newsletter, terms of use) from the form
	 */
	function registrationFormReadUserVars($hookName, $params) {
		$userVars =& $params[1];
		$userVars[] = 'cemogNewsletter';
		$userVars[] = 'cemogTermsOfUse';
		$userVars[] = 'sendPassword';		
		return false;
	}

	/**
	 * Save new registration form fields (newsletter, terms of use), table user_settings
	 */
	function registrationFormSave($hookName, $params) {
		$registrationForm =& $params[0];
		$user =& $params[1];
		$cemogNewsletter = $registrationForm->getData('cemogNewsletter');
		$cemogTermsOfUse = $registrationForm->getData('cemogTermsOfUse');
		$user->setData('cemogNewsletter', $cemogNewsletter);
		$user->setData('cemogTermsOfUse', $cemogTermsOfUse);
		return false;
	}

	/**
	 * Add new registration form fields to the user
	 */
	function registrationGetFieldNames($hookName, $params) {
		$fields =& $params[1];
		$fields[] = 'cemogNewsletter';
		$fields[] = 'cemogTermsOfUse';
		return false;
	}	
	
	function handleRegistrationFormValidation($hookName, $args) {

		$request = $this->getRequest();
		$context = $request->getContext();
		
		$registrationForm =& $args[0];
		$isValid =& $args[1];

		// remove country error message from error array
		$errors =& $registrationForm->_errors;
		foreach ($errors as $key => $error) {
			if ($error->getField()=="country") {
				unset($errors[$key]); 
			}
		}

		// skip validation test for country
		$isValid = true;
		foreach ($registrationForm->_checks as $check) {
			if ($check->getField()!="country") {
				$isValid = $isValid & $check->isValid();
			}	
		}
		if ($isValid) {
			$username = $registrationForm->_data['username'];
			$password = $registrationForm->_data['password'];
			$fullName = $registrationForm->_data['firstName']. " " . $registrationForm->_data['lastName'];
			$email = $registrationForm->_data['email'];
			$sendPassword = $registrationForm->_data['sendPassword'];

			if ($username && $password && $sendPassword) {
				// Send welcome email to user
				import('lib.pkp.classes.mail.MailTemplate');		
				$mail = new MailTemplate('USER_REGISTER');
				$mail->setReplyTo($context->getSetting('contactEmail'), $context->getSetting('contactName'));
				$mail->assignParams(array('username' => $username, 'password' => $password, 'userFullName' => $fullName));
				$mail->addRecipient($email, $fullName);
				$mail->send();
			}	
		}		
		return true;
	}

	function handleFormConstructor($hookName, $args) {
		$request = $this->getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$form =& $args[0];

		if (in_array($hookName,array('publicprofileform::Constructor','contactform::Constructor'))){		
			$user = Request::getUser();
			$roleDao = DAORegistry::getDAO('RoleDAO');
			$userRoles = $roleDao->getByUserId($user->getId(),$request->getContext()->getId());
			$uploadAllowed = false;
			foreach ($userRoles as $userRole) {
				if (in_array($userRole->getId(), array(ROLE_ID_SITE_ADMIN,ROLE_ID_MANAGER,ROLE_ID_SUB_EDITOR))) {
					$uploadAllowed = true;
				}
			}
			$templateMgr->assign('uploadAllowed', $uploadAllowed);
		}
			
		switch ($hookName) {
			case 'publicprofileform::Constructor':				
				$form->setTemplate($this->getTemplatePath() . 'publicProfileFormModified.tpl');
				return true;
			case 'contactform::Constructor':				
				if ($form->_template=='user/contactForm.tpl') {
					$form->setTemplate($this->getTemplatePath() . 'contactFormModified.tpl');
					return true;
				}
				return false;
			case 'registrationform::Constructor':				
				$form->setTemplate($this->getTemplatePath() . 'userRegisterModified.tpl');
				$templateMgr->assign('templatePath', $this->getTemplatePath());
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
				$templateMgr->assign('pluginPath',$request->getBaseUrl());
				$templateMgr->display($this->getTemplatePath() . 'indexModified.tpl', 'text/html', 'TemplateManager::display');
				return true;	
			case 'frontend/pages/userLogin.tpl':
				$templateMgr->assign('loginMessage', 'plugins.generic.cemog.login.loginMessage');
				break;				
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
				$pageTitle = $params['smarty_include_vars']['pageTitle'];	
				if ($pageTitle=="navigation.catalog") {
					$templateMgr->assign('pageTitle','plugins.generic.cemog.primnav.catalog');		
				} elseif($pageTitle=="announcement.announcements") {
					$templateMgr->assign('pageTitle','plugins.generic.cemog.submission.primnav.announcements');					
				} elseif($pageTitle=="about.aboutContext") {
					$templateMgr->assign('pageTitle','plugins.generic.cemog.primnav.about');					
				} else {
					$templateMgr->assign('pageTitleTranslated',$params['smarty_include_vars']['pageTitleTranslated']);
					$templateMgr->assign('pageTitle',$params['smarty_include_vars']['pageTitle']);					
				}
				$templateMgr->display($this->getTemplatePath() .'headerModified.tpl', 'text/html', 'TemplateManager::include');
				return true;
			case 'frontend/components/breadcrumbs.tpl':
				$templateMgr->display($this->getTemplatePath() .'emptyTemplate.tpl', 'text/html', 'TemplateManager::include');
				return true;
			//case 'frontend/components/breadcrumbs_catalog.tpl':
				//$templateMgr->display($this->getTemplatePath() .'emptyTemplate.tpl', 'text/html', 'TemplateManager::include');
				//return true;
			case 'frontend/objects/monograph_summary.tpl':
				$templateMgr->display($this->getTemplatePath() .'monograph_summaryModified.tpl', 'text/html', 'TemplateManager::include');
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
		return __('plugins.generic.cemog.displayName');
	}
	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.cemog.description');
	}
	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}
}
?>

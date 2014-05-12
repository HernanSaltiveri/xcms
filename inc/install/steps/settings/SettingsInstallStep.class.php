<?php
/**
 *  \details &copy; 2011  Open Ximdex Evolution SL [http://www.ximdex.org]
 *
 *  Ximdex a Semantic Content Management System (CMS)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  See the Affero GNU General Public License for more details.
 *  You should have received a copy of the Affero GNU General Public License
 *  version 3 along with Ximdex (see LICENSE file).
 *
 *  If not, visit http://gnu.org/licenses/agpl-3.0.html.
 *
 *  @author Ximdex DevTeam <dev@ximdex.com>
 *  @version $Revision$
 */

require_once(XIMDEX_ROOT_PATH . '/inc/install/steps/generic/GenericInstallStep.class.php');
require_once(XIMDEX_ROOT_PATH . '/inc/install/managers/InstallModulesManager.class.php');


class SettingsInstallStep extends GenericInstallStep {

	/**
	 * Main function. Show the step	 
	 */
	public function index(){
		$this->addJs("SettingController.js");

		$values = array("go_method"=>"initializeSettings");
		$this->render($values);
		
	}


	public function initializeSettings(){
		$password = $this->request->getParam("pass");
		$language = $this->request->getParam("language");
		$anonymousInformation = $this->request->getParam("anonymous_information");
		if($anonymousInformation)
			Config::update("ActionStats","1");
		$this->installManager->setSingleParam("##XIMDEX_LOCALE##", $language);
		Config::update("AppRoot", XIMDEX_ROOT_PATH);
		$urlRoot = substr(str_replace("index.php", "", $_SERVER['HTTP_REFERER']),0,-1) ;
		Config::update("UrlRoot", $urlRoot);
		Config::update("locale", $language);
		$this->installManager->setLocale($language);
		$this->installManager->setXid();
		$this->installManager->insertXimdexUser($password);
		$this->loadNextAction();
		$result["success"] = true;
		$this->sendJSON($result);
		
	}


}

?>
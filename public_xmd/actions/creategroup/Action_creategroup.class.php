<?php

/**
 *  \details &copy; 2019 Open Ximdex Evolution SL [http://www.ximdex.org]
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

use Ximdex\Models\Node;
use Ximdex\Models\NodeType;
use Ximdex\MVC\ActionAbstract;

class Action_creategroup extends ActionAbstract
{
    /**
     * Main method: shows initial form
     */
    public function index()
    {
		$idNode = $this->request->getParam('nodeid');
		$folder = new Node($idNode);
		$values = array(
			'id_node' => $idNode,
		    'nodeTypeID' => $folder->nodeType->getID(),
		    'node_Type' => $folder->nodeType->GetName(),
			'go_method' => 'creategroup');
		$this->render($values, null, 'default-3.0.tpl');
    }
    
    public function creategroup()
    {
    	$idNode = $this->request->getParam('id_node');
    	$name = $this->request->getParam('name');
		$nodeType = new NodeType();
		$nodeType->SetByName('Group');
	    $grupo = new Node();
		$result = $grupo->createNode($name, $idNode, $nodeType->get('IdNodeType'), null);
		if ($result) {
			$grupo->messages->add(_('Group has been successfully inserted'), MSG_TYPE_NOTICE);
		}
		$values = array('messages' => $grupo->messages->messages,"parentID" => $grupo->GetParent());
		$this->sendJSON($values);
    }
}

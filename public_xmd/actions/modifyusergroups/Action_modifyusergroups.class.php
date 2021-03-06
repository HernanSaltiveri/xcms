<?php

/**
 *  \details &copy; 2018 Open Ximdex Evolution SL [http://www.ximdex.org]
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

use Ximdex\Models\Group;
use Ximdex\Models\Role;
use Ximdex\Models\User;
use Ximdex\MVC\ActionAbstract;

/**
 * @deprecated
 */
class Action_modifyusergroups extends ActionAbstract
{
    /**
     * Main method: shows initial form
     */
    public function index()
    {
        $idNode = $this->request->getParam('nodeid');
        $this->addJs('/actions/modifyusergroups/resources/js/helper.js');
        $user = new User($idNode);
        $group = new Group();
        $generalRole = $user->GetRoleOnGroup(Group::getGeneralGroup());
        $rol = new Role();
        $roles = $rol->find('IdRole, Name');
        $userGroups = $user->GetGroupList();
        $excludedGroups = '';
        if (is_array($userGroups)) {
            $excludedGroups = implode(', ', $userGroups);
        }
        if (empty($excludedGroups)) {
            $filteredGroups = $group->find('IdGroup, Name');
        } else {
            $filteredGroups = $group->find('IdGroup, Name', 'NOT IdGroup IN (%s)',
                array($excludedGroups), MULTI, false);
        }
        $userGroupsWithRole = array();
        if (is_array($userGroups)) {
            $index = 0;
            foreach ($userGroups as $value) {
                if (! is_array($value) || ! array_key_exists('IdGroup', $value) || $value['IdGroup'] != Group::getGeneralGroup()) {
                    $userGroupsWithRole[$index]['IdGroup'] = $value;
                    $tmpGroup = new Group($value);
                    $userGroupsWithRole[$index]['Name'] = $tmpGroup->get('Name');
                    $userGroupsWithRole[$index]['IdRole'] = $user->getRoleOnGroup($value);
                    $userGroupsWithRole[$index]['dirty'] = false;
                    $index ++;
                }
            }
        }
        if (! is_array($filteredGroups)) {
            $filteredGroups = array();
        }
        $values = array(
            'id_node' => $idNode,
            'user_name' => $user->get('Name'),
            'general_role' => $generalRole,
            'all_roles' => json_encode($roles),
            'filtered_groups' => json_encode($filteredGroups),
            'nodeTypeID' => $user->nodeType->getID(),
            'node_Type' => $user->nodeType->GetName(),
            'user_groups_with_role' => json_encode($userGroupsWithRole));
        $this->render($values, null, 'default-3.0.tpl');
    }

    public function suscribeGroupUser()
    {
        $newrole = $this->request->getParam('newrole');
        $newgroup = $this->request->getParam('newgroup');
        $idUser = $this->request->getParam('nodeid');
        $group = new Group($newgroup);
        $group->addUserWithRole($idUser, $newrole);
        $values = array(
            'result' => 'OK',
            'message' => 'The association was created successfully'
        );
        $this->sendJSON($values);
    }

    public function updateGroupUser()
    {
        $iduser = $this->request->getParam('nodeid');
        $idGroup = $this->request->getParam('group');
        $idRole = $this->request->getParam('role');
        $group = new Group();
        $group->SetID($idGroup);
        $userRoles = $group->getUserRoleInfo();
        $exist = false;
        foreach ($userRoles as $u) {
            if ($u['IdUser'] == $iduser && $u['IdRole'] == $idRole) {
                $exist = true;
                break;
            }
        }
        if (! $exist) {
            $group->changeUserRole($iduser, $idRole);
            $values = array(
                'result' => 'OK',
                'message' => 'The association has been successfully updated'
            );
        } else {
            $values = array(
                'result' => 'FAIL'
            );
        }
        $this->sendJSON($values);
    }

    public function deleteGroupUser()
    {
        $group = $this->request->getParam('group');
        $iduser = $this->request->getParam('nodeid');
        $group = new Group($group);
        $group->deleteUser($iduser);
        $values = array(
            'result' => 'OK',
            'message' => 'The association has been successfully deleted'
        );
        $this->sendJSON($values);
    }
}

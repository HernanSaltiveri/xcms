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
 * @author Ximdex DevTeam <dev@ximdex.com>
 * @version $Revision$
 */

namespace Ximdex\Models\ORM;

use Ximdex\Data\GenericData;

class NodesToPublishOrm extends GenericData
{
    public $_idField = 'Id';
    
    public $_table = 'NodesToPublish';
    
    public $_metaData = array(
        'Id' => array('type' => "int(10)", 'not_null' => 'true', 'auto_increment' => 'true', 'primary_key' => true),
        'IdNode' => array('type' => "int(11)", 'not_null' => 'true'),
        'IdNodeGenerator' => array('type' => "int(11)", 'not_null' => 'true'),
        'Version' => array('type' => "int(12)", 'not_null' => 'false'),
        'Subversion' => array('type' => "int(12)", 'not_null' => 'false'),
        'DateUp' => array('type' => "int(14)", 'not_null' => 'true'),
        'DateDown' => array('type' => "int(14)", 'not_null' => 'false'),
        'State' => array('type' => "tinyint(1)", 'not_null' => 'true'),
        'UserId' => array('type' => "int(12)", 'not_null' => 'false'),
        'ForcePublication' => array('type' => "tinyint(1)", 'not_null' => 'true'),
        'DeepLevel' => array('type' => "int(12)", 'not_null' => 'false'),
        'UseCache' => array('type' => "tinyint(1)", 'not_null' => 'true'),
    );
    
    public $_uniqueConstraints = array();
    
    public $_indexes = array('Id');
    
    public $Id;
    
    public $IdNode;
    
    public $IdNodeGenerator;
    
    public $Version;
    
    public $Subversion;
    
    public $DateUp;
    
    public $DateDown;
    
    public $State; // 0: Pending, 1: Locked, 2: Processed
    
    public $UserId;
    
    public $ForcePublication = false;
    
    public $DeepLevel;
    
    public $UseCache = true;
}

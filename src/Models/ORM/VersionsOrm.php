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

class VersionsOrm extends GenericData
{
    public $_idField = 'IdVersion';
    
    public $_table = 'Versions';
    
    public $_metaData = array(
        'IdVersion' => array('type' => 'int(12)', 'not_null' => 'true', 'auto_increment' => 'true', 'primary_key' => true),
        'IdNode' => array('type' => 'int(12)', 'not_null' => 'true'),
        'Version' => array('type' => 'int(12)', 'not_null' => 'true'),
        'SubVersion' => array('type' => 'int(12)', 'not_null' => 'true'),
        'File' => array('type' => 'varchar(255)', 'not_null' => 'false'),
        'IdUser' => array('type' => 'int(12)', 'not_null' => 'false'),
        'Date' => array('type' => 'int(14)', 'not_null' => 'false'),
        'Comment' => array('type' => 'text', 'not_null' => 'false'),
        'IdSync' => array('type' => 'int(12)', 'not_null' => 'false'),
    );
    
    public $_uniqueConstraints = array();
    
    public $_indexes = array('IdVersion');
    
    public $IdVersion;
    
    public $IdNode;
    
    public $Version = 0;
    
    public $SubVersion = 0;
    
    public $File = null;
    
    public $IdUser = null;
    
    public $Date = null;
    
    public $Comment = null;
    
    public $IdSync = null;
}

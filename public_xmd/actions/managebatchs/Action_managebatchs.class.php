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

use Ximdex\Logger;
use Ximdex\Models\User;
use Ximdex\MVC\ActionAbstract;
use Ximdex\Runtime\App;
use Ximdex\Utils\Date;
use Ximdex\Utils\FilterParameters;
use Ximdex\Utils\Serializer;
use Ximdex\Runtime\Session;
use Ximdex\Models\Node;
use Ximdex\Models\Batch;
use Ximdex\Models\Server;
use Ximdex\Sync\BatchManager;
use Ximdex\Models\PortalFrames;

class Action_managebatchs extends ActionAbstract
{
    private $params = array();
    
    /**
     * Main method: shows initial form
     */
    public function index()
    {
        $acceso = true;
        $idNode = $this->request->getParam('nodeid');
        $node = new Node($idNode);
        
        // Initializing variables
        $userID = Session::get('userID');
        $user = new User();
        $user->SetID($userID);
        if (!$user->HasPermission('view_publication_resume')) {
            $acceso = false;
            $errorMsg = 'You have not access to this report. Consult an administrator.';
        } else {
            $errorMsg = '';
        }
        $jsFiles = array(
            App::getUrl('/actions/managebatchs/resources/js/index.js'),
            App::getUrl('/assets/js/ximtimer.js')
        );
        $this->addJs('/actions/managebatchs/resources/js/managebatchs.js');
        $cssFiles = array(
            App::getUrl('/actions/managebatchs/resources/css/index.css')
        );
        $arrValores = array(
            'acceso' => $acceso,
            'errorBox' => $errorMsg,
            'js_files' => $jsFiles,
            'node_Type' => $node->nodeType->GetName(),
            'css_files' => $cssFiles
        );
        $this->render($arrValores, null, 'default-3.0.tpl');
    }

    private function filterParams()
    {
        $this->params['idNode'] = FilterParameters::filterInteger($this->request->getParam('nodeid'));
        $this->params['idBatch'] = FilterParameters::filterInteger($this->request->getParam('idBatch'));
        $this->params['dateFrom'] = FilterParameters::filterInteger($this->request->getParam('dateFrom'));
        $this->params['dateTo'] = FilterParameters::filterInteger($this->request->getParam('dateTo'));
        $this->params['finished'] = FilterParameters::filterBool($this->request->getParam('finished'));
        $this->params['searchText'] = FilterParameters::filterText($this->request->getParam('searchText'));
    }

    /**
     * Return a JSON code with a list of portal frames with its servers
     */
    public function getFrameList()
    {
        $this->filterParams();
        $report = [];
        try {
            $order = 1;
            $portals = PortalFrames::getByState(PortalFrames::STATUS_ACTIVE);
            foreach ($portals as $portal) {
                $report[] = self::portalInfo($portal, $order);
            }
            $portals = PortalFrames::getByState(PortalFrames::STATUS_CREATED);
            foreach ($portals as $portal) {
                $report[] = self::portalInfo($portal, $order);
            }
            $portals = PortalFrames::getByState(PortalFrames::STATUS_ENDED, 3600);
            foreach ($portals as $portal) {
                $report[] = self::portalInfo($portal, $order);
            }
        } catch (Exception $e) {
            $this->sendJSON(['error' => $e->getMessage()]);
        }
        $this->sendJSON($report);
    }

    public function stopBatch()
    {
        $success = false;
        if (!$_POST['frm_deactivate_batch'] !== 'yes') {
            if (!$this->doDeactivateBatch($_POST['frm_id_batch'])) {
                $errorMsg = 'An error occurred while deactivate batch.';
            } else {
                $success = true;
                if ($_POST['frm_id_batch'] == 'all') {
                    $errorMsg = 'All batches have been deactivated.';
                } else {
                    $errorMsg = 'Batch #' . $_POST['frm_id_batch'] . ' has been deactivated.';
                }
            }
            Logger::info("PUBLISH results: $errorMsg");
        }
        $json = Serializer::encode(SZR_JSON, array('success' => $success));
        $this->render(array('result' => $json), null, 'only_template.tpl');
    }

    public function startBatch()
    {
        if (!$_POST['frm_activate_batch'] !== 'yes') {
            if (!$this->doActivateBatch($_POST['frm_id_batch'])) {
                Logger::error('An error occurred while activating batch ' . $_POST['frm_id_batch']);
            } else {
                if ($_POST['frm_id_batch'] == 'all') {
                    Logger::info('All batches have been activated');
                } else {
                    Logger::info('Batch #' . $_POST['frm_id_batch'] . ' has been activated');
                }
            }
        }
        $json = Serializer::encode(SZR_JSON, array('success' => true));
        $this->render(array('result' => $json), null, 'only_template.tpl');
    }

    public function changeBatchPriority()
    {
        $mode = 'up';
        if (isset($_POST['frm_increase']) && $_POST['frm_increase'] == 'yes') {
            Logger::info('PUBLISH pre doPrioritizeBatch');
        } elseif (isset($_POST['frm_decrease']) && $_POST['frm_decrease'] == 'yes') {
            Logger::info('PUBLISH pre doUnprioritizeBatch');
            $mode = 'down';
        }
        if (!$this->doPrioritizeBatch($_POST['frm_id_batch'], $mode)) {
            Logger::error("An error occurred while changing batch priority ($mode)");
        } else {
            Logger::info('Batch #' . $_POST['frm_id_batch'] . " priority has been changed ($mode)");
        }
        $json = Serializer::encode(SZR_JSON, array('success' => true));
        $this->render(array('result' => $json), null, 'only_template.tpl');
    }

    private function doActivateBatch($idBatch)
    {
        if ($idBatch != 'all') {
            $batchObj = new Batch();
            return $batchObj->setBatchPlayingOrUnplaying($idBatch, 1);
        } else {
            $batchManagerObj = new BatchManager();
            return $batchManagerObj->setAllBatchsPlayingOrUnplaying(1);
        }
    }

    private function doDeactivateBatch($idBatch)
    {
        if ($idBatch !== 'all') {
            $idBatch = (int) $idBatch;
            $batchObj = new Batch();
            return $batchObj->setBatchPlayingOrUnplaying($idBatch, 0);
        } else {
            $batchManagerObj = new BatchManager();
            return $batchManagerObj->setAllBatchsPlayingOrUnplaying(0);
        }
    }

    private function doPrioritizeBatch($idBatch, $mode = 'up')
    {
        $batch = new Batch();
        $hasChanged = $batch->prioritizeBatch($idBatch, $mode);
        return $hasChanged;
    }
    
    /**
     * Return a list of portal information including a list of servers affected
     * 
     * @param PortalFrames $portal
     * @param int $order
     * @return array
     */
    private static function portalInfo(PortalFrames $portal, int & $order) : array
    {
        $node = new Node($portal->get('IdNodeGenerator'));
        
        // User name
        if (!$portal->get('CreatedBy')) {
            $userName = 'Unknown';
        } else {
            $user = new User($portal->get('CreatedBy'));
            $userName = $user->getRealName() . ' (' . $user->getLogin() . ')';
        }
        
        // Servers stats
        $servers = [];
        $total = $pending = $active = $delayed = $success = $fatal = $soft = $stopped = 0;
        foreach ($portal->getServers() as $id => $name) {
            $server = new Server($id);
            $stats = $server->stats($portal->get('id'));
            $servers[] = [
                'id' => $id, 
                'name' => $name,
                'total' => $stats['total'],
                'active' => $stats['active'],
                'delayed' => $stats['delayed'],
                'pending' => $stats['pending'],
                'success' => $stats['success'],
                'fatal' => $stats['fatal'],
                'soft' => $stats['soft'],
                'stopped' => $stats['stopped'],
                'activeForPumping' => (int) $server->get('ActiveForPumping'),
                'delayedTime' => Date::formatTime($server->get('DelayTimeToEnableForPumping')),
                'enabled' => (int) $server->get('Enabled')
            ];
            $total += $stats['total'];
            $pending += $stats['pending'];
            $active += $stats['active'];
            $delayed += $stats['delayed'];
            $success += $stats['success'];
            $fatal += $stats['fatal'];
            $soft += $stats['soft'];
            $stopped += $stats['stopped'];
        }
        
        // Portal frames information
        $report = [
            'idPortal' => (int) $portal->get('id'),
            'idNodeGenerator' => (int) $node->GetID(),
            'nodeName' => $node->GetNodeName(),
            'userName' => $userName,
            'version' => (int) $portal->get('Version'),
            'creationTime' => Date::formatTime($portal->get('CreationTime')),
            'type' => $portal->get('PublishingType'),
            'scheduledTime' => Date::formatTime($portal->get('ScheduledTime')),
            'statusTime' => Date::formatTime($portal->get('StatusTime')),
            'startTime' => Date::formatTime($portal->get('StartTime')),
            'endTime' => Date::formatTime($portal->get('EndTime')),
            'total' => $total,
            'active' => $active,
            'delayed' => $delayed,
            'pending' => $pending,
            'success' => $success,
            'fatal' => $fatal,
            'soft' => $soft,
            'stopped' => $stopped,
            'order' => (int) $order++
        ];
        $report['servers'] = $servers;
        
        // Batchs list
        $report['batchs'] = $portal->getBatchs();
        $report['totalBatchs'] = count($report['batchs']);
        return $report;
    }
}

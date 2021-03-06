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

namespace Ximdex\Sync;

use Ximdex\Models\Batch;
use Ximdex\Models\ChannelFrame;
use Ximdex\Models\NodeFrame;
use Ximdex\Models\Server;
use Ximdex\Models\ServerFrame;
use Ximdex\Models\Node;
use Ximdex\Models\User;
use Ximdex\Logger;
use Ximdex\Runtime\Session;
use Ximdex\Models\PortalFrames;
use Ximdex\NodeTypes\ServerNode;
use Ximdex\Runtime\DataFactory;

include_once XIMDEX_ROOT_PATH . '/src/Sync/conf/synchro_conf.php';

class SynchroFacade
{
    /**
     * Return the target server to publicate a specified node in one given channel
     * 
     * @param int $idTargetNode
     * @param int $idTargetChannel
     * @param int $idServer
     * @return null|int
     */
    public function getServer(int $idTargetNode, ?int $idTargetChannel, int $idServer) : ?int
    {
        $targetNode = new Node(($idTargetNode));
        if (! $targetNode->get('IdNode')) {
            Logger::error(_('No correct node received'));
            return null;
        }
        $server = new Server($idServer);
        if (! $server->get('IdServer')) {
            Logger::error(_('No correct server received'));
            return null;
        }

        // Looking for a possible frame for the destiny channel
        $targetFrame = new ServerFrame();
        $frameID = $targetFrame->getCurrent($idTargetNode, $idTargetChannel); // esto es un idSync
        if (! ($frameID > 0)) {
            Logger::warning(_("No target frame available") . " FACADE target node: $idTargetNode target channel: " 
                . (is_null($idTargetChannel) ? 'NULL' : $idTargetChannel) . " server: $idServer");
            return null;
        }
        
        // Calculating physical origin and destiny servers
        $physicalTargetServers = $targetFrame->getCompleteServerList($idTargetNode, $idTargetChannel);
        if (count($physicalTargetServers) == 0) {
            Logger::error(_("No physical target server available"));
            return null;
        }
        
        // Gets only enabled servers
        if (in_array($idServer, $physicalTargetServers)) {
            return $idServer;
        }
        return $physicalTargetServers[rand(0, count($physicalTargetServers) - 1)];
    }

    /**
     * Return pending tasks for a given node
     * 
     * @param int $nodeID
     * @return null|array
     */
    public function getPendingTasksByNode(?int $nodeID) : ?array
    {
        if (is_null($nodeID)) {
            Logger::info("Void node");
            return null;
        }
        $nodeFramesMng = new NodeFrameManager();
        $pendingTasks = $nodeFramesMng->getPendingNodeFrames($nodeID);
        return $pendingTasks;
    }

    /**
     * Return if node is published
     * 
     * @param int $nodeID
     * @return boolean
     */
    public function isNodePublished(?int $nodeID) : bool
    {
        if (is_null($nodeID)) {
            Logger::info('Void node');
            return false;
        }
        $nodeFrame = new NodeFrame();
        $result = $nodeFrame->getPublishedId($nodeID);
        return (bool) $result;
    }
    
    /**
     * Delete all tasks by node
     *
     * @param int $nodeID
     * @param boolean $unPublish --> don't delete task, set it to Due2Out state
     * @return null|boolean
     */
    public function deleteAllTasksByNode(?int $nodeID, bool $unPublish = false) : ?bool
    {
        if (is_null($nodeID)) {
            Logger::info("No existing node with id $nodeID");
            return null;
        }
        if (! is_null($unPublish)) {
            Logger::info('Unpublish documents before deleting node');
        } else {
            Logger::info('Delete node and keep documents published');
        }
        $deleteIDs = self::getAllTaskByNode($nodeID);
        foreach ($deleteIDs as $id) {
            $nodeFrameMng = new NodeFrameManager();
            $tasks = $nodeFrameMng->getByNode($id);
            if (sizeof($tasks) > 0) {
                foreach ($tasks as $dataFrames) {
                    $idNodeFrame = $dataFrames[0];
                    $nodeFrameMng->delete($idNodeFrame, $unPublish);
                }
            }
        }
        return true;
    }

    /**
     * @param int $nodeID
     * @return array
     */
    public static function getAllTaskByNode(?int $nodeID) : array
    {
        if (is_null($nodeID)) {
            Logger::info("No existing node with id $nodeID");
            return array();
        }
        $node = new Node($nodeID);
        $pendingTasks = array();
        $publishedTasks = array();
        
        // First node will be the root node
        $childList = [
            $nodeID
        ];
        $workFlowSlaves = $node->getWorkFlowSlaves();
        $workFlowSlaves = count($workFlowSlaves) > 0 ? $workFlowSlaves : array();
        if ($childList) {
            foreach ($childList as $child) {
                $childNode = new Node($child);
                $childList = array_merge($childList, $childNode->traverseTree(), $workFlowSlaves);
            }
            if (sizeof($childList) > 0) {
                foreach ($childList as $nodeID) {
                    $pendingTasks = array_merge($pendingTasks, self::getPendingTasksByNode($nodeID));
                    if (self::isNodePublished($nodeID)) {
                        $publishedTasks[] = $nodeID;
                    }
                }
            }
        }
        $deleteIDs = array_merge($pendingTasks, $publishedTasks);
        $deleteIDs = array_unique($deleteIDs);
        return $deleteIDs;
    }
    
    /**
     * Gets the Channel associated to frame
     *
     * @param int idFrame
     * @return int|NULL
     */
    public function getFrameChannel(?int $idFrame) : ?int
    {
        if (is_null($idFrame)) {
            Logger::error('Void param idFrame');
            return null;
        }
        $serverFrame = new ServerFrame($idFrame);
        $channelFrameId = $serverFrame->get('IdChannelFrame');
        $channelFrame = new ChannelFrame($channelFrameId);
        $channel = $channelFrame->get('ChannelId');
        return (int) $channel;
    }

    /**
     * PushDocInPublishingPool
     *
     * @param int $idNode
     * @param int $upDate --> date for publication document (timestamp type)
     * @param int $downDate --> date for unpublish the document (timestamp type)
     * @param array $flagsArray
     *            forcePublication:
     *            --> if true --> publish the document although it is in the last version
     *            --> if false --> only publish the document if there is a new mayor version no publish
     *            forceDependencies: --> if true --> publish the dependencies although they are in the last version
     * @return array|null
     */
    function pushDocInPublishingPool(int $idNode, int $upDate, int $downDate = null, array $flagsArray = null) : ?array
    {
        $syncMngr = new SyncManager();
        
        // Default values
        if (! isset($flagsArray['force'])) {
            $syncMngr->setFlag('force', false);
        } else {
            $syncMngr->setFlag('force', $flagsArray['force']);
        }
        if ($flagsArray) {
            foreach ($flagsArray as $key => $value) {
                if ($key == 'force') {
                    continue;
                }
                $syncMngr->setFlag($key, $value);
            }
        }
        Logger::info("Stablishing PUSH");
        $syncMngr->setFlag('deleteOld', true);
        $result = $syncMngr->pushDocInPublishingPool($idNode, $upDate, $downDate);
        return $result;
    }

    /**
     * Function which looks for a frame remote path
     * 
     * @param int $idNode
     * @return boolean
     */
    public static function HasUnlimitedLifeTime(int $idNode) : bool
    {
        return false;
    }
    
    /**
     * Node expiration
     * 
     * @param Node $node
     * @param int $down
     * @param array $flagsExpiration
     * @return bool
     */
    public function expire(Node $node, int $down, array $flagsExpiration) : bool
    {
        set_time_limit(0);
        
        // Get the implicated nodes to will be expire
        $syncMngr = new SyncManager();
        $syncMngr->setFlags($flagsExpiration);
        $nodes2expire = $syncMngr->getPublishableDocs($node, $down, $down);
        if (! $nodes2expire) {
            return true;
        }
        
        // Get portal version
        $portal = new PortalFrames();
        $idPortalFrame = $portal->upPortalFrameVersion($node->getID(), $down, Session::get('userID'), PortalFrames::TYPE_DOWN);
        if (! $idPortalFrame) {
            Logger::error('Cannot create the portal version for server: ' . $node->getServer());
            return false;
        }
        
        // Load enabled servers
        $enabledServers = ServerNode::getServersForPumping();
        if ($enabledServers === false) {
            return false;
        }
        
        // Generate a list with server frames per enabled server
        $nodeFrame = new NodeFrame();
        $servers = [];
        foreach ($nodes2expire as $id) {
            
            // Obtain the node frames related to the nodes to expire
            $nodeFrames = $nodeFrame->getNodeFramesOnDate($id, $down);
            if ($nodeFrames === false) {
                return false;
            }
            foreach ($nodeFrames as $nodeFrameId) {
                $nodeFrame = new NodeFrame($nodeFrameId);
                $nodeFrame->set('TimeDown', $down);
                $nodeFrame->set('IdPortalFrame', $idPortalFrame);
                $nodeFrame->update();
                
                // Update server frames list with server organization
                foreach ($nodeFrame->getFrames(null, Batch::TYPE_DOWN, $enabledServers) as $idSync) {
                    $serverFrame = new ServerFrame($idSync);
                    if (in_array($serverFrame->get('State'), ServerFrame::PUBLISHING_STATUS)) {
                        $servers[$serverFrame->get('IdServer')][] = $idSync;
                    }
                }
            }
            
            // Obtain the frames to be cancelled
            $nodeFrames = $nodeFrame->getFutureNodeFramesForDate($id, $down);
            if ($nodeFrames === false) {
                return false;
            }
            foreach ($nodeFrames as $nodeFrameId) {
                $nodeFrame = new NodeFrame($nodeFrameId);
                $nodeFrame->cancel();
            }
        }
        
        // Processing each server
        $batch = new Batch();
        $batchId = null;
        $numFrames = 0;
        foreach ($servers as $serverId => & $frames) {
            
            // Set the date to expire for each server frames
            $createBatch = true;
            foreach ($frames as $idSync) {
                
                // If must be created a new batch on max number
                if ($createBatch) {
                    
                    // Close and save previous server batch
                    if ($batchId) {
                        if ($this->setBatchToWaiting($batch, $numFrames) === false) {
                            return false;
                        }
                    }
                    
                    // Create a new batch type Down
                    $batchId = $batch->create($down, Batch::TYPE_DOWN, $node->getID(), Batch::PRIORITY_TYPE_DOWN, $serverId, null
                        , $idPortalFrame, Session::get('userID'), 0);
                    if (! $batchId) {
                        Logger::error('Cannot create the down batch process');
                        return false;
                    }
                    $createBatch = false;
                }
                $serverFrame = new ServerFrame($idSync);
                if (! $serverFrame->get('IdSync')) {
                    Logger::error('Cannot load the server frame with ID: ' . $idSync);
                    continue;
                }
                $serverFrame->set('DateDown', $down);
                $serverFrame->set('IdBatchDown', $batchId);
                $serverFrame->set('IdPortalFrame', $idPortalFrame);
                $serverFrame->update();
                $numFrames++;
                
                // Update the batch with the results
                if ($numFrames == MAX_NUM_NODES_PER_BATCH) {
                    if ($this->setBatchToWaiting($batch, $numFrames) === false) {
                        return false;
                    }
                    $batchId = null;
                    $createBatch = true;
                }
            }
        }
        if ($batch->get('IdBatch')) {
            
            // Update the batch with the last generated frames 
            if ($numFrames and $numFrames < MAX_NUM_NODES_PER_BATCH) {
                if ($this->setBatchToWaiting($batch, $numFrames) === false) {
                    return false;
                }
            }
            
            // Play the portal
            $portal->set('Playing', true);
            $portal->update();
            
            // Update portal frame information
            try {
                PortalFrames::updatePortalFrames(null, null, $portal->get('id'));
            } catch (\Exception $e) {
                Logger::error($e->getMessage());
            }
        } else {
            
            // We have a portal type Down frame without batchs type Down
            $portal->delete();
        }
        return true;
    }
    
    /**
     * Publish a node in a physical server, always forced. Channels not supported at this time
     * 
     * @param int $nodeId
     * @param int $serverId
     * @param string $name
     * @throws \Exception
     */
    public function publicate(int $nodeId, int $serverId, string $name = null) : void
    {
        $node = new Node($nodeId);
        if (! $node->getID()) {
            throw new \Exception("Node {$nodeId} does not exists");
        }
        $time = time();
        $userId = Session::get('userID');
        if (! $userId) {
            $userId = User::XIMDEX_ID;
        }
        if (! $name) {
            $name = $node->getPublishedNodeName();
        }
        $portal = new PortalFrames();
        $portalId = $portal->upPortalFrameVersion($nodeId, $time, $userId, PortalFrames::TYPE_UP, true);
        if (! $portalId) {
            throw new \Exception('Cannot generate a new portal frame version');
        }
        $batch = new Batch();
        $batchId = $batch->create($time, Batch::TYPE_UP, $nodeId, DEFAULT_BATCH_PRIORITY, $serverId, null, $portalId, $userId);
        if (! $batchId) {
            throw new \Exception('Cannot generate a publication batch');
        }
        
        // Creating nodeFrame
        $data = new DataFactory($nodeId);
        $versionId = $data->getLastVersionId();
        if (! $versionId) {
            throw new \Exception("Can not obtain the last version for node {$nodeId}");
        }
        $nodeFrame = new NodeFrame();
        $nodeFrameId = $nodeFrame->create($nodeId, $name, $versionId, $time, $portalId);
        if (! $nodeFrameId) {
            throw new \Exception("A node frame could not be obtained for node {$nodeId}");
        }
        
        // Creating server frame
        $serverFrame = new ServerFrame();
        $id = $serverFrame->create($nodeId, $serverId, $time, $node->getPublishedPath(), $name, 0, $nodeFrameId, null, null, $batchId
            , $portalId, null, 0, false);
        if (! $id) {
            throw new \Exception("A server frame could not be created for node {$nodeId}");
        }
        
        // Update batch and portal frames information
        $batch = new Batch($batchId);
        $frames = 1;
        $this->setBatchToWaiting($batch, $frames);
        $portal = new PortalFrames($portalId);
        $portal->set('Playing', true);
        $portal->update();
    }
    
    private function setBatchToWaiting(Batch $batch, int & $numFrames)
    {
        $batch->set('ServerFramesTotal', $numFrames);
        $batch->set('ServerFramesPending', $numFrames);
        $batch->set('State', Batch::WAITING);
        $numFrames = 0;
        $res = $batch->update();
        
        // Update portal frame information for current batch
        try {
            PortalFrames::updatePortalFrames($batch);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
        return $res;
    }
}

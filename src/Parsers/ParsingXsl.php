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

namespace Ximdex\Parsers;

use DOMDocument;
use DOMXPath;
use Ximdex\Logger;
use Ximdex\Models\Node;
use Ximdex\NodeTypes\XsltNode;
use Ximdex\Utils\FsUtils;
use Ximdex\Utils\Messages;

class ParsingXsl
{
	private $xpathObj;
	private $idTemplate;
	private $node;
	private $includedElements = array();
	private $path;
	private $idDoc;

	function __construct(int $idTemplate = null, string $path = null, int $idDoc = null)
	{
	    if (! $this->setNode($idTemplate, $path)) {
			return null;
	    }
		$this->idDoc = $idDoc;
		$this->setXpathObj();
		$this->setIncludedElements();
	}

	public function getIncludedElements(string $name = null, bool $removeExtension = false, bool $baseName = false)
	{
	    if ($removeExtension || $baseName) {
			$this->setIncludedElements($removeExtension, $baseName);
	    }
	    if (is_null($name)) {
			return $this->includedElements;
	    }
		$out = array();
		foreach ($this->includedElements as $includedElement) {
		    if (strpos($includedElement, $name) !== false) {
				$out[] = $includedElement;
		    }
		}
		return $out;
	}

	private function setIncludedElements(bool $removeExtension = false, bool $baseName = false)
	{
		$this->includedElements = array();
		if (! $this->setXpathObj()) {
			return false;
		}
		$nodeList = $this->xpathObj->query('//*[local-name(.)="include"]');
		if ($nodeList->length > 0) {
			foreach ($nodeList as $domNode) {
				if ($domNode->nodeName == 'xsl:include') {
					$templateRef = $domNode->getAttribute('href');
					if ($baseName) {
						$templateRef = basename($templateRef);
					}
					if ($removeExtension) {
						$templateRef = str_replace('.xsl', '', $templateRef);
					}
					$this->includedElements[] = $templateRef;
				}
			}
		}

		return true;
	}

	private function setNode(int $idNode = null, string $path = null)
	{
		if (is_null($idNode) && is_null($path)) {
			Logger::error('Cannot parse template: idNode and path are null');
			return false;
		}
		if (is_null($idNode)) {
			$this->path = $path;
			return true;
		}
		$this->node = new Node($idNode);
		if (! $this->node->get('IdNode')) {
			Logger::error('Can not parse template: Not existing node ' . $idNode);
			return false;
		}
		if ($this->node->nodeType->get('Name') != 'XslTemplate') {
			Logger::error('Cannot parse template: Node ' . $idNode . ' is not a Xsl Template');
			return false;
		}
		$this->idTemplate = $idNode;
		return true;
	}

	private function setXpathObj()
	{
		if ($this->node) {
			$content = $this->node->GetContent();
			if ($this->node->GetNodeName() == 'docxap.xsl' and $this->idDoc) {
			    
			    // Include the correspondant includes_template.xsl for the current document
			    if (! XsltNode::replace_path_to_local_templatesInclude($content, $this->idDoc)) {
			        Logger::error('setXpathObj error: cannot replace the templates_include in docxap for XML document: ' . $this->idDoc);
			        return false;
			    }
			}
		} else {
			$content = FsUtils::file_get_contents($this->path);
		}
		if (! $content) {
		    $error = 'setXpathObj error: empty XML content or another problem to get it';
		    if (Messages::error_message()) {
		        $error .= ' (' . Messages::error_message() . ')';
		    }
		    Logger::error($error);
		    return false;
		}
		$domDoc = new DOMDocument();
		$domDoc->preserveWhiteSpace = false;
		$domDoc->validateOnParse = true;
		$domDoc->formatOutput = true;
		$res = @$domDoc->loadXML($content);
		if ($res === false) {
		    Logger::error('setXpathObj error: can\'t load XML content (' . Messages::error_message() . ')');
		    return false;
		}
		$this->xpathObj = new DOMXPath($domDoc);
		return true;
	}
}
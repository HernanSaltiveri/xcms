{**
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
 *}

<form method="post" name="formulario" id="formulario" action="{$action_url}">
	<input type="hidden" name="nodeid" value="{$id_node}" />
	{include file="actions/components/title_Description.tpl"}
	   <div class="action_content">
	   <ul>
            <li><span>{$texto}</span></li>
            {if (isset($depList) and $depList)}
                <li><span><em>{$titulo}:</em></span></li>
                {foreach from=$depList item=dep}
                    <li nowrap class="deletenodes"><span>{$dep}</span></li>
                {foreachelse}
                    <li nowrap><span>{t}No dependencies were found{/t}</span></li>
                {/foreach}
            {/if}
	   </ul>
	</div>
</form>
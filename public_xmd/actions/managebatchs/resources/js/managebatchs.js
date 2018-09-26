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

function doPrioritizeSubmit(idBatch)
{
	setFilterValues();
	document.getElementById('frm_id_batch').value = idBatch;
	document.getElementById('frm_prioritize_batch').value = 'yes';
	document.frm_batchs.submit();
}

function doDeprioritizeSubmit(idBatch)
{
	setFilterValues();
	document.getElementById('frm_id_batch').value = idBatch;
	document.getElementById('frm_deprioritize_batch').value = 'yes';
	document.frm_batchs.submit();
}

function doDeactivateSubmit(idBatch)
{
	setFilterValues();
	document.getElementById('frm_id_batch').value = idBatch;
	document.getElementById('frm_deactivate_batch').value = 'yes';
	document.frm_batchs.submit();
}

function doActivateSubmit(idBatch)
{
	setFilterValues();
	document.getElementById('frm_id_batch').value = idBatch;
	document.getElementById('frm_activate_batch').value = 'yes';
	document.frm_batchs.submit();
}

function doFilterSubmit()
{
	setFilterValues();
	document.frm_batchs.submit();
}

function setFilterValues()
{
	document.getElementById('frm_filter_state_batch').value = document.getElementById('frm_select_filter_state_batch').value;
	document.getElementById('frm_filter_active_batch').value = document.getElementById('frm_select_filter_active_batch').value;
	if (document.getElementById('update').value != 'Click Aqui...') {
		document.getElementById('frm_filter_up_date').value = document.getElementById('update').value + ' '
			+ document.getElementById('uphour').value + ':'
			+ document.getElementById('upmin').value;
	}
	if (document.getElementById('downdate').value != 'Click Aqui...') {
		document.getElementById('frm_filter_down_date').value = document.getElementById('downdate').value + ' '
			+ document.getElementById('downhour').value + ':'
			+ document.getElementById('downmin').value;
	}
	document.getElementById('frm_filter_batch').value = 'yes';
}

function showOrHideContent(divId, name, extra)
{
	if (isVisibleAnyContents(name) && extra == 'all') {
		hideContent(name, divId);
		document.getElementById(divId).style.display = 'none';
		return;
	}
	hideContent(name, 'none');
	var element = document.getElementById(divId);
	var state = document.getElementById(divId).style.display;
	if (state == 'none') {
		document.getElementById(divId).style.display = 'block';
	} else {
		document.getElementById(divId).style.display = 'none';
	}
}

function hideContent(divName, excluded)
{
	var elements = document.getElementsByName(divName);
	if (elements.length > 0) {
		for (i = 0; i < elements.length; i ++){
			if (elements[i].id != excluded || excluded == 'none') {
				elements[i].style.display = 'none';
			}
		}
	}
}

function isVisibleAnyContents(divName)
{
	var elements = document.getElementsByName(divName);
	if (elements.length > 0) {
		for (i = 0; i < elements.length; i ++){
			if (elements[i].style.display == 'block') {
				return true;
			}
		}
	}
	return false;
}

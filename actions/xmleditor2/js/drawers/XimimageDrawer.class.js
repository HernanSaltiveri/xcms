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

var XimimageDrawer = Object.xo_create (new Drawer(), {

	_init: function (elementid, tool) {

		this.element 	= getFromSelector (elementid); //object drawer
	    this.tool 		= tool;
		this.ximElement = null;
	},

	setXimElement: function (ximElement) {

		this.ximElement = ximElement;
	},

    createContent: function () {

		$("body").append ($("<div/>").addClass ("overlay js_overlay"));
		$('button.save-button', this.element).click (this.save.bind (this));
    	$('button.close-button', this.element).click (this.close.bind (this));

    	var div 	= $('#attributes-list-options', this.element);
    	var tagName = $('<p />').addClass ('tag-name').html (this.ximElement.tagName);

    	div.empty ();
    	div.append (tagName);

    	for (var aux in this.ximElement.schemaNode.attributes) this.createInput (aux, div);

		$(this.element).show();
        this.focusElement();
    },

    createInput: function (aux, div) {

    	if (aux == 'uid') return;

    	var attribute 	= this.ximElement.schemaNode.attributes [aux];
    	var label 		= $('<label />').attr ({for: 'ximimage-' + aux}).addClass ('title').html (aux);
    	var input 		= attribute.values.length ? $('<select />') : $('<input />');

    	input.attr ({id: 'ximimage-' + aux}).addClass ('kupu-toolbox-st ximlink-search xlinks_input');

    	if (attribute.values.length) {

    		var j = 0;

    		while (j < attribute.values.length) {

    			var option 	= $('<option />');
    			var valor 	= attribute.values [j];

    			if (valor == this.ximElement.attributes [aux]) option.attr('selected', 'selected');
				input.append (option.attr ({value: valor}).html (valor));
				j++;
    		}
    	}else input.attr ({type: 'text', value: this.ximElement.attributes [aux]});

    	div.append (label, input);
    },

    save: function () {

    	/*var item = $('select.ximlink-list option:selected', this.element);
    	var text = $('div.descriptions-list-options input:radio:checked', this.element);

		if( item.length >= 1){
			this.$input.val(item.val());
			if (text.length && text.val() != ""){
				$(this.ximElement._htmlElements).filter(":visible").text(text.val());
			}
		}*/

   	   	var toolbox = this.tool.toolboxes['attributestoolbox'];

       	toolbox.updateButtonHandler();
		this.close ();
    },

    close: function () {

    	/*this.data = null;

    	$('div.js_add_link_panel', this.element).hide(); ;
		$('div.js_add_link_panel', this.element).next("div.buttons").hide(); ;
		$('div.js_search_link_panel', this.element).show() ;
		$('div.js_search_link_panel', this.element).next("div.buttons").show() ;
		$('a.js_add_link', this.element).show() ;
		$("input", this.element).val("");
    	$('select.ximlink-list', this.element).unbind().empty();
    	$('input.ximlink-search', this.element).unbind();

    	$("div.xim-treeview-selector",this.element).treeview("destroy");
    	$("div.xim-treeview-selector",this.element).empty();
    	*/

    	var dt = this.tool.editor.getTool ('ximdocdrawertool');

    	dt.closeDrawer();
    	$('button.save-button', this.element).unbind();
    	$('button.close-button', this.element).unbind();
		$('div.js_overlay').remove();
    }
});
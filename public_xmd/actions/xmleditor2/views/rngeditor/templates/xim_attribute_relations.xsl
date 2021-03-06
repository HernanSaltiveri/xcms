<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:xim="http://www.ximdex.com/" exclude-result-prefixes="xim" extension-element-prefixes="xim">
	<xsl:template name="xim:attribute_relations" match="xim:attribute_relations">
        <span id="{@uid}" />
        <div uid="{@uid}" class="rngeditor_block">
			<img src="../../assets/images/tree/Lminus.png" align="absmiddle" class="ctrl minus folding"/>
			<img src="../../assets/images/tree/folder.png" align="absmiddle" class="folder folding"/>
			<img src="../../assets/images/tree/blank.png" width="10px" align="absmiddle"/>
			<span class="rngeditor_title">xim:attribute_relations</span>
        	<div id="tg_{@uid}">
				<xsl:apply-templates/>
			</div>
        </div>
	</xsl:template>
</xsl:stylesheet>

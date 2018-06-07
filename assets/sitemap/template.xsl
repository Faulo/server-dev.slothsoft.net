<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://schema.slothsoft.net/farah/sitemap"
	xmlns:sfd="http://schema.slothsoft.net/farah/dictionary"
	xmlns:sfm="http://schema.slothsoft.net/farah/module"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 
	<xsl:template match="/*">
		<domain 
			name="dev.slothsoft.net"
			vendor="slothsoft"
			module="dev.slothsoft.net"
			ref="pages/home" status-active="" status-public=""
			sfd:languages="de-de en-us">
			
			<page name="pics" ref="pages/pics" status-active=""/>
			<page name="poll" ref="pages/poll" status-active=""/>
			<page name="smartphone" ref="pages/smartphone" status-active=""/>
			
			<page name="GameOfLife" ref="pages/GameOfLife" status-active=""/>
			
			<page name="sitemap" ref="//slothsoft@farah/sitemap-generator" status-active=""/>
		</domain>
	</xsl:template>
</xsl:stylesheet>
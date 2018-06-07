<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:sfm="http://schema.slothsoft.net/farah/module"
	xmlns:sfs="http://schema.slothsoft.net/farah/sitemap"
	xmlns:lio="http://slothsoft.net"
	xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions"
	xmlns:str="http://exslt.org/strings"
	extension-element-prefixes="lio exsl func str">

	<xsl:import href="farah://slothsoft@dev/overwatch/counter/counter-list" />

	<xsl:variable name="sourceTable"
		select="$source//*[@class='td-post-content']/table" />

	<func:function name="lio:calculate-counter">
		<xsl:param name="thisHero" />
		<xsl:param name="thatHero" />

		<xsl:variable name="goodList"
			select="$sourceTable//td[count(preceding-sibling::*) = 1]" />
		<xsl:variable name="badList"
			select="$sourceTable//td[count(preceding-sibling::*) = 2]" />

		<xsl:choose>
			<xsl:when
				test="$goodList[lio:equals((preceding::td[b])[last()], $thisHero)][lio:equals(., $thatHero)]">
				<func:result select="1" />
			</xsl:when>
			<xsl:when
				test="$badList[lio:equals((preceding::td[b])[last()], $thisHero)][lio:equals(., $thatHero)]">
				<func:result select="-1" />
			</xsl:when>
			<xsl:otherwise>
				<func:result select="0" />
			</xsl:otherwise>
		</xsl:choose>
	</func:function>
</xsl:stylesheet>

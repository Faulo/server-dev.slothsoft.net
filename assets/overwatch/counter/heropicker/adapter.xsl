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

	<xsl:variable name="sourceTable" select="$source//*[@class='counter-table']" />

	<func:function name="lio:calculate-counter">
		<xsl:param name="thisHero" />
		<xsl:param name="thatHero" />

		<xsl:variable name="headRow" select="$sourceTable/thead/tr/*" />
		<xsl:variable name="dataRow"
			select="$sourceTable/tbody/tr[lio:equals(td[1], $thisHero)]/*" />

		<xsl:for-each select="$headRow">
			<xsl:variable name="i" select="position()" />
			<xsl:if test="lio:equals(., $thatHero)">
				<func:result
					select="lio:translate-counter(normalize-space($dataRow[$i]/@class))" />
			</xsl:if>
		</xsl:for-each>
	</func:function>

	<func:function name="lio:translate-counter">
		<xsl:param name="key" />

		<xsl:variable name="translation"
			select="document('')//lio:translation[@key = $key]" />
		<xsl:choose>
			<xsl:when test="$translation">
				<func:result select="$translation/@val" />
			</xsl:when>
			<xsl:otherwise>
				<func:result select="$key" />
			</xsl:otherwise>
		</xsl:choose>
	</func:function>

	<lio:translation key="counter-data counter-good counter-strong"
		val="2" />
	<lio:translation key="counter-data counter-good counter-medium"
		val="1" />
	<lio:translation key="counter-data same-hero" val="0" />
	<lio:translation key="counter-data" val="0" />
	<lio:translation key="counter-data counter-bad counter-medium"
		val="-1" />
	<lio:translation key="counter-data counter-bad counter-strong"
		val="-2" />
	<lio:translation key=""
		val="HERO NOT FOUND OR SOME OTHER ERROR, HELP" />
</xsl:stylesheet>

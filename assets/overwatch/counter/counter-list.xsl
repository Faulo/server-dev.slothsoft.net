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
	extension-element-prefixes="exsl func str">

	<xsl:variable name="config" select="/*/*[@name='config']/config" />
	<xsl:variable name="source" select="/*/*[@name='source']/*" />



	<func:function name="lio:contains">
		<xsl:param name="a" />
		<xsl:param name="b" />

		<func:result select="contains(lio:normalize($a), lio:normalize($b))" />
	</func:function>
	<func:function name="lio:equals">
		<xsl:param name="a" />
		<xsl:param name="b" />

		<func:result select="lio:normalize($a) = lio:normalize($b)" />
	</func:function>
	<func:function name="lio:normalize">
		<xsl:param name="a" />

		<func:result
			select="normalize-space(translate(normalize-space($a), 'abcdefghijklmnopqrstuvwxyz:', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ '))" />
	</func:function>
	
	<xsl:template match="/*[@name='counter']">
		<lio:counter-list>
			<xsl:for-each select="*">
				<lio:source name="{@name}">
					<xsl:copy-of select="lio:counter/@*"/>
					<xsl:copy-of select="lio:counter/*"/>
				</lio:source>
			</xsl:for-each>
		</lio:counter-list>
	</xsl:template>

	<xsl:template match="/*[@name!='counter']">
		<lio:counter>
			<xsl:for-each select="$config/hero-list/hero">
				<xsl:variable name="heroName" select="@name" />
				<lio:hero name="{$heroName}">
					<xsl:for-each select="$config/hero-list/hero">
						<xsl:variable name="otherName" select="@name" />
						<lio:other-hero name="{$otherName}"
							counter-value="{lio:calculate-counter($heroName, $otherName)}" />
					</xsl:for-each>
				</lio:hero>
			</xsl:for-each>
		</lio:counter>
	</xsl:template>
</xsl:stylesheet>

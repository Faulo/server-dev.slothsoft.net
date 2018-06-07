<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://schema.slothsoft.net/farah/module"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:sfm="http://schema.slothsoft.net/farah/module">
	
	<xsl:output indent="yes"/>
	
	<xsl:param name="module"/>
	
	<xsl:template name="ref">
		<xsl:param name="path" select="''"/>
		<xsl:if test="@name">
			<xsl:attribute name="ref">
				<xsl:choose>
					<xsl:when test="starts-with(@name, '/')">
						<xsl:text>//slothsoft@</xsl:text>
						<xsl:value-of select="substring-before(substring-after(@name, '/'), '/')"/>
						<xsl:text>/</xsl:text>
						<xsl:value-of select="$path"/>
						<xsl:value-of select="substring-after(substring-after(@name, '/'), '/')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="concat($path, @name)"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="info">
		<xsl:comment>
Farah Module Manifest

Module: <xsl:value-of select="$module"/>
Vendor: slothsoft
Farah ID: farah://slothsoft@<xsl:value-of select="$module"/>
Composer ID: slothsoft/<xsl:value-of select="$module"/>

Homepage: http://farah.slothsoft.net/modules/<xsl:value-of select="$module"/>
Packagist: https://packagist.org/packages/slothsoft/<xsl:value-of select="$module"/>
GitHub: https://github.com/Faulo/slothsoft-<xsl:value-of select="$module"/>

Llo
</xsl:comment>
	</xsl:template>

	<xsl:template match="sfm:module">
		<module>
			<xsl:call-template name="info"/>
			<default-configuration>
				<xsl:copy-of select="sfm:default-configuration/*"/>
			</default-configuration>
			<xsl:apply-templates select="sfm:assets"/>
		</module>
	</xsl:template>
	
	<xsl:template match="
		sfm:assets | sfm:param | sfm:include-fragment | sfm:directory
	 	| sfm:use-stylesheet | sfm:use-template | sfm:use-script | sfm:use-document
	 	">
		<xsl:copy select=".">
			<xsl:copy-of select="attribute::*"/>
			<xsl:apply-templates select="sfm:resource | sfm:resourceDir | sfm:resource-directory" mode="res"/>
			<xsl:apply-templates select="*"/>
		</xsl:copy>
	</xsl:template>
	
	<xsl:template match="module">
		<module>
			<xsl:call-template name="info"/>
			<default-configuration>
				<xsl:copy-of select="default-configuration/*"/>
			</default-configuration>
			<assets>
				<resource-directory name="data" path="data" type="application/x-php"/>
				<resource-directory name="css" path="stylesheet" type="text/css"/>
				<resource-directory name="js" path="script" type="application/javascript"/>
				<resource-directory name="xsl" path="template" type="application/xslt+xml"/>
				<resource-directory name="dictionary" path="dictionary" type="application/xml"/>
				<resource-directory name="pages" path="pages" type="application/xml"/>
				
				<directory name="static">
					<xsl:apply-templates select="*" mode="res"/>
				</directory>
				
				<xsl:apply-templates select="*"/>
			</assets>
		</module>
	</xsl:template>
	
	<xsl:template match="fragment | sfm:fragment">
		<fragment name="{@name}">
			<xsl:apply-templates select="*"/>
		</fragment>
	</xsl:template>
	
	<xsl:template match="template | sfm:template">
		<use-template>
			<xsl:call-template name="ref">
				<xsl:with-param name="path" select="'xsl/'"/>
			</xsl:call-template>
			<xsl:apply-templates select="*"/>
		</use-template>
	</xsl:template>
	
	<xsl:template match="data | sfm:data">
		<use-document>
			<xsl:if test="@as">
				<xsl:attribute name="as"><xsl:value-of select="@as"/></xsl:attribute>
			</xsl:if>
			<xsl:call-template name="ref">
				<xsl:with-param name="path" select="'data/'"/>
			</xsl:call-template>
			<xsl:apply-templates select="*"/>
		</use-document>
	</xsl:template>
	
	<xsl:template match="res | sfm:res">
		<use-document>
			<xsl:if test="@as">
				<xsl:attribute name="as"><xsl:value-of select="@as"/></xsl:attribute>
			</xsl:if>
			<xsl:call-template name="ref">
				<xsl:with-param name="path" select="'static/'"/>
			</xsl:call-template>
			<xsl:apply-templates select="*"/>
		</use-document>
	</xsl:template>
	
	<xsl:template match="resDir | sfm:resDir">
		<use-document>
			<xsl:if test="@as">
				<xsl:attribute name="as"><xsl:value-of select="@as"/></xsl:attribute>
			</xsl:if>
			<xsl:call-template name="ref">
				<xsl:with-param name="path" select="'static/'"/>
			</xsl:call-template>
			<xsl:apply-templates select="*"/>
		</use-document>
	</xsl:template>
	
	<xsl:template match="style | sfm:style">
		<use-stylesheet>
			<xsl:call-template name="ref">
				<xsl:with-param name="path" select="'css/'"/>
			</xsl:call-template>
			<xsl:apply-templates select="*"/>
		</use-stylesheet>
	</xsl:template>
	
	<xsl:template match="script | sfm:script">
		<use-script>
			<xsl:call-template name="ref">
				<xsl:with-param name="path" select="'js/'"/>
			</xsl:call-template>
			<xsl:apply-templates select="*"/>
		</use-script>
	</xsl:template>
	
	<xsl:template match="param">
		<param name="{@name}">
			<xsl:if test="@value">
				<xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@scope">
				<xsl:attribute name="scope"><xsl:value-of select="@scope"/></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*"/>
		</param>
	</xsl:template>
	
	<xsl:template match="call | sfm:call">
		<use-document>
			<xsl:if test="@as">
				<xsl:attribute name="as"><xsl:value-of select="@as"/></xsl:attribute>
			</xsl:if>
			<xsl:call-template name="ref">
				<xsl:with-param name="path" select="''"/>
			</xsl:call-template>
			<xsl:apply-templates select="*"/>
		</use-document>
	</xsl:template>
	
	<xsl:template match="struc | sfm:struc">
		<xsl:choose>
			<xsl:when test="ancestor::fragment | ancestor::sfm:fragment">
				<include-fragment>
					<xsl:call-template name="ref">
						<xsl:with-param name="path" select="''"/>
					</xsl:call-template>
					<xsl:apply-templates select="*"/>
				</include-fragment>
			</xsl:when>
			<xsl:otherwise>
				<fragment name="{@name}">
					<xsl:apply-templates select="*"/>
				</fragment>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="resourceDir | sfm:resourceDir | sfm:resource-directory"/>
	<xsl:template match="resource | sfm:resource"/>
	
	<xsl:template match="*">
		<xsl:message terminate="yes">
			Unknown element: <xsl:value-of select="name()"/>
		</xsl:message>
	</xsl:template>
	<xsl:template match="text()">
	</xsl:template>
	
	<xsl:template match="*" mode="res">
		<xsl:apply-templates select="*" mode="res"/>
	</xsl:template>
	<xsl:template match="resourceDir | sfm:resourceDir | sfm:resource-directory" mode="res">
		<xsl:variable name="options" select="attribute::*[name() != 'name' and name() != 'path' and name() != 'type' and name() != 'source']"/>
		<resource-directory name="{@name}">
			<xsl:if test="@path">
				<xsl:attribute name="path"><xsl:value-of select="@path"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@type">
				<xsl:attribute name="type"><xsl:value-of select="@type"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@source">
				<source href="{@source}"/>
			</xsl:if>
			<xsl:if test="count($options)">
				<options>
					<xsl:copy-of select="$options"/>
				</options>
			</xsl:if>
			<xsl:apply-templates select="*" mode="res"/>
		</resource-directory>
	</xsl:template>
	<xsl:template match="resource | sfm:resource" mode="res">
		<xsl:variable name="options" select="attribute::*[name() != 'name' and name() != 'path' and name() != 'type' and name() != 'source']"/>
		<resource name="{@name}">
			<xsl:if test="@path">
				<xsl:attribute name="path"><xsl:value-of select="@path"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@type">
				<xsl:attribute name="type"><xsl:value-of select="@type"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@source">
				<source href="{@source}"/>
			</xsl:if>
			<xsl:if test="count($options)">
				<options>
					<xsl:copy-of select="$options"/>
				</options>
			</xsl:if>
			<xsl:apply-templates select="*" mode="res"/>
		</resource>
	</xsl:template>
</xsl:stylesheet>

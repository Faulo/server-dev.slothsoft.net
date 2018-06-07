<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/data">
		<html>
			<head/>
			<body>
				<xsl:apply-templates select="." mode="fonts"/>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="data" mode="fonts">
		<div class="fonts">
			<xsl:variable name="name" select="'postscript'"/>
			<xsl:variable name="fonts" select="*[@data-cms-name='fonts']/array[string[@key=$name]]"/>
			<xsl:choose>
				<xsl:when test="*[@data-cms-name='prefFonts']">
					
					<xsl:for-each select="*[@data-cms-name='prefFonts']/object/array">
						<h2><xsl:value-of select="@key"/></h2>
						<table>
							<xsl:for-each select="string">
								<xsl:variable name="key" select="@val"/>
								<xsl:call-template name="printFont">
									<xsl:with-param name="font" select="$fonts[string[@key=$name and @val=$key]]"/>
									<xsl:with-param name="name" select="$name"/>
								</xsl:call-template>
							</xsl:for-each>
						</table>
						<hr/>
					</xsl:for-each>
				</xsl:when>
				<xsl:otherwise>
					<table>
						<xsl:for-each select="$fonts">
							<xsl:sort select="string[@key=$name]/@val"/>
							<xsl:variable name="font" select="normalize-space(string[@key=$name]/@val)"/>
							<xsl:if test="string-length($font) and count(following::string[@key=$name][@val = $font]) = 0">
								<xsl:call-template name="printFont">
									<xsl:with-param name="font" select="."/>
									<xsl:with-param name="name" select="$name"/>
								</xsl:call-template>
							</xsl:if>
						</xsl:for-each>
					</table>
				</xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>
	
	<xsl:template name="printFont">
		<xsl:param name="font"/>
		<xsl:param name="name"/>
		<xsl:for-each select="$font">
			<xsl:variable name="style">font-family: myFont<xsl:value-of select="string[@key=$name]/@val"/>, 'Arial Black';</xsl:variable>
			<tr>
				<td><xsl:value-of select="string[@key=$name]/@val"/></td>
				<style type="text/css">

@font-face {
	font-family: myFont<xsl:value-of select="string[@key=$name]/@val"/>;
	src:
		local("<xsl:value-of select="string[@key='postscript']/@val"/>"),
		local("<xsl:value-of select="string[@key='name']/@val"/>"),
		local("<xsl:value-of select="string[@key='family']/@val"/>"),
		url(<xsl:value-of select="../@uri"/>);
}

				</style>
				<td style="{$style}">Test</td>
				<td style="{$style}">𝖆𝖇𝖈𝖉𝖊𝖋𝖌𝖍𝖎𝖏𝖐𝖑𝖒𝖓𝖔𝖕𝖖𝖗𝖘𝖙𝖚𝖛𝖜𝖝𝖞𝖟</td>
				<td style="{$style}">🌰 	🌱 	🌲 	🌳 	🌴 	🌵 		🌷 	🌸 	🌹 	🌺 	🌻 	🌼 	🌽 	🌾 	🌿</td>
				<!--
				<td><h1 style="{$style}">Slothsoft - Font Test</h1></td>
				<td><time style="{$style}">[13.01.12 18:20:33]</time></td>
				<td>
					<span style="{$style}"> Normal </span>
					<b style="{$style}"> Bold </b>
					<i style="{$style}"> Kursiv </i>
					<u style="{$style}"> Unterstrichen </u>
				</td>
				<td>
					<p style="{$style} width: 800px;">
					Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras luctus facilisis cursus. Curabitur quis suscipit risus. Mauris id dolor leo, in gravida nisl. Phasellus porttitor velit non diam congue porttitor. Sed sed semper elit. Morbi in imperdiet nunc. Donec non turpis nulla. Praesent sed erat eu turpis rutrum mattis. Donec pretium aliquet justo vitae malesuada.
					</p>
				</td>
				-->
			</tr>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>

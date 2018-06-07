<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/data">
		<html>
			<head>
				<xsl:copy-of select="."/>
			</head>
			<body>
				<xsl:apply-templates select="." mode="fonts"/>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="data" mode="fonts">
		<div class="fonts">
			<xsl:variable name="fonts" select="*[@data-cms-name='fonts']"/>
			<table>
				<xsl:for-each select="$fonts">
					<xsl:call-template name="printFont"/>
				</xsl:for-each>
			</table>
		</div>
	</xsl:template>
	
	<xsl:template name="printFont">
		<xsl:variable name="style">font-family: <xsl:value-of select="generate-id(.)"/>, Arial;</xsl:variable>
		<tr>
			<td><xsl:value-of select="string[@key=$name]/@val"/></td>
			<style type="text/css">
@font-face {
font-family: <xsl:value-of select="generate-id(.)"/>;
src:
	url(<xsl:value-of select="@uri"/>);
}

			</style>
			<!--
			<td style="{$style}">Test</td>
			<td style="{$style}">𝖆𝖇𝖈𝖉𝖊𝖋𝖌𝖍𝖎𝖏𝖐𝖑𝖒𝖓𝖔𝖕𝖖𝖗𝖘𝖙𝖚𝖛𝖜𝖝𝖞𝖟</td>
			<td style="{$style}">🌰 	🌱 	🌲 	🌳 	🌴 	🌵 		🌷 	🌸 	🌹 	🌺 	🌻 	🌼 	🌽 	🌾 	🌿</td>
			-->
			<td><h1><a href="{@uri}" download="{@path}" style="{$style}"><xsl:value-of select="@path"/></a></h1></td>
			<td>
				<time style="{$style}">[13.01.12&#160;18:20:33]</time><br/>
				<time style="{$style}">[                     ]</time><br/>
				<time style="{$style}">[&#8195;&#8195;&#8195;&#8195;&#8195;&#8195;&#8195;&#8195;&#8195;&#8195;&#8195;&#8195;]</time><br/>
			</td>
			<td>
				<span style="{$style}"> Normal </span>
				<b style="{$style}"> Bold </b>
				<i style="{$style}"> Kursiv </i>
				<u style="{$style}"> Unterstrichen </u>
			</td>
			<td>
				<p style="{$style} height: 6em; column-width: 12em">
				Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras luctus facilisis cursus. Curabitur quis suscipit risus. Mauris id dolor leo, in gravida nisl. Phasellus porttitor velit non diam congue porttitor. Sed sed semper elit. Morbi in imperdiet nunc. Donec non turpis nulla. Praesent sed erat eu turpis rutrum mattis. Donec pretium aliquet justo vitae malesuada.
				</p>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>

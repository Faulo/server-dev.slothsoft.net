<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 
	<xsl:template match="/data">
		<html>
			<head>
				<title>GameOfLife</title>
				<style type="text/css"><![CDATA[
body {
	margin: 0;
}
button {
	padding: 1em;
	display: block;
	width: 100%;
}
svg {
	display: block;
}
pre {
	padding: 0 1em;
	margin: 0;
}
				]]></style>
			</head>
			<body>
				<button type="button" onclick="new GameOfLife(this.parentNode)">
					Start
				</button>
				<pre/>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

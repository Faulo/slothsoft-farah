<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfm="http://schema.slothsoft.net/farah/module">
	<xsl:template match="/*">
		<html>
			<head>
				<title>
					<xsl:value-of select="@url" />
				</title>
			</head>
			<body>
				<xsl:copy-of select="sfm:document-info/* | sfm:manifest-info" />
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

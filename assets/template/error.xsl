<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="error" mode="cms">
		<details open="open" class="errorMessage">
			<summary><xsl:value-of select="@message"/></summary>
			<pre>
				<xsl:value-of select="."/>
			</pre>
		</details>
	</xsl:template>
</xsl:stylesheet>

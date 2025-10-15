<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary">

	<xsl:import href="farah://slothsoft@farah/xsl/dictionary" />

	<xsl:template match="test">
		<test>
			<xsl:for-each select="word">
				<word input="{@input}" translation="{sfd:lookup-string(@input)}" />
			</xsl:for-each>
		</test>
	</xsl:template>
</xsl:stylesheet>
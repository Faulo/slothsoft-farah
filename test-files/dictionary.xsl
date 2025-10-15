<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary">

	<xsl:import href="farah://slothsoft@farah/xsl/dictionary" />

	<xsl:template match="test">
		<test>
			<xsl:for-each select="word">
				<word input="{@input}">
					<xsl:choose>
						<xsl:when test="@xml:lang">
							<xsl:attribute name="translation">
                                <xsl:value-of select="sfd:lookup-text(@input, 'slothsoft@test-module', @xml:lang)" />
                            </xsl:attribute>
							<xsl:copy-of select="@xml:lang" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="translation">
				                <xsl:value-of select="sfd:lookup-text(@input, 'slothsoft@test-module')" />
				            </xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</word>
			</xsl:for-each>
		</test>
	</xsl:template>
</xsl:stylesheet>
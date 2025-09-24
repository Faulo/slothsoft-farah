<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfx="http://schema.slothsoft.net/farah/xslt" xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions" xmlns:str="http://exslt.org/strings" extension-element-prefixes="func php" xmlns:php="http://php.net/xsl">

	<xsl:import href="farah://slothsoft@farah/xsl/xslt" />

	<xsl:template match="test">
		<test>
			<xsl:apply-templates select="*" />
		</test>
	</xsl:template>

	<xsl:template match="range">
		<range>
			<xsl:copy-of select="@*" />
			<xsl:for-each select="sfx:range(@min, @max, @step)">
				<item>
					<xsl:value-of select="." />
				</item>
			</xsl:for-each>
		</range>
	</xsl:template>

	<xsl:template match="id">
		<id id="{sfx:id(., @suffix)}">
			<xsl:copy-of select="@suffix" />
			<xsl:apply-templates select="*" />
		</id>
	</xsl:template>
</xsl:stylesheet>
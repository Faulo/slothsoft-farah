<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfx="http://schema.slothsoft.net/farah/xslt" xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions" xmlns:str="http://exslt.org/strings" extension-element-prefixes="func php" xmlns:php="http://php.net/xsl">

	<func:function name="sfx:range">
		<xsl:param name="min" />
		<xsl:param name="max" />
		<xsl:param name="step" select="1" />

		<xsl:choose>
			<xsl:when test="$min &lt; $max">
				<func:result select="str:split($min)/text() | sfx:range($min + $step, $max, $step)" />
			</xsl:when>
			<xsl:otherwise>
				<func:result select="str:split($max)/text()" />
			</xsl:otherwise>
		</xsl:choose>
	</func:function>
</xsl:stylesheet>
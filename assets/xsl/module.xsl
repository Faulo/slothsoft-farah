<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfm="http://schema.slothsoft.net/farah/module">

	<xsl:template match="sfm:stylesheet" mode="sfm:html">
		<html:link rel="stylesheet" href="{@href}" type="{@type}" />
	</xsl:template>

	<xsl:template match="sfm:script" mode="sfm:html">
		<html:script src="{@href}" type="{@type}" defer="defer" />
	</xsl:template>

	<xsl:template match="sfm:error" mode="sfm:html">
		<details open="open" class="errorMessage" xmlns="http://www.w3.org/1999/xhtml">
			<summary>
				<h2>
					<xsl:value-of select="@name" />
					<xsl:if test="@class">
						<xsl:text> in class </xsl:text>
						<br />
						<code>
							<xsl:value-of select="@class" />
						</code>
					</xsl:if>
					<xsl:choose>
						<xsl:when test="@result">
							<xsl:text> while processing result </xsl:text>
							<br />
							<code>
								<xsl:value-of select="@result" />
							</code>
						</xsl:when>
						<xsl:when test="@asset">
							<xsl:text> while processing asset </xsl:text>
							<br />
							<code>
								<xsl:value-of select="@asset" />
							</code>
						</xsl:when>
						<xsl:when test="@module">
							<xsl:text> while processing module </xsl:text>
							<br />
							<code>
								<xsl:value-of select="@module" />
							</code>
						</xsl:when>
					</xsl:choose>
				</h2>
			</summary>
			<p>
				<em>
					<xsl:value-of select="@message" />
				</em>
			</p>
			<xsl:copy-of select="." />
			<xsl:apply-templates select="sfm:error" mode="sfm:html" />
		</details>
	</xsl:template>
</xsl:stylesheet>

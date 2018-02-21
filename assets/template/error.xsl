<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:sfm="http://schema.slothsoft.net/farah/module">

	<xsl:template match="/">
		<html>
			<head>
				<title><xsl:value-of select="sfm:error/@name"/></title>
			</head>
			<body>
				<xsl:apply-templates select="sfm:error"/>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="sfm:error">
		<h1>
			<xsl:value-of select="@name"/>
			<xsl:choose>
				<xsl:when test="@asset">
					<xsl:text> in asset </xsl:text>
					<code><xsl:value-of select="@asset"/></code>
				</xsl:when>
				<xsl:when test="@definition">
					<xsl:text> in definition </xsl:text>
					<code><xsl:value-of select="@definition"/></code>
				</xsl:when>
				<xsl:when test="@module">
					<xsl:text> in module </xsl:text>
					<code><xsl:value-of select="@module"/></code>
				</xsl:when>
			</xsl:choose>
		</h1>
		<p><em><xsl:value-of select="@message"/></em></p>
		<dl>
			<xsl:if test="@asset">
				<dt>Asset:</dt>
				<dd><code><xsl:value-of select="@asset"/></code></dd>
			</xsl:if>
			<xsl:if test="@definition">
				<dt>Asset Definition:</dt>
				<dd><code><xsl:value-of select="@definition"/></code></dd>
			</xsl:if>
			<xsl:if test="@module">
				<dt>Module:</dt>
				<dd><code><xsl:value-of select="@module"/></code></dd>
			</xsl:if>
		</dl>
		<pre>
			<xsl:value-of select="concat('   ', @file, '(', @line, ')')"/>
			<br/>
			<xsl:value-of select="@trace"/>
		</pre>
		<xsl:apply-templates select="sfm:error"/>
	</xsl:template>
</xsl:stylesheet>

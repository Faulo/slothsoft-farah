<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:sfs="http://schema.slothsoft.net/farah/sitemap"
	xmlns:sfd="http://schema.slothsoft.net/farah/dictionary"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template match="/*">
		<xsl:processing-instruction name="xml-stylesheet"> href="/getAsset.php/farah/sitemap-generator/prettify" type="application/xslt+xml"</xsl:processing-instruction>
		
		<urlset>
			<xsl:apply-templates select="*[@name='sites']/sfs:domain"/>
		</urlset> 
	</xsl:template>
	
	<xsl:template match="sfs:domain | sfs:page">
		<xsl:param name="priority" select="1.0"/>
		<xsl:variable name="childPages" select="sfs:page[@status-active][@status-public]"/>
		<xsl:variable name="domain" select="ancestor-or-self::sfs:domain"/>
		
		<xsl:if test="@ref and string-length(@url)">
			<url>
				<loc><xsl:value-of select="@url"/></loc>
				<xsl:if test="$domain/@sfd:languages = 'en-us de-de'">
					<html:link rel="alternate" href="{@url}" hreflang="x-default" />
					<html:link rel="alternate" href="{@url}?lang=de-de" hreflang="de-de" />
					<html:link rel="alternate" href="{@url}?lang=en-us" hreflang="en-us" />
				</xsl:if>
				<priority><xsl:value-of select="format-number($priority, '0.00')"/></priority>
			</url>
		</xsl:if>
		<xsl:apply-templates select="$childPages">
			<xsl:with-param name="priority" select="$priority * 0.75"/>
		</xsl:apply-templates>
	</xsl:template>
</xsl:stylesheet>

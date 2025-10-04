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

	<xsl:template match="id-content">
		<id-content>
			<xsl:copy-of select="@suffix" />
			<xsl:call-template name="sfx:id">
				<xsl:with-param name="suffix" select="@suffix" />
			</xsl:call-template>
		</id-content>
	</xsl:template>

	<xsl:template match="test-set-id">
		<test-set-id>
			<set-id>
				<xsl:call-template name="sfx:set-id" />
			</set-id>

			<set-id-with-target>
				<xsl:call-template name="sfx:set-id">
					<xsl:with-param name="context" select="*[1]" />
				</xsl:call-template>
			</set-id-with-target>

			<set-id-wiht-name>
				<xsl:call-template name="sfx:set-id">
					<xsl:with-param name="context" select="*[2]" />
					<xsl:with-param name="name" select="'custom-id'" />
				</xsl:call-template>
			</set-id-wiht-name>

			<set-id-with-namespace>
				<xsl:call-template name="sfx:set-id">
					<xsl:with-param name="context" select="*[3]" />
					<xsl:with-param name="namespace" select="'http://www.w3.org/XML/1998/namespace'" />
				</xsl:call-template>
			</set-id-with-namespace>

			<set-id-with-suffix>
				<xsl:call-template name="sfx:set-id">
					<xsl:with-param name="context" select="*[4]" />
					<xsl:with-param name="suffix" select="'suffix'" />
				</xsl:call-template>
			</set-id-with-suffix>
		</test-set-id>
	</xsl:template>

	<xsl:template match="test-set-href">
		<test-set-href>
			<set-href>
				<xsl:call-template name="sfx:set-href" />
			</set-href>

			<set-href-with-target>
				<xsl:call-template name="sfx:set-href">
					<xsl:with-param name="context" select="*[1]" />
				</xsl:call-template>
			</set-href-with-target>

			<set-href-wiht-name>
				<xsl:call-template name="sfx:set-href">
					<xsl:with-param name="context" select="*[2]" />
					<xsl:with-param name="name" select="'custom-href'" />
				</xsl:call-template>
			</set-href-wiht-name>

			<set-href-with-namespace>
				<xsl:call-template name="sfx:set-href">
					<xsl:with-param name="context" select="*[3]" />
					<xsl:with-param name="name" select="'xlink:href'" />
					<xsl:with-param name="namespace" select="'http://www.w3.org/1999/xlink'" />
				</xsl:call-template>
			</set-href-with-namespace>

			<set-href-with-suffix>
				<xsl:call-template name="sfx:set-href">
					<xsl:with-param name="context" select="*[4]" />
					<xsl:with-param name="suffix" select="'suffix'" />
				</xsl:call-template>
			</set-href-with-suffix>
		</test-set-href>
	</xsl:template>
</xsl:stylesheet>
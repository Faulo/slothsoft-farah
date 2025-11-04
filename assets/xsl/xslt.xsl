<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfx="http://schema.slothsoft.net/farah/xslt" xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions" xmlns:str="http://exslt.org/strings" extension-element-prefixes="func php" xmlns:php="http://php.net/xsl">

	<func:function name="sfx:range">
		<xsl:param name="min" />
		<xsl:param name="max" />
		<xsl:param name="step" select="1" />

		<xsl:choose>
			<xsl:when test="not($step)">
				<func:result select="sfx:range($min, $max)" />
			</xsl:when>
			<xsl:when test="$min &lt; $max">
				<func:result select="str:split($min)/text() | sfx:range($min + $step, $max, $step)" />
			</xsl:when>
			<xsl:otherwise>
				<func:result select="str:split($max)/text()" />
			</xsl:otherwise>
		</xsl:choose>
	</func:function>

	<func:function name="sfx:id">
		<xsl:param name="context" select="." />
		<xsl:param name="suffix" select="''" />

		<xsl:choose>
			<xsl:when test="string($suffix) = ''">
				<func:result select="concat('id-', count($context/preceding::* | $context/ancestor::*))" />
			</xsl:when>
			<xsl:otherwise>
				<func:result select="concat('id-', count($context/preceding::* | $context/ancestor::*), '-', $suffix)" />
			</xsl:otherwise>
		</xsl:choose>
	</func:function>

	<xsl:template name="sfx:id">
		<xsl:param name="context" select="." />
		<xsl:param name="suffix" select="''" />

		<xsl:choose>
			<xsl:when test="string($suffix) = ''">
				<xsl:value-of select="concat('id-', count($context/preceding::* | $context/ancestor::*))" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="concat('id-', count($context/preceding::* | $context/ancestor::*), '-', $suffix)" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="sfx:set-id">
		<xsl:param name="context" select="." />
		<xsl:param name="suffix" select="''" />
		<xsl:param name="name" select="'id'" />
		<xsl:param name="namespace" select="''" />

		<xsl:choose>
			<xsl:when test="string($namespace) = ''">
				<xsl:attribute name="{$name}">
                    <xsl:call-template name="sfx:id">
                        <xsl:with-param name="context" select="$context" />
                        <xsl:with-param name="suffix" select="$suffix" />
                    </xsl:call-template>
                </xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:attribute name="{$name}" namespace="{$namespace}">
                    <xsl:call-template name="sfx:id">
				        <xsl:with-param name="context" select="$context" />
				        <xsl:with-param name="suffix" select="$suffix" />
                    </xsl:call-template>
                </xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="sfx:set-href">
		<xsl:param name="context" select="." />
		<xsl:param name="suffix" select="''" />
		<xsl:param name="name" select="'href'" />
		<xsl:param name="namespace" select="''" />

		<xsl:choose>
			<xsl:when test="string($namespace) = ''">
				<xsl:attribute name="{$name}">
				    <xsl:text>#</xsl:text>
                    <xsl:call-template name="sfx:id">
                        <xsl:with-param name="context" select="$context" />
                        <xsl:with-param name="suffix" select="$suffix" />
                    </xsl:call-template>
                </xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:attribute name="{$name}" namespace="{$namespace}">
                    <xsl:text>#</xsl:text>
                    <xsl:call-template name="sfx:id">
                        <xsl:with-param name="context" select="$context" />
                        <xsl:with-param name="suffix" select="$suffix" />
                    </xsl:call-template>
                </xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>


	<func:function name="sfx:base64-encode">
		<xsl:param name="text" select="." />

		<func:result select="php:functionString('base64_encode', string($text))" />
	</func:function>

	<func:function name="sfx:base64-decode">
		<xsl:param name="code" select="." />

		<func:result select="php:functionString('base64_decode', string($code))" />
	</func:function>
</xsl:stylesheet>